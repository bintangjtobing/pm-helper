<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class TeamActivity extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-status-online';
    protected static ?string $navigationLabel = 'Team Activity';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'team-activity';
    protected static string $view = 'filament.pages.team-activity';

    public ?string $filterPresence = 'all';
    public ?string $filterDepartment = null;
    public ?string $filterRole = null;
    public ?string $filterSearch = null;

    protected static function getNavigationGroup(): ?string
    {
        return __('Team');
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('Update user') ?? false;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('Update user'), 403);
    }

    protected function getTitle(): string
    {
        return __('Team Activity');
    }

    public function resetFilters(): void
    {
        $this->filterPresence = 'all';
        $this->filterDepartment = null;
        $this->filterRole = null;
        $this->filterSearch = null;
    }

    public function getViewData(): array
    {
        $now = now();

        $query = User::query()->with(['department', 'position', 'roles']);

        if ($this->filterDepartment) {
            $query->where('department_id', $this->filterDepartment);
        }

        if ($this->filterRole) {
            $query->whereHas('roles', fn ($q) => $q->where('roles.id', $this->filterRole));
        }

        if ($this->filterSearch) {
            $term = trim($this->filterSearch);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('username', 'like', "%{$term}%");
            });
        }

        $users = $query->orderByRaw('last_seen_at IS NULL')
            ->orderByDesc('last_seen_at')
            ->get();

        $enriched = $users->map(function ($u) use ($now) {
            $lastSeen = $u->last_seen_at;
            $idleDays = $lastSeen ? (int) $lastSeen->diffInDays($now) : null;

            if (!$lastSeen) {
                $bucket = 'never';
                $label = __('Never logged in');
                $tone = 'gray';
            } else {
                $minutes = $lastSeen->diffInMinutes($now);
                if ($minutes <= 60) {
                    $bucket = 'active';
                    $label = __('Active now');
                    $tone = 'emerald';
                } elseif ($lastSeen->isToday()) {
                    $bucket = 'active';
                    $label = __('Today, :time', ['time' => $lastSeen->format('H:i')]);
                    $tone = 'emerald';
                } elseif ($idleDays <= 1) {
                    $bucket = 'recent';
                    $label = __('Yesterday');
                    $tone = 'amber';
                } elseif ($idleDays <= 7) {
                    $bucket = 'recent';
                    $label = __(':n days ago', ['n' => $idleDays]);
                    $tone = 'amber';
                } elseif ($idleDays <= 30) {
                    $bucket = 'idle';
                    $label = __(':n days idle', ['n' => $idleDays]);
                    $tone = 'orange';
                } else {
                    $bucket = 'dormant';
                    $label = __(':n days idle', ['n' => $idleDays]);
                    $tone = 'red';
                }
            }

            $u->presence_bucket = $bucket;
            $u->presence_label = $label;
            $u->presence_tone = $tone;
            $u->idle_days = $idleDays;
            return $u;
        });

        if ($this->filterPresence && $this->filterPresence !== 'all') {
            $enriched = $enriched->filter(fn ($u) => $u->presence_bucket === $this->filterPresence)->values();
        }

        $userIds = $enriched->pluck('id')->all();

        $ticketCounts = collect();
        if (!empty($userIds)) {
            $ticketCounts = Ticket::whereIn('responsible_id', $userIds)
                ->whereNull('deleted_at')
                ->whereNotIn('status_id', [12, 15])
                ->selectRaw('responsible_id, COUNT(*) as cnt')
                ->groupBy('responsible_id')
                ->pluck('cnt', 'responsible_id');
        }

        $stats = [
            'total' => $users->count(),
            'active' => $users->filter(fn ($u) => $u->last_seen_at && $u->last_seen_at->diffInHours($now) <= 24)->count(),
            'this_week' => $users->filter(fn ($u) => $u->last_seen_at && $u->last_seen_at->diffInDays($now) <= 7)->count(),
            'idle' => $users->filter(fn ($u) => $u->last_seen_at && $u->last_seen_at->diffInDays($now) > 7)->count(),
            'never' => $users->filter(fn ($u) => !$u->last_seen_at)->count(),
        ];

        return [
            'users' => $enriched,
            'stats' => $stats,
            'ticketCounts' => $ticketCounts,
            'departments' => Department::orderBy('sort_order')->get(),
            'roles' => Role::orderBy('name')->get(),
        ];
    }
}
