<?php

namespace App\Helpers;

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
        $query->with(['project', 'owner', 'responsible', 'status', 'type', 'priority', 'epic']);
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

        $ticket->order = $newIndex;
        $ticket->status_id = $newStatus;
        $ticket->save();
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
            return '<span class="text-sm text-gray-400">Manage your project tickets with drag &amp; drop</span>';
        }

        // Icon SVGs
        $icons = [
            'link' => '<svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>',
            'doc' => '<svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
            'globe' => '<svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>',
            'code' => '<svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>',
        ];

        $html = '<div class="flex flex-wrap gap-2 mt-1">';
        foreach ($items as $item) {
            if (!$item['url']) continue;

            // Pick icon based on URL/label
            $url = $item['url'];
            $label = e($item['label']);
            if (stripos($url, 'docs.google.com') !== false) {
                $icon = $icons['doc'];
            } elseif (stripos($url, '/docs/api') !== false || stripos($label, 'api') !== false) {
                $icon = $icons['code'];
            } elseif (stripos($label, 'staging') !== false || stripos($label, 'frontend') !== false || stripos($label, 'dashboard') !== false) {
                $icon = $icons['globe'];
            } else {
                $icon = $icons['link'];
            }

            $html .= '<a href="' . e($url) . '" target="_blank" rel="noopener"'
                . ' class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md'
                . ' bg-gray-700/50 text-primary-400 hover:text-primary-300 hover:bg-gray-700'
                . ' border border-gray-600/50 transition-colors duration-150">'
                . $icon . $label
                . '<svg class="w-2.5 h-2.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>'
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
        $heading .= '<span class="text-2xl font-bold text-gray-900">' . __('Kanban');
        if ($this->project) {
            $heading .= ' - ' . e($this->project->name) . '</span>';
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
        $heading .= '<span class="text-2xl font-bold text-gray-900">' . __('Scrum');
        if ($this->project) {
            $heading .= ' - ' . $this->project->name . '</span>';
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
