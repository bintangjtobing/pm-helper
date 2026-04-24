<?php

namespace App\Services;

use App\Events\Messenger\MessengerMessageDeleted;
use App\Events\Messenger\MessengerMessageEdited;
use App\Events\Messenger\MessengerMessageRead;
use App\Events\Messenger\MessengerMessageSent;
use App\Events\Messenger\MessengerReactionToggled;
use App\Events\Messenger\MessengerStatusChanged;
use App\Models\MessengerConversation;
use App\Models\MessengerMessage;
use App\Models\MessengerMessageAttachment;
use App\Models\MessengerMessageReaction;
use App\Models\MessengerMessageRead as MessengerMessageReadModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;

class MessengerService
{
    /**
     * Allowed mime types for image attachments.
     */
    public const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Max file size in bytes for images (10MB) and other files (30MB).
     */
    public const MAX_IMAGE_SIZE = 10 * 1024 * 1024;
    public const MAX_FILE_SIZE = 30 * 1024 * 1024;

    /**
     * Resize ratio applied to uploaded images.
     */
    public const IMAGE_RESIZE_RATIO = 0.9;

    /**
     * Edit window in minutes after a message is sent.
     */
    public const EDIT_WINDOW_MINUTES = 15;

    // ──────────────────────────────────────────────────────────────────────────
    // Conversation lifecycle
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Get or create a 1-on-1 conversation between two users.
     * The pair is normalized so user_one_id is always the smaller id.
     */
    public function createOrGetConversation(User $userA, User $userB): MessengerConversation
    {
        if ($userA->id === $userB->id) {
            throw new InvalidArgumentException('Cannot start a conversation with yourself.');
        }

        $smaller = (int) min($userA->id, $userB->id);
        $larger  = (int) max($userA->id, $userB->id);

        $existing = MessengerConversation::query()
            ->where('user_one_id', $smaller)
            ->where('user_two_id', $larger)
            ->first();

        if ($existing) {
            return $existing;
        }

        return MessengerConversation::create([
            'user_one_id'       => $smaller,
            'user_two_id'       => $larger,
            'attachment_folder' => $this->generateUniqueFolderName(),
        ]);
    }

    /**
     * Generate a unique 10-character lowercase alphanumeric folder name.
     */
    public function generateUniqueFolderName(): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $name = '';
            for ($j = 0; $j < 10; $j++) {
                $name .= $alphabet[random_int(0, 35)];
            }

            $exists = MessengerConversation::where('attachment_folder', $name)->exists();
            if (! $exists) {
                return $name;
            }
        }

        throw new RuntimeException('Could not generate a unique attachment folder name.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Send / edit / delete
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Send a message in a conversation. Body or files (or both) are required.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public function sendMessage(
        MessengerConversation $conversation,
        User $sender,
        ?string $body = null,
        array $files = [],
        ?int $replyToId = null
    ): MessengerMessage {
        if (! $conversation->hasParticipant((int) $sender->id)) {
            throw new RuntimeException('Sender is not a participant of this conversation.');
        }

        $body = $body !== null ? trim($body) : null;
        $hasBody = $body !== null && $body !== '';
        $hasFiles = count($files) > 0;

        if (! $hasBody && ! $hasFiles) {
            throw new InvalidArgumentException('Message must have body text or at least one attachment.');
        }

        if ($replyToId !== null) {
            $reply = MessengerMessage::find($replyToId);
            if (! $reply || (int) $reply->conversation_id !== (int) $conversation->id) {
                throw new InvalidArgumentException('Invalid reply target.');
            }
        }

        // Validate files BEFORE writing anything to disk.
        foreach ($files as $file) {
            $this->assertAttachmentValid($file);
        }

        $savedAbsolutePaths = [];

        try {
            DB::beginTransaction();

            $message = MessengerMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $sender->id,
                'body'            => $hasBody ? $body : null,
                'reply_to_id'     => $replyToId,
            ]);

            foreach ($files as $file) {
                $meta = $this->storeAttachmentFile($file, $conversation->attachment_folder);
                $savedAbsolutePaths[] = $meta['absolute_path'];

                $message->attachments()->create([
                    'filename_stored'   => $meta['filename_stored'],
                    'filename_original' => $meta['filename_original'],
                    'mime_type'         => $meta['mime_type'],
                    'size_bytes'        => $meta['size_bytes'],
                    'width'             => $meta['width'],
                    'height'            => $meta['height'],
                ]);
            }

            $conversation->update(['last_message_at' => now()]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            foreach ($savedAbsolutePaths as $path) {
                @unlink($path);
            }
            throw $e;
        }

        // Fetch link preview for the first URL (non-blocking, after commit).
        if ($hasBody) {
            $this->fetchAndStoreLinkPreview($message, $body);
        }

        $message->load(['attachments', 'replyTo.sender', 'sender']);

        event(new MessengerMessageSent($message));

        return $message;
    }

    /**
     * Edit a message body. Only the original sender, only within the edit window.
     */
    public function editMessage(MessengerMessage $message, User $actor, string $newBody): MessengerMessage
    {
        if ((int) $message->sender_id !== (int) $actor->id) {
            throw new RuntimeException('Only the sender can edit a message.');
        }

        if ($message->isDeletedForEveryone()) {
            throw new RuntimeException('Cannot edit a deleted message.');
        }

        if (! $message->isWithinEditWindow()) {
            throw new RuntimeException('The 15-minute edit window has expired.');
        }

        $newBody = trim($newBody);
        if ($newBody === '') {
            throw new InvalidArgumentException('Edited body cannot be empty.');
        }

        $message->update([
            'body'      => $newBody,
            'edited_at' => now(),
            'link_preview' => null,
        ]);

        // Re-fetch link preview for the updated body.
        $this->fetchAndStoreLinkPreview($message, $newBody);

        event(new MessengerMessageEdited($message));

        return $message;
    }

    /**
     * Delete a message. Scope is either 'me' (hide for actor only) or 'everyone' (sender only).
     */
    public function deleteMessage(MessengerMessage $message, User $actor, string $scope): MessengerMessage
    {
        if (! in_array($scope, ['me', 'everyone'], true)) {
            throw new InvalidArgumentException('Scope must be "me" or "everyone".');
        }

        $conversation = $message->conversation;
        if (! $conversation || ! $conversation->hasParticipant((int) $actor->id)) {
            throw new RuntimeException('Actor is not a participant of this conversation.');
        }

        if ($scope === 'everyone') {
            if ((int) $message->sender_id !== (int) $actor->id) {
                throw new RuntimeException('Only the sender can delete a message for everyone.');
            }
            $message->update(['deleted_for_everyone_at' => now()]);
        } else {
            $hidden = $message->hidden_for_user_ids ?? [];
            if (! in_array((int) $actor->id, $hidden, true)) {
                $hidden[] = (int) $actor->id;
                $message->update(['hidden_for_user_ids' => array_values($hidden)]);
            }
        }

        event(new MessengerMessageDeleted($message, $scope === 'everyone', (int) $actor->id));

        return $message;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Read receipts
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Mark a single message as read by a user. Idempotent.
     * Returns the read row, or null if the reader is the sender (skipped).
     */
    public function markAsRead(MessengerMessage $message, User $reader): ?MessengerMessageReadModel
    {
        if ((int) $message->sender_id === (int) $reader->id) {
            return null;
        }

        $conversation = $message->conversation;
        if (! $conversation || ! $conversation->hasParticipant((int) $reader->id)) {
            return null;
        }

        // firstOrCreate is race-safe and avoids 1062 duplicate-entry errors
        // that happened when two concurrent markAsRead calls raced on the same
        // (message_id, user_id) unique constraint.
        $row = MessengerMessageReadModel::firstOrCreate(
            ['message_id' => $message->id, 'user_id' => $reader->id],
            ['read_at' => now()]
        );

        // Only broadcast if THIS call actually inserted the row (not a pre-existing one).
        if ($row->wasRecentlyCreated) {
            event(new MessengerMessageRead(
                (int) $conversation->id,
                (int) $message->id,
                (int) $reader->id,
                $row->read_at->toIso8601String()
            ));
        }

        return $row;
    }

    /**
     * Mark all unread incoming messages in a conversation as read.
     * Returns the number of newly marked messages.
     */
    public function markConversationAsRead(MessengerConversation $conversation, User $reader): int
    {
        if (! $conversation->hasParticipant((int) $reader->id)) {
            return 0;
        }

        $unread = MessengerMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('deleted_for_everyone_at')
            ->whereDoesntHave('reads', function ($q) use ($reader) {
                $q->where('user_id', $reader->id);
            })
            ->get();

        $count = 0;
        foreach ($unread as $msg) {
            if ($this->markAsRead($msg, $reader)) {
                $count++;
            }
        }

        return $count;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Reactions
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Toggle a reaction by a user on a message. One reaction per user per message.
     * Returns ['action' => 'added'|'removed'|'changed', 'emoji' => string].
     */
    public function toggleReaction(MessengerMessage $message, User $user, string $emoji): array
    {
        if (! MessengerMessageReaction::isAllowed($emoji)) {
            throw new InvalidArgumentException('Reaction emoji is not allowed.');
        }

        $conversation = $message->conversation;
        if (! $conversation || ! $conversation->hasParticipant((int) $user->id)) {
            throw new RuntimeException('User is not a participant of this conversation.');
        }

        if ($message->isDeletedForEveryone()) {
            throw new RuntimeException('Cannot react to a deleted message.');
        }

        $existing = MessengerMessageReaction::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->first();

        $action = 'added';

        if ($existing) {
            if ($existing->emoji === $emoji) {
                $existing->delete();
                $action = 'removed';
            } else {
                $existing->update(['emoji' => $emoji]);
                $action = 'changed';
            }
        } else {
            MessengerMessageReaction::create([
                'message_id' => $message->id,
                'user_id'    => $user->id,
                'emoji'      => $emoji,
            ]);
        }

        event(new MessengerReactionToggled(
            (int) $conversation->id,
            (int) $message->id,
            (int) $user->id,
            $emoji,
            $action
        ));

        return ['action' => $action, 'emoji' => $emoji];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Search
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Search messages within a single conversation by body text.
     */
    public function searchInConversation(
        MessengerConversation $conversation,
        User $reader,
        string $query,
        int $limit = 50
    ): Collection {
        if (! $conversation->hasParticipant((int) $reader->id)) {
            return new Collection();
        }

        $query = trim($query);
        if ($query === '') {
            return new Collection();
        }

        return MessengerMessage::query()
            ->where('conversation_id', $conversation->id)
            ->whereNull('deleted_for_everyone_at')
            ->where('body', 'like', '%' . $query . '%')
            ->latest('created_at')
            ->limit($limit)
            ->with(['sender'])
            ->get()
            ->reject(fn (MessengerMessage $m) => $m->isHiddenFor((int) $reader->id))
            ->values();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Attachment storage helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Validate file size + presence based on whether it's an image or not.
     */
    protected function assertAttachmentValid(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Uploaded file is not valid.');
        }

        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $size = $file->getSize() ?: 0;
        $isImage = in_array($mime, self::IMAGE_MIME_TYPES, true);

        if ($isImage && $size > self::MAX_IMAGE_SIZE) {
            throw new InvalidArgumentException('Image attachment exceeds 10 MB.');
        }
        if (! $isImage && $size > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('File attachment exceeds 30 MB.');
        }
    }

    /**
     * Store an uploaded file under the conversation's private attachment folder.
     * Images are resized to IMAGE_RESIZE_RATIO of original width.
     *
     * @return array{filename_stored: string, filename_original: string, mime_type: string, size_bytes: int, width: ?int, height: ?int, absolute_path: string}
     */
    protected function storeAttachmentFile(UploadedFile $file, string $folderName): array
    {
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $isImage = in_array($mime, self::IMAGE_MIME_TYPES, true);

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $extension = strtolower($extension);

        $filenameStored = (string) Str::uuid() . '.' . $extension;

        $relativeDir = 'messenger-attachments/' . $folderName;
        $absoluteDir = storage_path('app/' . $relativeDir);

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $absolutePath = $absoluteDir . '/' . $filenameStored;

        $width = null;
        $height = null;
        $sizeBytes = (int) $file->getSize();

        if ($isImage) {
            $sourcePath = $file->getRealPath();
            $imageInfo = @getimagesize($sourcePath);
            $origWidth = $imageInfo[0] ?? 0;

            if ($origWidth > 0) {
                $newWidth = max(1, (int) round($origWidth * self::IMAGE_RESIZE_RATIO));

                Image::load($sourcePath)
                    ->manipulate(function (Manipulations $m) use ($newWidth) {
                        $m->width($newWidth);
                        $m->quality(90);
                    })
                    ->save($absolutePath);

                $finalInfo = @getimagesize($absolutePath);
                if ($finalInfo) {
                    $width = (int) $finalInfo[0];
                    $height = (int) $finalInfo[1];
                }
                $sizeBytes = (int) (@filesize($absolutePath) ?: $sizeBytes);
            } else {
                // Fallback: not a recognizable image, just copy as-is.
                copy($sourcePath, $absolutePath);
            }
        } else {
            $sourcePath = $file->getRealPath();
            copy($sourcePath, $absolutePath);
        }

        return [
            'filename_stored'   => $filenameStored,
            'filename_original' => $file->getClientOriginalName(),
            'mime_type'         => $mime,
            'size_bytes'        => $sizeBytes,
            'width'             => $width,
            'height'            => $height,
            'absolute_path'     => $absolutePath,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // User status
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Default in-meeting auto-clear duration in hours.
     */
    public const IN_MEETING_AUTO_CLEAR_HOURS = 2;
    public const LUNCH_BREAK_AUTO_CLEAR_HOURS = 1;

    /**
     * Set the user's manual status. Validates allowed values and applies
     * sensible auto-clear rules.
     *
     * @param string|null $status One of User::ALLOWED_STATUSES, or null to clear.
     * @param string|null $message Optional custom status message (max 80 chars).
     * @param string|null $onLeaveFrom YYYY-MM-DD (only for on_leave).
     * @param string|null $onLeaveUntil YYYY-MM-DD (only for on_leave).
     */
    public function setStatus(
        User $user,
        ?string $status,
        ?string $message = null,
        ?string $onLeaveFrom = null,
        ?string $onLeaveUntil = null
    ): User {
        if ($status !== null && ! in_array($status, User::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException('Invalid status value.');
        }

        $message = $message !== null ? trim($message) : null;
        if ($message !== null && mb_strlen($message) > 80) {
            $message = mb_substr($message, 0, 80);
        }

        $updates = [
            'status' => $status,
            'status_message' => $message ?: null,
            'status_until' => null,
            'on_leave_from' => null,
            'on_leave_until' => null,
        ];

        if ($status === 'in_meeting') {
            $updates['status_until'] = now()->addHours(self::IN_MEETING_AUTO_CLEAR_HOURS);
        }

        if ($status === 'lunch_break') {
            $updates['status_until'] = now()->addHours(self::LUNCH_BREAK_AUTO_CLEAR_HOURS);
        }

        if ($status === 'on_leave') {
            if (! $onLeaveUntil) {
                throw new InvalidArgumentException('on_leave requires an end date.');
            }
            $updates['on_leave_from'] = $onLeaveFrom ?: now()->toDateString();
            $updates['on_leave_until'] = $onLeaveUntil;
        }

        $user->forceFill($updates)->saveQuietly();

        event(new MessengerStatusChanged($user->fresh()));

        return $user;
    }

    /**
     * Clear the user's manual status (back to presence-driven).
     */
    public function clearStatus(User $user): User
    {
        return $this->setStatus($user, null);
    }

    /**
     * Sweep expired statuses across all users. Called from a scheduled command.
     */
    public function clearExpiredStatuses(): int
    {
        $cleared = 0;

        // status_until expired (e.g. in_meeting > 2h)
        $rows = User::query()
            ->whereNotNull('status_until')
            ->where('status_until', '<', now())
            ->get();

        foreach ($rows as $u) {
            $this->clearStatus($u);
            $cleared++;
        }

        // on_leave_until expired
        $leaveRows = User::query()
            ->whereNotNull('on_leave_until')
            ->whereDate('on_leave_until', '<', now()->toDateString())
            ->get();

        foreach ($leaveRows as $u) {
            $this->clearStatus($u);
            $cleared++;
        }

        return $cleared;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Attachment path helper
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Resolve the absolute path of an attachment file. Returns null if missing.
     */
    public function resolveAttachmentPath(MessengerMessageAttachment $attachment): ?string
    {
        $folder = $attachment->message?->conversation?->attachment_folder;
        if (! $folder) {
            return null;
        }
        $path = storage_path('app/messenger-attachments/' . $folder . '/' . $attachment->filename_stored);
        return is_file($path) ? $path : null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Link preview (Open Graph)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Extract the first URL from message body, fetch its OG meta, and store on the message.
     */
    protected function fetchAndStoreLinkPreview(MessengerMessage $message, string $body): void
    {
        // Extract first URL from the body.
        if (! preg_match('#(https?://[^\s<>\'")\]]+)#i', $body, $match)) {
            return;
        }

        $url = $match[1];
        // Strip trailing punctuation.
        $url = preg_replace('/[.,;:!?\)]+$/', '', $url);

        // Skip Jitsi meeting URLs — they're rendered as a dedicated pill
        // in the chat bubble, no OG preview card needed.
        if (preg_match('~^https?://meet\.(digicrats\.com|jit\.si)/pmhelper-~i', $url)) {
            return;
        }

        try {
            $preview = $this->fetchOgMeta($url);
            if ($preview) {
                $message->update(['link_preview' => $preview]);
            }
        } catch (\Throwable $e) {
            // Silently ignore — link preview is non-critical.
        }
    }

    /**
     * Fetch Open Graph meta tags from a URL.
     *
     * @return array{url: string, domain: string, title: ?string, description: ?string, image: ?string}|null
     */
    protected function fetchOgMeta(string $url): ?array
    {
        $context = stream_context_create([
            'http' => [
                'timeout'       => 3,
                'max_redirects' => 3,
                'header'        => "User-Agent: PMHelper/1.0 LinkPreview\r\n",
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $html = @file_get_contents($url, false, $context, 0, 100000); // Read max 100KB
        if (! $html || strlen($html) < 50) {
            return null;
        }

        $parsed = parse_url($url);
        $domain = $parsed['host'] ?? $url;

        // Parse OG meta tags with DOMDocument.
        $doc = new \DOMDocument();
        @$doc->loadHTML('<?xml encoding="UTF-8">' . mb_substr($html, 0, 100000), LIBXML_NOERROR | LIBXML_NOWARNING);

        $ogTitle = null;
        $ogDescription = null;
        $ogImage = null;
        $pageTitle = null;

        // Get <title> as fallback.
        $titleTags = $doc->getElementsByTagName('title');
        if ($titleTags->length > 0) {
            $pageTitle = trim($titleTags->item(0)->textContent);
        }

        // Parse <meta> tags.
        $metas = $doc->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            $property = $meta->getAttribute('property') ?: $meta->getAttribute('name');
            $content = $meta->getAttribute('content');

            if (! $property || $content === '') {
                continue;
            }

            switch (strtolower($property)) {
                case 'og:title':
                    $ogTitle = $content;
                    break;
                case 'og:description':
                    $ogDescription = $content;
                    break;
                case 'og:image':
                    $ogImage = $content;
                    break;
                case 'description':
                    if (! $ogDescription) {
                        $ogDescription = $content;
                    }
                    break;
            }
        }

        $title = $ogTitle ?: $pageTitle;

        // Must have at least a title to show a preview.
        if (! $title) {
            return null;
        }

        // Resolve relative image URL.
        if ($ogImage && ! preg_match('#^https?://#i', $ogImage)) {
            $base = ($parsed['scheme'] ?? 'https') . '://' . $domain;
            $ogImage = str_starts_with($ogImage, '/') ? $base . $ogImage : $base . '/' . $ogImage;
        }

        return [
            'url'         => $url,
            'domain'      => $domain,
            'title'       => mb_substr($title, 0, 200),
            'description' => $ogDescription ? mb_substr($ogDescription, 0, 300) : null,
            'image'       => $ogImage,
        ];
    }
}
