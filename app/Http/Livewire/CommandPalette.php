<?php

namespace App\Http\Livewire;

use App\Models\Goal;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CommandPalette extends Component
{
    public bool $open = false;
    public string $query = '';

    /**
     * Toggle open state — called from Alpine/JS.
     */
    public function toggle(): void
    {
        $this->open = ! $this->open;
        if ($this->open === false) {
            $this->query = '';
        }
    }

    public function close(): void
    {
        $this->open = false;
        $this->query = '';
    }

    public function updatedQuery(): void
    {
        // Livewire auto re-renders; nothing to do server-side.
    }

    /**
     * Use Filament's own resolved navigation — it already filters by
     * shouldRegisterNavigation() and handles both resources + pages
     * uniformly, giving us NavigationItem objects we can just ask.
     */
    protected function navigationItems(): array
    {
        $cacheKey = 'cmd_palette_nav_' . (auth()->id() ?? 'guest');

        return Cache::remember($cacheKey, 300, function () {
            $items = [];

            foreach (Filament::getNavigation() as $group) {
                $groupLabel = $group->getLabel();

                foreach ($group->getItems() as $item) {
                    if (! $item instanceof \Filament\Navigation\NavigationItem) {
                        continue;
                    }

                    $items[] = [
                        'label' => $item->getLabel(),
                        'group' => $groupLabel,
                        'url' => $item->getUrl(),
                        'icon' => $item->getIcon() ?: 'heroicon-o-cube',
                        'type' => 'nav',
                    ];
                }
            }

            return $items;
        });
    }

    public function getResultsProperty(): array
    {
        $q = trim($this->query);
        $nav = $this->navigationItems();

        if ($q === '') {
            // Default: show all nav items grouped by their nav group
            return ['Navigation' => $nav];
        }

        $qLower = mb_strtolower($q);

        // Filter nav by label or group match
        $navFiltered = array_values(array_filter($nav, function ($item) use ($qLower) {
            return mb_stripos((string) $item['label'], $qLower) !== false
                || mb_stripos((string) ($item['group'] ?? ''), $qLower) !== false;
        }));

        $user = auth()->user();

        // Tickets — match on name OR code (code is a stored column like QOS-32)
        $ticketsQuery = Ticket::query()
            ->with(['status', 'project'])
            ->where(function ($inner) use ($q) {
                $inner->where('name', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%");
            });

        if ($user && ! ($user->hasRole('Super Admin') ?? false)) {
            $ticketsQuery->where(function ($inner) use ($user) {
                $inner->where('responsible_id', $user->id)
                      ->orWhere('owner_id', $user->id)
                      ->orWhereHas('project', function ($p) use ($user) {
                          $p->where('owner_id', $user->id)
                            ->orWhereHas('users', fn ($u) => $u->where('users.id', $user->id));
                      });
            });
        }

        $tickets = $ticketsQuery->latest('updated_at')->limit(6)->get()->map(function ($t) {
            return [
                'label' => '[' . ($t->code ?? $t->id) . '] ' . $t->name,
                'group' => $t->project?->name ?? 'Ticket',
                'url' => route('filament.resources.tickets.edit', $t),
                'icon' => 'heroicon-o-ticket',
                'type' => 'ticket',
            ];
        })->all();

        // Projects — match name or ticket prefix (users often search by prefix like "qos")
        $projectsQuery = Project::query()->where(function ($inner) use ($q) {
            $inner->where('name', 'like', "%{$q}%")
                  ->orWhere('ticket_prefix', 'like', "%{$q}%");
        });
        if ($user && ! ($user->hasRole('Super Admin') ?? false)) {
            $projectsQuery->where(function ($inner) use ($user) {
                $inner->where('owner_id', $user->id)
                      ->orWhereHas('users', fn ($u) => $u->where('users.id', $user->id));
            });
        }
        $projects = $projectsQuery->limit(5)->get()->map(fn ($p) => [
            'label' => $p->name,
            'group' => 'Project',
            'url' => route('filament.resources.projects.edit', $p),
            'icon' => 'heroicon-o-archive',
            'type' => 'project',
        ])->all();

        // Users — searchable by everyone (org is transparent)
        $users = User::query()
            ->where(function ($inner) use ($q) {
                $inner->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(fn ($u) => [
                'label' => $u->name,
                'group' => $u->email . ($u->department ? ' · ' . $u->department->name : ''),
                'url' => route('filament.resources.users.edit', $u),
                'icon' => 'heroicon-o-user',
                'type' => 'user',
            ])
            ->all();

        // Goals — match on title, code, or description
        $goals = Goal::query()
            ->where(function ($inner) use ($q) {
                $inner->where('title', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
            })
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($g) => [
                'label' => ($g->code ? '[' . $g->code . '] ' : '') . $g->title,
                'group' => 'Goal · ' . ucfirst($g->level),
                'url' => route('filament.resources.goals.edit', $g),
                'icon' => 'heroicon-o-flag',
                'type' => 'goal',
            ])
            ->all();

        $out = [];
        if (! empty($navFiltered)) $out['Navigation'] = $navFiltered;
        if (! empty($tickets)) $out['Tickets'] = $tickets;
        if (! empty($projects)) $out['Projects'] = $projects;
        if (! empty($users)) $out['People'] = $users;
        if (! empty($goals)) $out['Goals'] = $goals;

        return $out;
    }

    public function render()
    {
        return view('livewire.command-palette', [
            'results' => $this->results,
        ]);
    }
}
