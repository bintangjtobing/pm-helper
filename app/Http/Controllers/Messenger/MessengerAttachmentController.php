<?php

namespace App\Http\Controllers\Messenger;

use App\Http\Controllers\Controller;
use App\Models\MessengerMessage;
use App\Models\MessengerMessageAttachment;
use App\Services\MessengerService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class MessengerAttachmentController extends Controller
{
    public function __construct(protected MessengerService $messenger)
    {
    }

    /**
     * Force-download an attachment.
     */
    public function download(MessengerMessage $message, MessengerMessageAttachment $attachment): BinaryFileResponse
    {
        $this->authorizeAccess($message, $attachment);

        $absolutePath = $this->messenger->resolveAttachmentPath($attachment);
        if (! $absolutePath) {
            abort(Response::HTTP_NOT_FOUND, 'Attachment file not found.');
        }

        return response()->download(
            $absolutePath,
            $attachment->filename_original,
            [
                'Content-Type' => $attachment->mime_type,
            ]
        );
    }

    /**
     * Inline preview (for images shown in chat bubble).
     */
    public function preview(MessengerMessage $message, MessengerMessageAttachment $attachment): BinaryFileResponse
    {
        $this->authorizeAccess($message, $attachment);

        $absolutePath = $this->messenger->resolveAttachmentPath($attachment);
        if (! $absolutePath) {
            abort(Response::HTTP_NOT_FOUND, 'Attachment file not found.');
        }

        return response()->file(
            $absolutePath,
            [
                'Content-Type'  => $attachment->mime_type,
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }

    /**
     * Authorization gate: user must be logged in, attachment must belong to the message,
     * and the message must belong to a conversation the user is a participant of.
     */
    protected function authorizeAccess(MessengerMessage $message, MessengerMessageAttachment $attachment): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if ((int) $attachment->message_id !== (int) $message->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $conversation = $message->conversation;
        if (! $conversation || ! $conversation->hasParticipant((int) $user->id)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        // Block access to attachments on messages deleted-for-everyone.
        if ($message->isDeletedForEveryone()) {
            abort(Response::HTTP_GONE);
        }

        // Block access if the requesting user has hidden the message for themselves.
        if ($message->isHiddenFor((int) $user->id)) {
            abort(Response::HTTP_FORBIDDEN);
        }
    }
}
