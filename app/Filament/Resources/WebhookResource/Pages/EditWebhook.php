<?php

namespace App\Filament\Resources\WebhookResource\Pages;

use App\Filament\Resources\WebhookResource;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;

class EditWebhook extends EditRecord
{
    protected static string $resource = WebhookResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('testFire')
                ->label('Test fire')
                ->icon('heroicon-o-paper-airplane')
                ->color('secondary')
                ->requiresConfirmation()
                ->modalHeading('Send test payload to your webhook?')
                ->modalSubheading('PM Helper will POST a sample QA Failed payload to your URL right now. Use this to verify your endpoint is reachable and parsing the payload correctly.')
                ->modalButton('Send test')
                ->action(function () {
                    $webhook = $this->record;

                    if (empty($webhook->url)) {
                        Notification::make()
                            ->title('No URL configured')
                            ->body('Save a Webhook URL before firing a test.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $payload = [
                        'event' => 'ticket.status_changed',
                        'new_status_id' => 7,
                        'old_status_id' => 6,
                        'ticket' => [
                            'id' => 0,
                            'code' => 'TEST-1',
                            'name' => 'Test fire from PM Helper Webhooks settings',
                            'project_id' => null,
                            'responsible_id' => $webhook->user_id,
                            'owner_id' => $webhook->user_id,
                            'updated_at' => now()->toIso8601String(),
                            'updated_by' => auth()->id(),
                        ],
                        'timestamp' => now()->toIso8601String(),
                        '_test' => true,
                    ];

                    try {
                        $response = Http::timeout(5)
                            ->withHeaders(array_filter([
                                'X-Webhook-Secret' => $webhook->secret,
                            ]))
                            ->post($webhook->url, $payload);

                        $webhook->forceFill([
                            'last_fired_at' => now(),
                            'last_status' => $response->status(),
                            'last_error' => $response->successful() ? null : substr((string) $response->body(), 0, 1000),
                        ])->saveQuietly();

                        if ($response->successful()) {
                            Notification::make()
                                ->title('Test fired successfully')
                                ->body('HTTP ' . $response->status() . ' — your endpoint accepted the payload.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Endpoint returned ' . $response->status())
                                ->body('Test request reached the endpoint but it responded with a non-2xx status. See "Last error" for details.')
                                ->warning()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        $webhook->forceFill([
                            'last_fired_at' => now(),
                            'last_status' => null,
                            'last_error' => substr($e->getMessage(), 0, 1000),
                        ])->saveQuietly();

                        Notification::make()
                            ->title('Test failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
