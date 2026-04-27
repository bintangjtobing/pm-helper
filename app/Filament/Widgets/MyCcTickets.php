<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class MyCcTickets extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3,
    ];

    public function mount(): void
    {
        self::$heading = __("Tickets I'm CC'd on");
    }

    public static function canView(): bool
    {
        return (bool) auth()->user();
    }

    protected const ACTIVITY_WINDOW_DAYS = 14;

    protected function getTableQuery(): Builder
    {
        $userId = auth()->id();
        $cutoff = now()->subDays(self::ACTIVITY_WINDOW_DAYS);

        return Ticket::query()
            ->select('tickets.*')
            ->selectRaw(
                'COALESCE((SELECT MAX(created_at) FROM ticket_comments WHERE ticket_id = tickets.id AND deleted_at IS NULL), tickets.updated_at) as last_activity_at'
            )
            ->whereHas('ccUsers', fn ($q) => $q->where('users.id', $userId))
            ->where(function ($q) use ($cutoff) {
                $q->where('tickets.updated_at', '>=', $cutoff)
                  ->orWhereExists(function ($sub) use ($cutoff) {
                      $sub->from('ticket_comments')
                          ->whereColumn('ticket_comments.ticket_id', 'tickets.id')
                          ->whereNull('ticket_comments.deleted_at')
                          ->where('ticket_comments.created_at', '>=', $cutoff);
                  });
            })
            ->with(['project:id,name', 'status:id,name,color', 'responsible:id,name,avatar_url'])
            ->orderByDesc('last_activity_at')
            ->limit(5);
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

                    $projectName = optional($record->project)->name;
                    $projectPill = $projectName
                        ? '<span class="px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded bg-primary-500/10 text-primary-500 self-start">' . e($projectName) . '</span>'
                        : '';

                    return new HtmlString('
                        <div class="flex flex-col gap-0.5">
                            ' . $projectPill . '
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-mono text-gray-400 dark:text-gray-500">' . e($record->code) . '</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">' . e($record->name) . '</span>
                            </div>
                            ' . $responsibleHtml . '
                        </div>
                    ');
                })
                ->url(fn ($record): string => route('filament.resources.tickets.view', $record)),

            Tables\Columns\TextColumn::make('status.name')
                ->label(__('Status'))
                ->formatStateUsing(fn ($record) => $record->status ? new HtmlString(
                    '<span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-medium rounded-md" style="background-color: ' . $record->status->color . '20; color: ' . $record->status->color . '">'
                    . '<span class="w-1.5 h-1.5 rounded-full" style="background-color: ' . $record->status->color . '"></span>'
                    . e($record->status->name)
                    . '</span>'
                ) : ''),

            Tables\Columns\TextColumn::make('last_activity_at')
                ->label(__('Last activity'))
                ->formatStateUsing(function ($state) {
                    if (! $state) {
                        return new HtmlString('<span class="text-xs text-gray-400">—</span>');
                    }

                    $when = Carbon::parse($state);

                    return new HtmlString(
                        '<span class="text-xs text-gray-500 dark:text-gray-400" title="' . e($when->toDayDateTimeString()) . '">'
                        . e($when->diffForHumans())
                        . '</span>'
                    );
                }),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('Nothing to watch');
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return __("No CC'd tickets with activity in the last :days days.", ['days' => self::ACTIVITY_WINDOW_DAYS]);
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-eye-off';
    }
}
