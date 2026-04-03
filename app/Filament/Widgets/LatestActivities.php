<?php

namespace App\Filament\Widgets;

use App\Models\TicketActivity;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class LatestActivities extends BaseWidget
{
    protected static ?int $sort = 9;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];

    public function mount(): void
    {
        self::$heading = __('Latest tickets activities');
    }

    public static function canView(): bool
    {
        return auth()->user()->can('List tickets');
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableQuery(): Builder
    {
        return TicketActivity::query()
            ->limit(5)
            ->whereHas('ticket', function ($query) {
                return $query->where('owner_id', auth()->user()->id)
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhereHas('project', function ($query) {
                        return $query->where('owner_id', auth()->user()->id)
                            ->orWhereHas('users', function ($query) {
                                return $query->where('users.id', auth()->user()->id);
                            });
                    });
            })
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('ticket')
                ->label(__('Ticket'))
                ->formatStateUsing(function ($record, $state) {
                    return new HtmlString('
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-mono text-gray-400 dark:text-gray-500">' . e($state->code) . '</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">' . e($state->name) . '</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded" style="background-color: ' . $record->oldStatus->color . '20; color: ' . $record->oldStatus->color . '">'
                                    . e($record->oldStatus->name) .
                                '</span>
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded" style="background-color: ' . $record->newStatus->color . '20; color: ' . $record->newStatus->color . '">'
                                    . e($record->newStatus->name) .
                                '</span>
                            </div>
                        </div>
                    ');
                }),

            Tables\Columns\TextColumn::make('user.name')
                ->label(__('By'))
                ->formatStateUsing(function ($record) {
                    $user = $record->user;
                    if (!$user) return '-';
                    $avatar = $user->getAttributes()['avatar_url']
                        ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
                    return new HtmlString('
                        <div class="flex items-center gap-2">
                            <img src="' . e($avatar) . '" class="w-6 h-6 rounded-full" loading="lazy" />
                            <span class="text-xs text-gray-700 dark:text-gray-300">' . e($user->name) . '</span>
                        </div>
                    ');
                }),

            Tables\Columns\TextColumn::make('created_at')
                ->label(__('When'))
                ->since(),
        ];
    }
}
