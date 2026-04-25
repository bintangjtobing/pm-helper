<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\UserWebhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TicketStatusWebhookObserver
{
    private const QA_FAILED_STATUS_ID = 7;

    public function updated(Ticket $ticket): void
    {
        if (! $ticket->wasChanged('status_id') || (int) $ticket->status_id !== self::QA_FAILED_STATUS_ID) {
            return;
        }

        $webhooks = UserWebhook::query()
            ->where('event', UserWebhook::EVENT_QA_FAILED)
            ->where('is_active', true)
            ->whereNotNull('url')
            ->where('user_id', $ticket->responsible_id)
            ->get();

        if ($webhooks->isEmpty()) {
            return;
        }

        $payload = [
            'event' => 'ticket.status_changed',
            'new_status_id' => self::QA_FAILED_STATUS_ID,
            'old_status_id' => $ticket->getOriginal('status_id'),
            'ticket' => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'name' => $ticket->name,
                'project_id' => $ticket->project_id,
                'responsible_id' => $ticket->responsible_id,
                'owner_id' => $ticket->owner_id,
                'updated_at' => optional($ticket->updated_at)->toIso8601String(),
                'updated_by' => auth()->id(),
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        foreach ($webhooks as $webhook) {
            if (! $webhook->matchesProject((int) $ticket->project_id)) {
                continue;
            }
            $this->fire($webhook, $payload, $ticket->id);
        }
    }

    private function fire(UserWebhook $webhook, array $payload, int $ticketId): void
    {
        try {
            $response = Http::timeout(2)
                ->withHeaders(array_filter([
                    'X-Webhook-Secret' => $webhook->secret,
                ]))
                ->post($webhook->url, $payload);

            $webhook->forceFill([
                'last_fired_at' => now(),
                'last_status' => $response->status(),
                'last_error' => $response->successful() ? null : substr((string) $response->body(), 0, 1000),
            ])->saveQuietly();
        } catch (\Throwable $e) {
            $webhook->forceFill([
                'last_fired_at' => now(),
                'last_status' => null,
                'last_error' => substr($e->getMessage(), 0, 1000),
            ])->saveQuietly();

            Log::warning('QA Failed webhook delivery failed', [
                'webhook_id' => $webhook->id,
                'user_id' => $webhook->user_id,
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
