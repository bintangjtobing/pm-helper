<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class LatestTickets extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];

    public function mount(): void
    {
        self::$heading = __('Latest tickets');
    }

    public static function canView(): bool
    {
        return auth()->user()->can('List tickets');
    }

    protected function getTableQuery(): Builder
    {
        return Ticket::query()
            ->limit(5)
            ->where(function ($query) {
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

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('Ticket'))
                ->formatStateUsing(function ($record) {
                    $responsibleHtml = '';
                    if ($record->responsible) {
                        $avatar = $record->responsible->getAttributes()['avatar_url']
                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($record->responsible->name) . '&size=64&background=' . substr(md5($record->responsible->id), 0, 6) . '&color=ffffff');
                        $responsibleHtml = '
                            <div class="flex items-center gap-1.5 mt-1">
                                <img src="' . e($avatar) . '" class="w-4 h-4 rounded-full" loading="lazy" />
                                <span class="text-xs text-gray-400 dark:text-gray-500">' . e($record->responsible->name) . '</span>
                            </div>';
                    }

                    return new HtmlString('
                        <div class="flex flex-col gap-0.5">
                            <span class="px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded bg-primary-500/10 text-primary-500 self-start">'
                                . e($record->project->name) .
                            '</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-mono text-gray-400 dark:text-gray-500">' . e($record->code) . '</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">' . e($record->name) . '</span>
                            </div>
                            ' . $responsibleHtml . '
                        </div>
                    ');
                }),

            Tables\Columns\TextColumn::make('status.name')
                ->label(__('Status'))
                ->formatStateUsing(fn($record) => new HtmlString(
                    '<span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-medium rounded-md" style="background-color: ' . $record->status->color . '20; color: ' . $record->status->color . '">'
                    . '<span class="w-1.5 h-1.5 rounded-full" style="background-color: ' . $record->status->color . '"></span>'
                    . e($record->status->name)
                    . '</span>'
                )),

            Tables\Columns\TextColumn::make('priority.name')
                ->label(__('Priority'))
                ->formatStateUsing(fn($record) => new HtmlString(
                    '<span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-medium rounded-md" style="background-color: ' . $record->priority->color . '20; color: ' . $record->priority->color . '">'
                    . '<span class="w-1.5 h-1.5 rounded-full" style="background-color: ' . $record->priority->color . '"></span>'
                    . e($record->priority->name)
                    . '</span>'
                )),
        ];
    }
}
