<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle CC users
        if (isset($data['cc_users'])) {
            $ccUsers = $data['cc_users'];
            unset($data['cc_users']);
            $this->ccUsers = $ccUsers;
        }

        // Auto-set request_status for Request type tickets
        $requestTypeId = \App\Models\TicketType::where('name', 'Request')->first()?->id;
        if (isset($data['type_id']) && $data['type_id'] == $requestTypeId) {
            $data['request_status'] = 'pending';
            // Set status to "Request" status
            $requestStatus = \App\Models\TicketStatus::where('name', 'Request')->first();
            if ($requestStatus) {
                $data['status_id'] = $requestStatus->id;
            }
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
}