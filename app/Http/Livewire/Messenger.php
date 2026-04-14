<?php

namespace App\Http\Livewire;

use App\Models\MessengerConversation;
use App\Models\MessengerMessage;
use App\Models\MessengerMessageReaction;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Services\MessengerService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Messenger extends Component
{
    use WithFileUploads;

    // ──────────────────────────────────────────────────────────────────────
    // UI state
    // ──────────────────────────────────────────────────────────────────────

    public bool $isOpen = false;

    /** 'list' | 'conversation' | 'new_chat' */
    public string $view = 'list';

    public ?int $activeConversationId = null;

    // ──────────────────────────────────────────────────────────────────────
    // Conversation list
    // ──────────────────────────────────────────────────────────────────────

    public array $conversations = [];
    public int $totalUnread = 0;

    // ──────────────────────────────────────────────────────────────────────
    // Active conversation messages
    // ──────────────────────────────────────────────────────────────────────

    public array $messages = [];
    public ?int $oldestMessageId = null;
    public bool $hasMoreMessages = false;
    public int $messagesPerPage = 30;

    // ──────────────────────────────────────────────────────────────────────
    // Composer
    // ──────────────────────────────────────────────────────────────────────

    public string $newMessage = '';
    public $files = [];
    public ?int $replyToMessageId = null;
    public ?int $editingMessageId = null;
    public string $editingBody = '';

    // ──────────────────────────────────────────────────────────────────────
    // Search
    // ──────────────────────────────────────────────────────────────────────

    public string $searchQuery = '';
    public array $searchResults = [];

    // ──────────────────────────────────────────────────────────────────────
    // New chat picker
    // ──────────────────────────────────────────────────────────────────────

    public string $newChatSearch = '';
    public array $userPickerResults = [];

    // ──────────────────────────────────────────────────────────────────────
    // Listeners (events emitted from Alpine on Echo callbacks)
    // ──────────────────────────────────────────────────────────────────────

    protected $listeners = [
        'messenger:incoming-message'    => 'handleIncomingMessage',
        'messenger:incoming-edit'       => 'handleIncomingEdit',
        'messenger:incoming-delete'     => 'handleIncomingDelete',
        'messenger:incoming-read'       => 'handleIncomingRead',
        'messenger:incoming-reaction'   => 'handleIncomingReaction',
        'messenger:incoming-status'     => 'handleIncomingStatus',
        'messenger:open-conversation'   => 'openConversation',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Lifecycle
    // ──────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        if (! auth()->check()) {
            return;
        }
        $this->loadConversations();
    }

    public function render()
    {
        return view('livewire.messenger');
    }

    protected function service(): MessengerService
    {
        return app(MessengerService::class);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Open/close
    // ──────────────────────────────────────────────────────────────────────

    public function toggleOpen(): void
    {
        $this->isOpen = ! $this->isOpen;
        if ($this->isOpen) {
            $this->loadConversations();
        }
        $this->dispatchBrowserEvent('messenger:reset-menus');
    }

    public function openConversation(int $conversationId): void
    {
        $conversation = MessengerConversation::find($conversationId);
        if (! $conversation || ! $conversation->hasParticipant((int) auth()->id())) {
            return;
        }

        $this->activeConversationId = $conversationId;
        $this->view = 'conversation';
        $this->isOpen = true;
        $this->resetComposer();
        $this->searchQuery = '';
        $this->searchResults = [];

        $this->loadMessages(initial: true);
        $this->markActiveConversationAsRead();
        $this->dispatchBrowserEvent('messenger:reset-menus');
    }

    public function closeConversation(): void
    {
        $this->activeConversationId = null;
        $this->view = 'list';
        $this->messages = [];
        $this->oldestMessageId = null;
        $this->hasMoreMessages = false;
        $this->resetComposer();
        $this->loadConversations();
        $this->dispatchBrowserEvent('messenger:reset-menus');
    }

    public function backToList(): void
    {
        $this->closeConversation();
    }

    protected function resetComposer(): void
    {
        $this->newMessage = '';
        $this->files = [];
        $this->replyToMessageId = null;
        $this->editingMessageId = null;
        $this->editingBody = '';
    }

    // ──────────────────────────────────────────────────────────────────────
    // Conversation list
    // ──────────────────────────────────────────────────────────────────────

    public function loadConversations(): void
    {
        $userId = (int) auth()->id();

        $rows = MessengerConversation::query()
            ->where(function ($q) use ($userId) {
                $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
            })
            ->with([
                'userOne:id,name,username,avatar_url,last_seen_at,status,status_message,status_until,on_leave_from,on_leave_until',
                'userTwo:id,name,username,avatar_url,last_seen_at,status,status_message,status_until,on_leave_from,on_leave_until',
                'latestMessage' => function ($q) {
                    // Cannot select() here — latestOfMany() adds a subquery JOIN that
                    // makes conversation_id ambiguous. Load all columns instead.
                    $q->with('attachments:id,message_id,mime_type');
                },
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();

        $conversationIds = $rows->pluck('id')->all();
        $unreadMap = $this->computeUnreadCounts($conversationIds, $userId);

        $totalUnread = 0;
        $list = [];

        foreach ($rows as $c) {
            $other = $c->otherParticipant($userId);
            if (! $other) {
                continue;
            }
            $unread = (int) ($unreadMap[$c->id] ?? 0);
            $totalUnread += $unread;

            $latest = $c->latestMessage;
            $preview = $this->buildPreview($latest, $userId);

            $list[] = [
                'id'                    => $c->id,
                'other_id'              => $other->id,
                'other_name'            => $other->name,
                'other_username'        => $other->username,
                'other_avatar'          => $other->avatar_url,
                'other_last_seen_at'    => optional($other->last_seen_at)->toIso8601String(),
                'other_status'          => $other->effectiveStatus(),
                'other_status_message'  => $other->status_message,
                'other_on_leave_until'  => optional($other->on_leave_until)->toDateString(),
                'unread'                => $unread,
                'last_preview'          => $preview['text'],
                'last_is_attachment'    => $preview['is_attachment'],
                'last_is_self'          => $latest && (int) $latest->sender_id === $userId,
                'last_at'               => optional($c->last_message_at)->diffForHumans(null, true),
                'last_at_iso'           => optional($c->last_message_at)->toIso8601String(),
            ];
        }

        $this->conversations = $list;
        $this->totalUnread = $totalUnread;
    }

    protected function computeUnreadCounts(array $conversationIds, int $userId): array
    {
        if (empty($conversationIds)) {
            return [];
        }

        return MessengerMessage::query()
            ->whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $userId)
            ->whereNull('deleted_for_everyone_at')
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $userId))
            ->select('conversation_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('conversation_id')
            ->pluck('cnt', 'conversation_id')
            ->toArray();
    }

    protected function buildPreview(?MessengerMessage $latest, int $userId): array
    {
        if (! $latest) {
            return ['text' => '', 'is_attachment' => false];
        }
        if ($latest->deleted_for_everyone_at) {
            return ['text' => 'Message deleted', 'is_attachment' => false];
        }
        if ($latest->body) {
            return ['text' => mb_substr($latest->body, 0, 80), 'is_attachment' => false];
        }
        $att = $latest->attachments->first();
        if ($att) {
            $isImage = str_starts_with($att->mime_type, 'image/');
            return ['text' => $isImage ? 'Sent an image' : 'Sent a file', 'is_attachment' => true];
        }
        return ['text' => '', 'is_attachment' => false];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Active conversation messages (infinite scroll up)
    // ──────────────────────────────────────────────────────────────────────

    public function loadMessages(bool $initial = false): void
    {
        if (! $this->activeConversationId) {
            return;
        }

        $userId = (int) auth()->id();
        $query = MessengerMessage::query()
            ->where('conversation_id', $this->activeConversationId)
            ->with([
                'sender:id,name,username,avatar_url',
                'replyTo' => fn ($q) => $q->select('id', 'sender_id', 'body')->with('sender:id,name'),
                'attachments',
                'reads',
                'reactions',
            ]);

        if (! $initial && $this->oldestMessageId) {
            $query->where('id', '<', $this->oldestMessageId);
        }

        $rows = $query->orderByDesc('id')
            ->limit($this->messagesPerPage + 1)
            ->get();

        $this->hasMoreMessages = $rows->count() > $this->messagesPerPage;
        if ($this->hasMoreMessages) {
            $rows = $rows->take($this->messagesPerPage);
        }

        // Reverse so oldest first (top to bottom in UI).
        $rows = $rows->reverse()->values();

        $serialized = $rows->map(fn (MessengerMessage $m) => $this->serializeMessage($m, $userId))->all();

        if ($initial) {
            $this->messages = $serialized;
        } else {
            // Prepend older messages.
            $this->messages = array_merge($serialized, $this->messages);
        }

        if (! empty($serialized)) {
            $this->oldestMessageId = (int) $serialized[0]['id'];
        }
    }

    protected function serializeMessage(MessengerMessage $m, int $userId): array
    {
        $isSelf = (int) $m->sender_id === $userId;
        $hidden = $m->isHiddenFor($userId);
        $deletedForEveryone = $m->isDeletedForEveryone();

        // Group reactions by emoji.
        $reactionGroups = [];
        foreach ($m->reactions as $r) {
            $emoji = $r->emoji;
            if (! isset($reactionGroups[$emoji])) {
                $reactionGroups[$emoji] = ['emoji' => $emoji, 'count' => 0, 'user_ids' => []];
            }
            $reactionGroups[$emoji]['count']++;
            $reactionGroups[$emoji]['user_ids'][] = (int) $r->user_id;
        }

        $reads = $m->reads->map(fn ($r) => [
            'user_id' => (int) $r->user_id,
            'read_at' => $r->read_at?->toIso8601String(),
        ])->all();

        return [
            'id'                    => (int) $m->id,
            'conversation_id'       => (int) $m->conversation_id,
            'sender_id'             => (int) $m->sender_id,
            'sender_name'           => $m->sender?->name,
            'sender_avatar'         => $m->sender?->avatar_url,
            'is_self'               => $isSelf,
            'body'                  => $deletedForEveryone ? null : $m->body,
            'rendered_body'         => $deletedForEveryone ? null : $this->renderTicketBadges($m->body),
            'link_preview'          => $deletedForEveryone ? null : $m->link_preview,
            'reply_to'              => $m->replyTo ? [
                'id'          => (int) $m->replyTo->id,
                'sender_name' => $m->replyTo->sender?->name,
                'body'        => mb_substr((string) $m->replyTo->body, 0, 100),
            ] : null,
            'edited_at'             => $m->edited_at?->toIso8601String(),
            'created_at'            => $m->created_at?->toIso8601String(),
            'time_label'            => $m->created_at?->format('H:i'),
            'date_label'            => $m->created_at?->format('d M Y'),
            'within_edit_window'    => $isSelf && ! $deletedForEveryone && $m->isWithinEditWindow(),
            'is_hidden_for_me'      => $hidden,
            'is_deleted_for_all'    => $deletedForEveryone,
            'attachments'           => $m->attachments->map(fn ($a) => [
                'id'                => (int) $a->id,
                'filename_original' => $a->filename_original,
                'mime_type'         => $a->mime_type,
                'size_bytes'        => (int) $a->size_bytes,
                'is_image'          => str_starts_with($a->mime_type, 'image/'),
                'width'             => $a->width,
                'height'            => $a->height,
                'preview_url'       => route('messenger.attachments.preview', ['message' => $m->id, 'attachment' => $a->id]),
                'download_url'      => route('messenger.attachments.download', ['message' => $m->id, 'attachment' => $a->id]),
            ])->all(),
            'reactions'             => array_values($reactionGroups),
            'reads'                 => $reads,
        ];
    }

    public function loadMoreMessages(): void
    {
        if (! $this->hasMoreMessages) {
            return;
        }
        $this->loadMessages(initial: false);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Send / edit / delete
    // ──────────────────────────────────────────────────────────────────────

    public function sendMessage(): void
    {
        if (! $this->activeConversationId) {
            return;
        }
        $conversation = MessengerConversation::find($this->activeConversationId);
        if (! $conversation) {
            return;
        }

        $body = trim($this->newMessage);
        $files = $this->files;

        if ($body === '' && empty($files)) {
            return;
        }

        // Per-file validation: image max 5 MB, others max 30 MB.
        foreach ($files as $file) {
            $mime = $file->getMimeType() ?: 'application/octet-stream';
            $size = $file->getSize() ?: 0;
            $isImage = str_starts_with($mime, 'image/');
            $maxBytes = $isImage ? (5 * 1024 * 1024) : MessengerService::MAX_FILE_SIZE;
            if ($size > $maxBytes) {
                $this->addError('files', $isImage
                    ? 'Image attachment exceeds 5 MB.'
                    : 'File attachment exceeds 30 MB.');
                return;
            }
        }

        try {
            $this->service()->sendMessage(
                $conversation,
                auth()->user(),
                $body !== '' ? $body : null,
                $files,
                $this->replyToMessageId
            );
        } catch (\Throwable $e) {
            $this->addError('newMessage', $e->getMessage());
            return;
        }

        $this->resetComposer();
        $this->loadMessages(initial: true);
        $this->loadConversations();

        $this->dispatchBrowserEvent('messenger:message-sent');
    }

    public function startEdit(int $messageId): void
    {
        $message = MessengerMessage::find($messageId);
        if (! $message) {
            return;
        }
        if ((int) $message->sender_id !== (int) auth()->id()) {
            return;
        }
        if (! $message->isWithinEditWindow() || $message->isDeletedForEveryone()) {
            return;
        }
        $this->editingMessageId = $message->id;
        $this->editingBody = (string) $message->body;
    }

    public function cancelEdit(): void
    {
        $this->editingMessageId = null;
        $this->editingBody = '';
    }

    public function saveEdit(): void
    {
        if (! $this->editingMessageId) {
            return;
        }
        $message = MessengerMessage::find($this->editingMessageId);
        if (! $message) {
            return;
        }
        try {
            $this->service()->editMessage($message, auth()->user(), $this->editingBody);
        } catch (\Throwable $e) {
            $this->addError('editingBody', $e->getMessage());
            return;
        }
        $this->cancelEdit();
        $this->loadMessages(initial: true);
    }

    public function deleteMessageForMe(int $messageId): void
    {
        $this->doDelete($messageId, 'me');
    }

    public function deleteMessageForEveryone(int $messageId): void
    {
        $this->doDelete($messageId, 'everyone');
    }

    protected function doDelete(int $messageId, string $scope): void
    {
        $message = MessengerMessage::find($messageId);
        if (! $message) {
            return;
        }
        try {
            $this->service()->deleteMessage($message, auth()->user(), $scope);
        } catch (\Throwable $e) {
            return;
        }
        $this->loadMessages(initial: true);
        $this->loadConversations();
    }

    public function setReplyTo(int $messageId): void
    {
        $message = MessengerMessage::find($messageId);
        if (! $message || (int) $message->conversation_id !== (int) $this->activeConversationId) {
            return;
        }
        $this->replyToMessageId = $messageId;
    }

    public function cancelReply(): void
    {
        $this->replyToMessageId = null;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Reactions
    // ──────────────────────────────────────────────────────────────────────

    public function toggleReaction(int $messageId, string $emoji): void
    {
        if (! MessengerMessageReaction::isAllowed($emoji)) {
            return;
        }
        $message = MessengerMessage::find($messageId);
        if (! $message) {
            return;
        }
        try {
            $this->service()->toggleReaction($message, auth()->user(), $emoji);
        } catch (\Throwable $e) {
            return;
        }
        $this->loadMessages(initial: true);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Read receipts
    // ──────────────────────────────────────────────────────────────────────

    public function markActiveConversationAsRead(): void
    {
        if (! $this->activeConversationId) {
            return;
        }
        $conversation = MessengerConversation::find($this->activeConversationId);
        if (! $conversation) {
            return;
        }
        $count = $this->service()->markConversationAsRead($conversation, auth()->user());
        if ($count > 0) {
            $this->loadConversations();
            $this->loadMessages(initial: true);
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Search (per-conversation, body only)
    // ──────────────────────────────────────────────────────────────────────

    public function updatedSearchQuery(): void
    {
        $this->runSearch();
    }

    public function runSearch(): void
    {
        if (! $this->activeConversationId) {
            $this->searchResults = [];
            return;
        }
        $q = trim($this->searchQuery);
        if (mb_strlen($q) < 2) {
            $this->searchResults = [];
            return;
        }
        $conversation = MessengerConversation::find($this->activeConversationId);
        if (! $conversation) {
            return;
        }
        $results = $this->service()->searchInConversation($conversation, auth()->user(), $q);
        $this->searchResults = $results->map(function ($m) {
            return [
                'id'         => $m->id,
                'sender'     => $m->sender?->name,
                'body'       => mb_substr((string) $m->body, 0, 200),
                'time_label' => $m->created_at?->format('d M Y H:i'),
            ];
        })->all();
    }

    public function clearSearch(): void
    {
        $this->searchQuery = '';
        $this->searchResults = [];
    }

    // ──────────────────────────────────────────────────────────────────────
    // New chat picker
    // ──────────────────────────────────────────────────────────────────────

    public function openNewChatPicker(): void
    {
        $this->view = 'new_chat';
        $this->isOpen = true;
        $this->newChatSearch = '';
        $this->loadUserPicker('');
    }

    public function updatedNewChatSearch(): void
    {
        $this->loadUserPicker($this->newChatSearch);
    }

    /**
     * Populate the user picker. Empty search returns the first 50 active users
     * (excluding the current user); a search filters by name/username/email.
     */
    protected function loadUserPicker(string $search): void
    {
        $userId = (int) auth()->id();
        $query = User::query()
            ->where('id', '!=', $userId)
            ->whereNull('deleted_at');

        $q = trim($search);
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('username', 'like', $like)
                  ->orWhere('email', 'like', $like);
            });
        }

        $users = $query->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'username', 'email', 'avatar_url']);

        $this->userPickerResults = $users->map(fn ($u) => [
            'id'       => (int) $u->id,
            'name'     => $u->name,
            'username' => $u->username,
            'avatar'   => $u->avatar_url,
        ])->all();
    }

    public function startConversationWith(int $userId): void
    {
        $other = User::find($userId);
        if (! $other) {
            return;
        }
        $conversation = $this->service()->createOrGetConversation(auth()->user(), $other);
        $this->openConversation($conversation->id);
    }

    public function cancelNewChat(): void
    {
        $this->view = 'list';
        $this->newChatSearch = '';
        $this->userPickerResults = [];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Echo event handlers (called from Alpine via $wire.emit)
    // ──────────────────────────────────────────────────────────────────────

    /** New message arrived on a conversation channel. */
    public function handleIncomingMessage(int $conversationId, int $messageId, int $senderId): void
    {
        if ($senderId === (int) auth()->id()) {
            // Already added optimistically by sender flow.
            return;
        }
        if ((int) $conversationId === (int) $this->activeConversationId) {
            $this->loadMessages(initial: true);
            $this->markActiveConversationAsRead();
        }
        $this->loadConversations();
    }

    public function handleIncomingEdit(int $messageId): void
    {
        if ($this->activeConversationId) {
            $this->loadMessages(initial: true);
        }
    }

    public function handleIncomingDelete(int $messageId): void
    {
        if ($this->activeConversationId) {
            $this->loadMessages(initial: true);
        }
        $this->loadConversations();
    }

    public function handleIncomingRead(int $messageId, int $readerId): void
    {
        if ($this->activeConversationId) {
            $this->loadMessages(initial: true);
        }
    }

    public function handleIncomingReaction(int $messageId): void
    {
        if ($this->activeConversationId) {
            $this->loadMessages(initial: true);
        }
    }

    /**
     * A user (could be self or other) updated their status.
     * Refresh conversation list (so other-user status is up to date)
     * and the active conversation header.
     */
    public function handleIncomingStatus(int $userId): void
    {
        $this->loadConversations();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Status management (current user)
    // ──────────────────────────────────────────────────────────────────────

    public function setMyStatus(?string $status, ?string $message = null, ?string $onLeaveFrom = null, ?string $onLeaveUntil = null): void
    {
        try {
            $this->service()->setStatus(auth()->user(), $status, $message, $onLeaveFrom, $onLeaveUntil);
        } catch (\Throwable $e) {
            $this->addError('status', $e->getMessage());
            return;
        }
        $this->loadConversations();
    }

    public function clearMyStatus(): void
    {
        $this->service()->clearStatus(auth()->user());
        $this->loadConversations();
    }

    public function updateMyStatusMessage(string $message): void
    {
        $user = auth()->user();
        // Preserve current status, just update the message.
        $current = $user->status;
        try {
            $this->service()->setStatus(
                $user,
                $current,
                $message,
                $user->on_leave_from?->toDateString(),
                $user->on_leave_until?->toDateString()
            );
        } catch (\Throwable $e) {
            $this->addError('statusMessage', $e->getMessage());
        }
    }

    public function getMyStatusProperty(): array
    {
        $user = auth()->user();
        return [
            'status' => $user->effectiveStatus(),
            'status_message' => $user->status_message,
            'on_leave_from' => $user->on_leave_from?->toDateString(),
            'on_leave_until' => $user->on_leave_until?->toDateString(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Ticket badge rendering
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Parse message body and convert ticket references (e.g. QOS-101, #101)
     * into clickable badge links with tooltip previews.
     *
     * Returns HTML-safe string (body is escaped first, then badges injected).
     */
    /** @var array|null Cached project prefixes for this request */
    protected ?array $cachedPrefixes = null;

    protected function getProjectPrefixes(): array
    {
        if ($this->cachedPrefixes === null) {
            $this->cachedPrefixes = Project::whereNotNull('ticket_prefix')
                ->where('ticket_prefix', '!=', '')
                ->pluck('ticket_prefix')
                ->all();
        }
        return $this->cachedPrefixes;
    }

    protected function renderTicketBadges(?string $body): ?string
    {
        if ($body === null || $body === '') {
            return $body;
        }

        // Escape the entire body first for XSS safety.
        $escaped = e($body);

        // Collect all known project prefixes (cached per request).
        $prefixes = $this->getProjectPrefixes();

        if (! empty($prefixes)) {
            // Build regex: match PREFIX-NUMBER patterns (case-insensitive).
            $prefixPattern = implode('|', array_map(fn ($p) => preg_quote($p, '/'), $prefixes));
            $pattern = '/\b(' . $prefixPattern . ')-(\d+)\b/i';

            // Also match #NUMBER shorthand.
            $hashPattern = '/#(\d+)\b/';

            // For #NUMBER: resolve project if there's only one project, or fallback
            // to the single prefix if all prefixes are the same.
            $singleProject = count($prefixes) === 1 ? $prefixes[0] : null;

            // First pass: collect all ticket codes we need to look up.
            $codes = [];
            if (preg_match_all($pattern, $escaped, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $codes[] = strtoupper($match[1]) . '-' . $match[2];
                }
            }
            if ($singleProject && preg_match_all($hashPattern, $escaped, $hashMatches, PREG_SET_ORDER)) {
                foreach ($hashMatches as $match) {
                    $codes[] = strtoupper($singleProject) . '-' . $match[1];
                }
            }

            if (! empty($codes)) {
                // Batch-load tickets that exist (for tooltip data).
                $tickets = Ticket::whereIn('code', array_unique($codes))
                    ->with(['status:id,name,color', 'responsible:id,name', 'project:id,ticket_prefix'])
                    ->get()
                    ->keyBy('code');

                // Replace PREFIX-NUMBER patterns — always link, even if ticket doesn't exist yet.
                $escaped = preg_replace_callback($pattern, function ($match) use ($tickets) {
                    $code = strtoupper($match[1]) . '-' . $match[2];
                    $ticket = $tickets->get($code);
                    return $this->buildTicketBadgeHtml($code, $ticket);
                }, $escaped);

                // Replace #NUMBER patterns (only if single project).
                if ($singleProject) {
                    $escaped = preg_replace_callback($hashPattern, function ($match) use ($tickets, $singleProject) {
                        $code = strtoupper($singleProject) . '-' . $match[1];
                        $ticket = $tickets->get($code);
                        return $this->buildTicketBadgeHtml($code, $ticket);
                    }, $escaped);
                }
            }
        }

        // Auto-link bare URLs (skip URLs already inside <a> tags from ticket badges).
        $escaped = $this->autoLinkUrls($escaped);

        return $escaped;
    }

    protected function buildTicketBadgeHtml(string $code, ?Ticket $ticket): string
    {
        $url = route('filament.resources.tickets.share', ['ticket' => $code]);
        $escapedCode = e($code);

        if ($ticket) {
            $title = e($ticket->name);
            $status = e($ticket->status?->name ?? 'No status');
            $assignee = e($ticket->responsible?->name ?? 'Unassigned');

            return '<a href="' . $url . '" target="_blank" class="msgr-ticket-badge" data-ticket-title="' . $title . '" data-ticket-status="' . $status . '" data-ticket-assignee="' . $assignee . '">'
                . '<span class="msgr-ticket-badge-icon">#</span>'
                . $escapedCode
                . '</a>';
        }

        // Ticket not found — still link it, but no tooltip data.
        return '<a href="' . $url . '" target="_blank" class="msgr-ticket-badge msgr-ticket-badge-unknown">'
            . '<span class="msgr-ticket-badge-icon">#</span>'
            . $escapedCode
            . '</a>';
    }

    protected function autoLinkUrls(string $html): string
    {
        // Split HTML into tags and text nodes to avoid linking inside existing <a> tags.
        $parts = preg_split('/(<[^>]+>)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $insideA = 0;

        foreach ($parts as &$part) {
            if (preg_match('/^<(a|\/a)\b/i', $part, $tag)) {
                $t = strtolower($tag[1]);
                if ($t === 'a') $insideA++;
                elseif ($t === '/a') $insideA = max(0, $insideA - 1);
                continue;
            }

            if (str_starts_with($part, '<') || $insideA > 0) {
                continue;
            }

            // Replace bare URLs in text nodes.
            $part = preg_replace_callback(
                '#(https?://[^\s<>\'")\]]+)#i',
                function ($matches) {
                    $url = $matches[1];
                    $trailing = '';
                    // Strip trailing punctuation that's likely not part of the URL.
                    if (preg_match('/([.,;:!?\)]+)$/', $url, $punct)) {
                        $url = substr($url, 0, -strlen($punct[1]));
                        $trailing = $punct[1];
                    }
                    return '<a href="' . e($url) . '" target="_blank" rel="noopener" class="msgr-auto-link">' . e($url) . '</a>' . $trailing;
                },
                $part
            );
        }

        return implode('', $parts);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers exposed to Blade
    // ──────────────────────────────────────────────────────────────────────

    public function getActiveConversationProperty(): ?array
    {
        if (! $this->activeConversationId) {
            return null;
        }
        $conversation = MessengerConversation::with([
            'userOne:id,name,username,avatar_url,last_seen_at,status,status_message,status_until,on_leave_from,on_leave_until',
            'userTwo:id,name,username,avatar_url,last_seen_at,status,status_message,status_until,on_leave_from,on_leave_until',
        ])->find($this->activeConversationId);
        if (! $conversation) {
            return null;
        }
        $userId = (int) auth()->id();
        $other = $conversation->otherParticipant($userId);
        if (! $other) {
            return null;
        }
        return [
            'id'                    => $conversation->id,
            'other_id'              => $other->id,
            'other_name'            => $other->name,
            'other_username'        => $other->username,
            'other_avatar'          => $other->avatar_url,
            'other_last_seen_at'    => optional($other->last_seen_at)->toIso8601String(),
            'other_status'          => $other->effectiveStatus(),
            'other_status_message'  => $other->status_message,
            'other_on_leave_until'  => optional($other->on_leave_until)->toDateString(),
        ];
    }

    public function getAllowedReactionEmojisProperty(): array
    {
        return MessengerMessageReaction::ALLOWED_EMOJIS;
    }

    public function getCurrentUserIdProperty(): int
    {
        return (int) auth()->id();
    }
}
