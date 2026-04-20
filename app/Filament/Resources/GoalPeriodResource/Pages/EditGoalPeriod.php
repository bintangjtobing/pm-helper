<?php

namespace App\Filament\Resources\GoalPeriodResource\Pages;

use App\Filament\Resources\GoalPeriodResource;
use App\Services\Goals\GoalReviewGenerator;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGoalPeriod extends EditRecord
{
    protected static string $resource = GoalPeriodResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('generateReviews')
                ->label('Generate Reviews')
                ->icon('heroicon-o-clipboard-check')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Generate Reviews for this Period')
                ->modalSubheading('This creates a review for every user who owns an Objective in this period. Existing reviews are left alone; new Key Results are snapshotted.')
                ->action(function () {
                    $count = app(GoalReviewGenerator::class)->generate($this->record);
                    Notification::make()
                        ->title("Generated {$count} new review(s).")
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
