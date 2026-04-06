<?php

namespace App\Filament\Resources\DiscussionResource\Pages;

use App\Filament\Resources\DiscussionResource;
use App\Models\User;
use App\Notifications\DiscussionCreated;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscussion extends CreateRecord
{
    protected static string $resource = DiscussionResource::class;

    protected function afterCreate(): void
    {
        $discussion = $this->record->load(['user', 'project']);

        // Notify users in the same project, or all if general
        if ($discussion->project_id) {
            $notifyUsers = User::where('id', '!=', auth()->id())
                ->where(function ($q) use ($discussion) {
                    $q->whereHas('projects', fn ($p) => $p->where('projects.id', $discussion->project_id))
                        ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['Super Admin', 'Stakeholder']));
                })
                ->get();
        } else {
            $notifyUsers = User::where('id', '!=', auth()->id())->get();
        }

        foreach ($notifyUsers as $user) {
            $user->notify(new DiscussionCreated($discussion));
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
