<?php

namespace App\Filament\Widgets;

use App\Models\TicketActivity;
use App\Models\TicketComment;
use App\Models\WeeklyReport;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use App\Models\TicketStatus;

class EnhancedActivityFeed extends BaseWidget
{
    protected static ?int $sort = 8;
    protected static ?string $heading = 'Recent Activity Feed';

    protected int|string|array $columnSpan = [
        'sm' => 2,
        'md' => 6,
        'lg' => 6
    ];

    // Properties untuk filter
    public string $activityType = 'all'; // 'all', 'activities', 'comments', 'weekly_reports'

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
        // Buat union query untuk menggabungkan activities dan comments
        $activitiesQuery = TicketActivity::query()
            ->validStatuses() // Gunakan scope untuk filter status yang valid
            ->select([
                'id',
                'created_at',
                'user_id',
                'ticket_id',
                \DB::raw("'activity' as type"),
                \DB::raw("CASE
                    WHEN old_status_id IS NOT NULL AND new_status_id IS NOT NULL THEN
                        CONCAT('changed status from ',
                            COALESCE((SELECT name FROM ticket_statuses WHERE id = old_status_id), 'Unknown'),
                            ' to ',
                            COALESCE((SELECT name FROM ticket_statuses WHERE id = new_status_id), 'Unknown'))
                    ELSE 'updated ticket status'
                END as description"),
                'old_status_id',
                'new_status_id',
                \DB::raw('NULL as content')
            ])
            ->whereHas('ticket', function ($query) {
                return $query->where('owner_id', auth()->user()->id)
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhereHas('project', function ($query) {
                        return $query->where('owner_id', auth()->user()->id)
                            ->orWhereHas('users', function ($query) {
                                return $query->where('users.id', auth()->user()->id);
                            });
                    });
            });

        $commentsQuery = TicketComment::query()
            ->select([
                'id',
                'created_at',
                'user_id',
                'ticket_id',
                \DB::raw("'comment' as type"),
                \DB::raw("'added a comment' as description"),
                \DB::raw('NULL as old_status_id'),
                \DB::raw('NULL as new_status_id'),
                'content'
            ])
            ->whereHas('ticket', function ($query) {
                return $query->where('owner_id', auth()->user()->id)
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhereHas('project', function ($query) {
                        return $query->where('owner_id', auth()->user()->id)
                            ->orWhereHas('users', function ($query) {
                                return $query->where('users.id', auth()->user()->id);
                            });
                    });
            });

        $weeklyReportsQuery = WeeklyReport::query()
            ->where('status', '!=', 'draft')
            ->select([
                'id',
                \DB::raw('COALESCE(submitted_at, created_at) as created_at'),
                'user_id',
                \DB::raw('NULL as ticket_id'),
                \DB::raw("'weekly_report' as type"),
                \DB::raw("CONCAT('submitted weekly report (', DATE_FORMAT(week_start, '%b %d'), ' – ', DATE_FORMAT(week_end, '%b %d, %Y'), ')') as description"),
                \DB::raw('NULL as old_status_id'),
                \DB::raw('NULL as new_status_id'),
                \DB::raw('NULL as content'),
            ]);

        // Filter berdasarkan type
        if ($this->activityType === 'activities') {
            return $activitiesQuery->latest()->limit(10);
        } elseif ($this->activityType === 'comments') {
            return $commentsQuery->latest()->limit(10);
        } elseif ($this->activityType === 'weekly_reports') {
            return $weeklyReportsQuery->orderBy('created_at', 'desc')->limit(10);
        }

        // Union untuk semua
        return $activitiesQuery->union($commentsQuery)->union($weeklyReportsQuery)
            ->orderBy('created_at', 'desc')
            ->limit(10);
    }

    private function getUserAvatar($user): string
    {
        return $user->getAttributes()['avatar_url']
            ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('activity_info')
                ->label('Activity')
                ->extraAttributes(['style' => 'max-width:none;'])
                ->formatStateUsing(function ($state, $record) {
                    $user = \App\Models\User::find($record->user_id);
                    $ticket = \App\Models\Ticket::with('project')->find($record->ticket_id);

                    if (!$user) return new HtmlString('<span class="text-gray-400">-</span>');

                    $avatarUrl = $this->getUserAvatar($user);
                    $timeAgo = $record->created_at->diffForHumans();

                    // === Weekly Report ===
                    if ($record->type === 'weekly_report') {
                        $viewUrl = route('filament.resources.weekly-reports.view', $record->id);
                        $descriptionText = $record->getAttributes()['description'] ?? 'submitted weekly report';

                        return new HtmlString('
                            <div class="flex items-start gap-3 py-1">
                                <img src="' . e($avatarUrl) . '" class="w-9 h-9 rounded-full object-cover shrink-0 ring-2 ring-purple-500/20" loading="lazy" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white">' . e($user->name) . '</span>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">' . e($descriptionText) . '</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="text-xs text-gray-400 dark:text-gray-500">' . $timeAgo . '</span>
                                            <a href="' . $viewUrl . '" class="p-1 text-gray-400 hover:text-primary-500 rounded transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide rounded-full bg-purple-500/10 text-purple-500 ring-1 ring-purple-500/20">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                            Weekly Report
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ');
                    }

                    if (!$ticket) return new HtmlString('<span class="text-gray-400">-</span>');

                    $viewUrl = route('filament.resources.tickets.share', $ticket->code);
                    $descriptionText = $record->type === 'comment' ? __('added a comment') : ($record->getAttributes()['description'] ?? __('updated ticket'));

                    // === Status Change Badges ===
                    $statusBadges = '';
                    if ($record->type === 'activity' && $record->old_status_id && $record->new_status_id) {
                        $oldStatus = TicketStatus::withTrashed()->find($record->old_status_id);
                        $newStatus = TicketStatus::withTrashed()->find($record->new_status_id);
                        if ($oldStatus && $newStatus) {
                            $statusBadges = '
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md" style="background-color: ' . ($oldStatus->color ?? '#6B7280') . '20; color: ' . ($oldStatus->color ?? '#6B7280') . '">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: ' . ($oldStatus->color ?? '#6B7280') . '"></span>
                                        ' . e($oldStatus->name) . '
                                    </span>
                                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md" style="background-color: ' . ($newStatus->color ?? '#6B7280') . '20; color: ' . ($newStatus->color ?? '#6B7280') . '">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: ' . ($newStatus->color ?? '#6B7280') . '"></span>
                                        ' . e($newStatus->name) . '
                                    </span>
                                </div>';
                        }
                    }

                    // === Comment Preview ===
                    $commentPreview = '';
                    if ($record->type === 'comment' && $record->content) {
                        $plainText = Str::limit(strip_tags(Str::markdown($record->content)), 120);
                        $commentPreview = '
                            <div class="mt-1.5 px-3 py-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg border-l-2 border-green-500/50">
                                <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">' . e($plainText) . '</p>
                            </div>';
                    }

                    $ringColor = $record->type === 'activity' ? 'ring-blue-500/20' : 'ring-green-500/20';

                    return new HtmlString('
                        <div class="flex items-start gap-3 py-1">
                            <img src="' . e($avatarUrl) . '" class="w-9 h-9 rounded-full object-cover shrink-0 ring-2 ' . $ringColor . '" loading="lazy" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">' . e($user->name) . '</span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">' . e($descriptionText) . '</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-xs text-gray-400 dark:text-gray-500">' . $timeAgo . '</span>
                                        <a href="' . $viewUrl . '" target="_blank" class="p-1 text-gray-400 hover:text-primary-500 rounded transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded bg-primary-500/10 text-primary-500">' . e($ticket->project->name) . '</span>
                                    <span class="text-xs font-mono text-gray-400 dark:text-gray-500">' . e($ticket->code) . '</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate">' . e(Str::limit($ticket->name, 45)) . '</span>
                                </div>
                                ' . $statusBadges . '
                                ' . $commentPreview . '
                            </div>
                        </div>
                    ');
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('type')
                ->label('Activity Type')
                ->options([
                    'all' => 'All Activities',
                    'activities' => 'Status Changes Only',
                    'comments' => 'Comments Only',
                    'weekly_reports' => 'Weekly Reports Only',
                ])
                ->default('all')
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['value'])) {
                        $this->activityType = $data['value'];
                    }
                    return $this->getTableQuery();
                }),
        ];
    }

    public function getTableDescription(): ?string
    {
        $typeDesc = match($this->activityType) {
            'activities' => 'status changes',
            'comments' => 'comments',
            'weekly_reports' => 'weekly reports',
            default => 'activities, comments, and weekly reports'
        };

        return "Recent {$typeDesc} from tickets you own, are responsible for, or from projects you're involved in.";
    }
}
