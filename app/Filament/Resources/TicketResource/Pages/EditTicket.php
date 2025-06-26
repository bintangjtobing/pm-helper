<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Notifications\TicketStatusUpdated;
use App\Models\TicketActivity;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle CC users
        if (isset($data['cc_users'])) {
            $ccUsers = $data['cc_users'];
            unset($data['cc_users']);

            // We'll attach CC users after the ticket is saved
            $this->ccUsers = $ccUsers;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Attach CC users if provided
        if (isset($this->ccUsers)) {
            $this->record->ccUsers()->sync($this->ccUsers);
        }
    }

    protected function beforeSave(): void
    {
        // Store old status for comparison
        $this->oldStatusId = $this->record->getOriginal('status_id');
    }

    protected function saved(): void
    {
        // Check if status was changed and trigger notifications manually
        if ($this->oldStatusId && $this->oldStatusId != $this->record->status_id) {

            // Create activity record
            TicketActivity::create([
                'ticket_id' => $this->record->id,
                'old_status_id' => $this->oldStatusId,
                'new_status_id' => $this->record->status_id,
                'user_id' => auth()->user()->id
            ]);

            // Get fresh ticket with watchers
            $freshTicket = $this->record->load('watchers', 'status');

            // Send notifications to all watchers
            foreach ($freshTicket->watchers as $user) {
                $user->notify(new TicketStatusUpdated($freshTicket));
            }

            // Log the notification for debugging
            \Log::info('Status update notification sent', [
                'ticket_id' => $this->record->id,
                'old_status' => $this->oldStatusId,
                'new_status' => $this->record->status_id,
                'watchers_count' => $freshTicket->watchers->count()
            ]);
        }
    }
}
