<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\TicketActivity;
use App\Models\TicketStatus;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ActivityLog extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';
    protected static ?string $navigationLabel = 'Activity Log';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'activity-log';
    protected static string $view = 'filament.pages.activity-log';

    // Filters
    public ?string $filterUser = null;
    public ?string $filterProject = null;
    public ?string $filterStatus = null;
    public string $filterDateRange = '7';
    public ?string $filterDateFrom = null;
    public ?string $filterDateTo = null;

    // View mode
    public string $viewMode = 'timeline'; // 'timeline' | 'tree'

    // Data
    public int $perPage = 50;
    public int $currentPage = 1;

    protected static function getNavigationGroup(): ?string
    {
        return __('Workspace');
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('List tickets');
    }

    protected function getTitle(): string
    {
        return __('Activity Log');
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->can('List tickets'), 403);
    }

    public function getActivitiesProperty(): Collection
    {
        $query = TicketActivity::query()
            ->validStatuses()
            ->with(['ticket.project', 'oldStatus', 'newStatus', 'user'])
            ->whereHas('ticket')
            ->whereHas('user');

        // Scope to user's projects unless Super Admin / PM
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Project Manager'])) {
            $query->whereHas('ticket', function ($q) {
                $q->whereHas('project', function ($pq) {
                    $pq->whereHas('users', fn ($uq) => $uq->where('users.id', auth()->id()));
                });
            });
        }

        // Filter: user
        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        // Filter: project
        if ($this->filterProject) {
            $query->whereHas('ticket', fn ($q) => $q->where('project_id', $this->filterProject));
        }

        // Filter: target status
        if ($this->filterStatus) {
            $query->where('new_status_id', $this->filterStatus);
        }

        // Filter: date range
        if ($this->filterDateRange === 'custom' && $this->filterDateFrom && $this->filterDateTo) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->filterDateFrom)->startOfDay(),
                Carbon::parse($this->filterDateTo)->endOfDay(),
            ]);
        } elseif ($this->filterDateRange !== 'all') {
            $days = (int) $this->filterDateRange;
            $query->where('created_at', '>=', now()->subDays($days)->startOfDay());
        }

        return $query->latest()
            ->limit($this->perPage * $this->currentPage)
            ->get();
    }

    public function getStatsProperty(): array
    {
        $activities = $this->activities;
        $totalChanges = $activities->count();
        $uniqueUsers = $activities->pluck('user_id')->unique()->count();
        $uniqueTickets = $activities->pluck('ticket_id')->unique()->count();

        // Most active status transition
        $topTransition = $activities->groupBy(fn ($a) => ($a->oldStatus?->name ?? '?') . ' → ' . ($a->newStatus?->name ?? '?'))
            ->sortByDesc(fn ($group) => $group->count())
            ->keys()
            ->first();

        return [
            'total_changes' => $totalChanges,
            'unique_users' => $uniqueUsers,
            'unique_tickets' => $uniqueTickets,
            'top_transition' => $topTransition,
        ];
    }

    public function getUsersProperty(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function getProjectsProperty(): Collection
    {
        $query = Project::orderBy('name');
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Project Manager'])) {
            $query->whereHas('users', fn ($q) => $q->where('users.id', auth()->id()));
        }
        return $query->get(['id', 'name']);
    }

    public function getStatusesProperty(): Collection
    {
        return TicketStatus::orderBy('name')->get(['id', 'name', 'color']);
    }

    public function applyFilters(): void
    {
        $this->currentPage = 1;
    }

    public function resetFilters(): void
    {
        $this->filterUser = null;
        $this->filterProject = null;
        $this->filterStatus = null;
        $this->filterDateRange = '7';
        $this->filterDateFrom = null;
        $this->filterDateTo = null;
        $this->currentPage = 1;
    }

    public function loadMore(): void
    {
        $this->currentPage++;
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    /**
     * Group activities as: Project → Ticket → Activities (for tree view)
     */
    public function getTreeDataProperty(): Collection
    {
        return $this->activities
            ->filter(fn ($a) => $a->ticket && $a->ticket->project)
            ->groupBy(fn ($a) => $a->ticket->project->id)
            ->map(function ($projectActivities) {
                $project = $projectActivities->first()->ticket->project;
                $tickets = $projectActivities->groupBy('ticket_id')->map(function ($ticketActivities) {
                    $ticket = $ticketActivities->first()->ticket;
                    return [
                        'ticket' => $ticket,
                        'activities' => $ticketActivities->sortByDesc('created_at')->values(),
                        'users' => $ticketActivities->pluck('user')->unique('id')->values(),
                    ];
                })->sortByDesc(fn ($t) => $t['activities']->first()->created_at)->values();

                return [
                    'project' => $project,
                    'tickets' => $tickets,
                    'total_changes' => $projectActivities->count(),
                    'unique_users' => $projectActivities->pluck('user_id')->unique()->count(),
                ];
            })->sortByDesc('total_changes')->values();
    }
}
