<?php

namespace App\Helpers;

use App\Events\TicketMoved;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

trait KanbanScrumHelper
{
    public bool $sortable = true;

    public Project|null $project = null;

    public $users = [];
    public $types = [];
    public $priorities = [];
    public $includeNotAffectedTickets = false;
    public string $sortBy = 'updated_at';

    public bool $ticket = false;

    public ?int $selectedStatusId = null;

    protected function formSchema(): array
    {
        return [
            Grid::make([
                'default' => 2,
                'md' => 6
            ])
                ->schema([
                    Select::make('users')
                        ->label(__('Owners / Responsibles'))
                        ->multiple()
                        ->options(User::all()->pluck('name', 'id')),

                    Select::make('types')
                        ->label(__('Ticket types'))
                        ->multiple()
                        ->options(TicketType::all()->pluck('name', 'id')),

                    Select::make('priorities')
                        ->label(__('Ticket priorities'))
                        ->multiple()
                        ->options(TicketPriority::all()->pluck('name', 'id')),

                    Toggle::make('includeNotAffectedTickets')
                        ->label(__('Show only not affected tickets'))
                        ->columnSpan(2),

                    Placeholder::make('search')
                        ->label(new HtmlString('&nbsp;'))
                        ->content(new HtmlString('
                            <button type="button"
                                    wire:click="filter" wire:loading.attr="disabled"
                                    class="px-3 py-2 text-white rounded bg-primary-500 hover:bg-primary-600 disabled:bg-primary-300">
                                ' . __('Filter') . '
                            </button>
                            <button type="button"
                                    wire:click="resetFilters" wire:loading.attr="disabled"
                                    class="px-3 py-2 ml-2 text-white bg-gray-800 rounded hover:bg-gray-900 disabled:bg-gray-300">
                                ' . __('Reset filters') . '
                            </button>
                        ')),
                ]),
        ];
    }

    public function getStatuses(): Collection
    {
        $query = TicketStatus::query();
        if ($this->project && $this->project->status_type === 'custom') {
            $query->where('project_id', $this->project->id);
        } else {
            $query->whereNull('project_id');
        }

        // Performance: single grouped count query instead of N+1
        $ticketCounts = collect();
        if ($this->project) {
            $ticketCounts = Ticket::where('project_id', $this->project->id)
                ->selectRaw('status_id, count(*) as count')
                ->groupBy('status_id')
                ->pluck('count', 'status_id');
        }

        $canCreateTicket = auth()->user()->can('Create ticket');

        return $query->orderBy('order')
            ->get()
            ->map(function ($item) use ($ticketCounts, $canCreateTicket) {
                return [
                    'id' => $item->id,
                    'title' => $item->name,
                    'color' => $item->color,
                    'size' => $ticketCounts->get($item->id, 0),
                    'add_ticket' => $canCreateTicket,
                    'can_drop' => $item->canBeSetByUser(),
                    'role_group' => $item->role_group,
                ];
            });
    }

    public function getRecords(): Collection
    {
        $query = Ticket::query();
        if ($this->project->type === 'scrum') {
            $query->where('sprint_id', $this->project->currentSprint->id);
        }
        $query->with(['project', 'owner', 'responsible', 'status', 'type', 'priority', 'epic', 'relations.relation']);
        $query->where('project_id', $this->project->id);
        if (sizeof($this->users)) {
            $query->where(function ($query) {
                return $query->whereIn('owner_id', $this->users)
                    ->orWhereIn('responsible_id', $this->users);
            });
        }
        if (sizeof($this->types)) {
            $query->whereIn('type_id', $this->types);
        }
        if (sizeof($this->priorities)) {
            $query->whereIn('priority_id', $this->priorities);
        }
        if ($this->includeNotAffectedTickets) {
            $query->whereNull('responsible_id');
        }
        $query->where(function ($query) {
            return $query->where('owner_id', auth()->user()->id)
                ->orWhere('responsible_id', auth()->user()->id)
                ->orWhereHas('project', function ($query) {
                    return $query->where('owner_id', auth()->user()->id)
                        ->orWhereHas('users', function ($query) {
                            return $query->where('users.id', auth()->user()->id);
                        });
                });
        });
        // Apply sort
        match ($this->sortBy) {
            'updated_at' => $query->orderByDesc('updated_at'),
            'created_at' => $query->orderByDesc('created_at'),
            'priority' => $query->orderBy('priority_id'),
            'due_date' => $query->orderByRaw('due_date IS NULL, due_date ASC'),
            default => $query->orderBy('order'),
        };

        return $query->get()
            ->map(fn(Ticket $item) => [
                'id' => $item->id,
                'code' => $item->code,
                'title' => $item->name,
                'owner' => $item->owner,
                'type' => $item->type,
                'responsible' => $item->responsible,
                'project' => $item->project,
                'status' => $item->status->id,
                'priority' => $item->priority,
                'epic' => $item->epic,
                'relations' => $item->relations,
                'totalLoggedHours' => $item->totalLoggedSeconds ? $item->totalLoggedHours : null,
                'due_date' => $item->due_date,
            ]);
    }

    public function recordUpdated(int $record, int $newIndex, int $newStatus): void
    {
        $ticket = Ticket::find($record);
        if (!$ticket) {
            Filament::notify('danger', __('Ticket not found'));
            return;
        }

        // Security: verify ticket belongs to current project
        if ($this->project && $ticket->project_id !== $this->project->id) {
            Filament::notify('danger', __('Unauthorized action'));
            return;
        }

        // Security: verify user has permission to update this ticket
        if (!auth()->user()->can('update', $ticket)) {
            Filament::notify('danger', __('You do not have permission to update this ticket'));
            return;
        }

        // Security: verify user can transition to this status (role-based)
        $targetStatus = \App\Models\TicketStatus::find($newStatus);
        if ($targetStatus && !$targetStatus->canBeSetByUser()) {
            Filament::notify('danger', __('You do not have permission to set tickets to ":status" status.', [
                'status' => $targetStatus->name
            ]));
            return;
        }

        $oldStatusId = (int) $ticket->status_id;
        $ticket->order = $newIndex;
        $ticket->status_id = $newStatus;
        $ticket->save();

        if ($this->project) {
            broadcast(new TicketMoved(
                projectId: (int) $this->project->id,
                ticketId: (int) $ticket->id,
                oldStatusId: $oldStatusId,
                newStatusId: (int) $newStatus,
                newIndex: (int) $newIndex,
                movedByUserId: (int) auth()->id(),
            ))->toOthers();
        }

        Filament::notify('success', __('Ticket updated'));
    }

    public function createTicketWithStatusDirect(int $statusId): void
    {
        try {
            // Get the status
            $status = TicketStatus::find($statusId);
            if (!$status) {
                Filament::notify('danger', __('Status not found'));
                return;
            }

            // Get default values
            $defaultType = TicketType::where('is_default', true)->first();
            $defaultPriority = TicketPriority::where('is_default', true)->first();

            // Auto set estimation dan due date berdasarkan default priority
            $estimationHours = $this->getEstimationByPriority($defaultPriority);
            $dueDate = $this->calculateDueDate($estimationHours);

            // Create new ticket with specific status
            $ticket = Ticket::create([
                'name' => 'New Ticket',
                'content' => 'Please update this ticket with proper details...',
                'project_id' => $this->project->id,
                'owner_id' => auth()->user()->id,
                'status_id' => $statusId,
                'type_id' => $defaultType?->id,
                'priority_id' => $defaultPriority?->id,
                'estimation' => $estimationHours,
                'due_date' => $dueDate,
            ]);

            // Show success notification
            Filament::notify('success', __('Ticket created in :status. Please update the details.', [
                'status' => $status->name
            ]));

            // Redirect to ticket edit page
            $this->redirect(route('filament.resources.tickets.edit', $ticket));

        } catch (\Exception $e) {
            // Show error notification
            Filament::notify('danger', __('Failed to create ticket: ') . $e->getMessage());
        }
    }

    private function getEstimationByPriority(?TicketPriority $priority): int
    {
        if (!$priority) return 3;

        // Mapping estimation hours berdasarkan priority name
        $estimationMapping = [
            'Low' => 2,
            'Normal' => 3,
            'High' => 5,
            'Critical' => 6,
            'Blocker' => 7,
        ];

        return $estimationMapping[$priority->name] ?? 3;
    }

    private function calculateDueDate(int $hours): string
    {
        // Asumsi 8 jam kerja per hari
        $workingDays = ceil($hours / 8);
        if ($workingDays < 1) $workingDays = 1; // minimal 1 hari

        $dueDate = now()->addWeekdays($workingDays);
        return $dueDate->format('Y-m-d');
    }

    public function isMultiProject(): bool
    {
        return $this->project === null;
    }

    public function filter(): void
    {
        $this->getRecords();
    }

    public function resetFilters(): void
    {
        $this->form->fill();
        $this->filter();
    }

    public function createTicket(): void
    {
        $this->selectedStatusId = null;
        $this->ticket = true;
    }

    public function createTicketWithStatus(int $statusId): void
    {
        $this->selectedStatusId = $statusId;
        $this->ticket = true;
    }

    public function closeTicketDialog(bool $refresh): void
    {
        $this->ticket = false;
        if ($refresh) {
            $this->filter();
        }
    }

    public function refreshBoard(): void
    {
        $this->filter();
        Filament::notify('success', __('Board refreshed'));
    }

    public function getProjectTitle(): string
    {
        return $this->project
            ? $this->project->name
            : __('No project selected');
    }

    protected function formatProjectDescription(string $description): string
    {
        // Convert HTML to plain text with newlines preserved
        $text = preg_replace('/<br\s*\/?>/i', "\n", $description);
        $text = preg_replace('/<\/(?:p|div|li|h[1-6])>/i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        $lines = preg_split('/\r?\n/', trim($text));
        $items = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = trim($lines[$i]);
            if ($line === '') {
                $i++;
                continue;
            }

            // Check if this line contains "label: URL" pattern
            if (preg_match('/^(.+?):\s*(https?:\/\/\S+)$/i', $line, $m)) {
                $items[] = ['label' => trim($m[1]), 'url' => trim($m[2])];
                $i++;
                continue;
            }

            // Check if next line is a URL (label on current line, URL on next)
            $nextLine = isset($lines[$i + 1]) ? trim($lines[$i + 1]) : '';
            if ($nextLine && preg_match('/^https?:\/\/\S+$/', $nextLine)) {
                $items[] = ['label' => $line, 'url' => $nextLine];
                $i += 2;
                continue;
            }

            // Standalone URL
            if (preg_match('/^https?:\/\/\S+$/', $line)) {
                $host = parse_url($line, PHP_URL_HOST) ?: $line;
                $items[] = ['label' => $host, 'url' => $line];
                $i++;
                continue;
            }

            // Plain text line - just skip or treat as label without URL
            $items[] = ['label' => $line, 'url' => null];
            $i++;
        }

        if (empty($items)) {
            return '<span style="font-size:0.875rem;color:#9ca3af;">Manage your project tickets with drag &amp; drop</span>';
        }

        $html = '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">';
        foreach ($items as $item) {
            if (!$item['url']) continue;

            $url = e($item['url']);
            $label = e($item['label']);

            $html .= '<a href="' . $url . '" target="_blank" rel="noopener"'
                . ' style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;'
                . 'font-size:12px;font-weight:500;border-radius:6px;'
                . 'background:rgba(55,65,81,0.5);color:#60a5fa;'
                . 'border:1px solid rgba(75,85,99,0.5);text-decoration:none;"'
                . ' onmouseover="this.style.background=\'rgba(55,65,81,0.8)\';this.style.color=\'#93bbfd\'"'
                . ' onmouseout="this.style.background=\'rgba(55,65,81,0.5)\';this.style.color=\'#60a5fa\'">'
                . $label . ' &#8599;'
                . '</a>';
        }
        $html .= '</div>';

        return $html;
    }

    protected function kanbanHeading(): string|Htmlable
    {
        $heading = '<div class="flex flex-col w-full gap-1">';
        $heading .= '<a href="' . route('filament.pages.board') . '"
                            class="text-xs font-medium text-primary-500 hover:underline">';
        $heading .= __('Back to board');
        $heading .= '</a>';
        $heading .= '<div class="flex flex-col gap-1">';
        $heading .= '<span class="text-2xl font-bold text-gray-900 inline-flex items-center flex-wrap gap-1">' . __('Kanban');
        if ($this->project) {
            $heading .= ' - ';
            $heading .= $this->projectSwitcherDropdown();
            $heading .= '</span>';
            $description = $this->project->description ?? '';
            if ($description) {
                $heading .= $this->formatProjectDescription($description);
            } else {
                $heading .= '<span class="text-sm text-gray-400">Manage your project tickets with drag &amp; drop</span>';
            }
        } else {
            $heading .= '</span><span class="text-xs text-gray-400">'
                . __('Only default statuses are listed when no projects selected')
                . '</span>';
        }
        $heading .= '</div>';
        $heading .= '</div>';

        // Add JavaScript for keyboard shortcut (since button now has ID from actions)
        $heading .= '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            // Keyboard shortcut Ctrl+T / Cmd+T
                            document.addEventListener("keydown", function(e) {
                                if ((e.ctrlKey || e.metaKey) && e.key === "t") {
                                    e.preventDefault();
                                    const btn = document.getElementById("createTicketBtn");
                                    if (btn) btn.click();
                                }
                            });
                        });
                    </script>';

        return new HtmlString($heading);
    }

    protected function scrumHeading(): string|Htmlable
    {
        $heading = '<div class="flex flex-col w-full gap-1">';
        $heading .= '<a href="' . route('filament.pages.board') . '"
                            class="text-xs font-medium text-primary-500 hover:underline">';
        $heading .= __('Back to board');
        $heading .= '</a>';
        $heading .= '<div class="flex flex-col gap-1">';
        $heading .= '<span class="text-2xl font-bold text-gray-900 inline-flex items-center flex-wrap gap-1">' . __('Scrum');
        if ($this->project) {
            $heading .= ' - ';
            $heading .= $this->projectSwitcherDropdown();
            $heading .= '</span>';
            $heading .= '<span class="text-sm text-gray-600">' . __('Manage your sprint tickets') . '</span>';
        } else {
            $heading .= '</span><span class="text-xs text-gray-400">'
                . __('Only default statuses are listed when no projects selected')
                . '</span>';
        }
        $heading .= '</div>';
        $heading .= '</div>';

        // Add JavaScript for keyboard shortcut
        $heading .= '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            // Keyboard shortcut Ctrl+T / Cmd+T
                            document.addEventListener("keydown", function(e) {
                                if ((e.ctrlKey || e.metaKey) && e.key === "t") {
                                    e.preventDefault();
                                    const btn = document.getElementById("createTicketBtn");
                                    if (btn) btn.click();
                                }
                            });
                        });
                    </script>';

        return new HtmlString($heading);
    }

    /**
     * Clickable dropdown next to the current project name that lets the user
     * jump straight to another project's board (kanban or scrum depending on
     * that project's type). Only shows projects the user owns or is a member
     * of.
     */
    protected function projectSwitcherDropdown(): string
    {
        $currentId = $this->project?->id;
        $userId = (int) auth()->id();

        // Projects the user owns OR is a member of. Exclude soft-deleted.
        $projects = Project::query()
            ->where(function ($q) use ($userId) {
                $q->where('owner_id', $userId)
                  ->orWhereHas('users', fn ($u) => $u->where('users.id', $userId));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'ticket_prefix']);

        $currentName = e($this->project->name ?? '');

        $items = '';
        foreach ($projects as $p) {
            $isCurrent = ((int) $p->id === (int) $currentId);
            $routeName = ($p->type === 'scrum') ? 'filament.pages.scrum/{project}' : 'filament.pages.kanban/{project}';
            $url = e(route($routeName, ['project' => $p]));
            $badge = '';
            if ($p->ticket_prefix) {
                $badge = '<span class="text-[10px] uppercase tracking-wide px-1.5 py-0.5 rounded bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300 mr-2">'.e($p->ticket_prefix).'</span>';
            }
            $typeDot = $p->type === 'scrum'
                ? '<span title="Scrum" class="inline-block w-1.5 h-1.5 rounded-full bg-purple-500 ml-auto"></span>'
                : '<span title="Kanban" class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500 ml-auto"></span>';
            $currentMark = $isCurrent ? '<span class="ml-2 text-[10px] text-primary-500 font-semibold">current</span>' : '';
            $items .= '<a href="'.$url.'" class="flex items-center gap-1 px-3 py-2 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 '.($isCurrent ? 'bg-gray-50 dark:bg-gray-700/50' : '').'">'
                . $badge
                . '<span class="truncate text-gray-900 dark:text-gray-100 font-normal">'.e($p->name).'</span>'
                . $currentMark
                . $typeDot
                . '</a>';
        }

        if ($items === '') {
            $items = '<div class="px-3 py-2 text-sm text-gray-400">No other projects available</div>';
        }

        return <<<HTML
        <span x-data="{ open: false }" class="relative inline-flex">
            <button type="button" x-on:click="open = ! open" x-on:click.outside="open = false"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition text-2xl font-bold text-gray-900 dark:text-gray-100">
                <span>{$currentName}</span>
                <svg class="w-5 h-5 text-gray-400" x-bind:class="open ? 'rotate-180' : ''" style="transition: transform 0.15s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak
                 x-transition.opacity.duration.150ms
                 class="absolute left-0 top-full mt-1 z-30 w-72 max-h-80 overflow-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl p-1"
                 style="font-size: 14px;">
                <div class="px-3 py-2 text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-700 mb-1">Switch project</div>
                {$items}
            </div>
        </span>
        HTML;
    }

    protected function scrumSubHeading(): string|Htmlable|null
    {
        if ($this->project?->currentSprint) {
            return new HtmlString(
                '<div class="flex flex-col w-full gap-1">'
                . '<div class="flex items-center w-full gap-2">'
                . '<span class="px-2 py-1 text-sm text-white rounded bg-danger-500">'
                . $this->project->currentSprint->name
                . '</span>'
                . '<span class="text-xs text-gray-400">'
                . __('Started at:') . ' ' . $this->project->currentSprint->started_at->format(__('Y-m-d')) . ' - '
                . __('Ends at:') . ' ' . $this->project->currentSprint->ends_at->format(__('Y-m-d')) . ' - '
                . ($this->project->currentSprint->remaining ?
                    (
                        __('Remaining:') . ' ' . $this->project->currentSprint->remaining . ' ' . __('days'))
                    : ''
                )
                . '</span>'
                . '</div>'
                . ($this->project->nextSprint ? '<span class="text-xs font-medium text-primary-500">'
                    . __('Next sprint:') . ' ' . $this->project->nextSprint->name . ' - '
                    . __('Starts at:') . ' ' . $this->project->nextSprint->starts_at->format(__('Y-m-d'))
                    . ' (' . __('in') . ' ' . $this->project->nextSprint->starts_at->diffForHumans() . ')'
                    . '</span>'
                    . '</span>' : '')
                . '</div>'
            );
        } else {
            return null;
        }
    }
}
