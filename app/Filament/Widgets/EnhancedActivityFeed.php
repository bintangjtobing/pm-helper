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
            Tables\Columns\ViewColumn::make('activity_info')
                ->label('Activity')
                ->view('partials.filament.widgets.activity-feed-row'),
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
