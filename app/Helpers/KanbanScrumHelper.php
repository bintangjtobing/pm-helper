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
use Filament\Pages\Actions\Action;
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

    protected function exportJsonAction(): Action
    {
        return Action::make('exportJson')
            ->label(__('Download JSON'))
            ->icon('heroicon-o-download')
            ->color('secondary')
            ->button()
            ->modalHeading(__('Export tickets as JSON'))
            ->modalButton(__('Download'))
            ->form([
                Select::make('status_ids')
                    ->label(__('Statuses'))
                    ->multiple()
                    ->options(fn () => TicketStatus::query()
                        ->when(
                            $this->project && $this->project->status_type === 'custom',
                            fn ($q) => $q->where('project_id', $this->project->id),
                            fn ($q) => $q->whereNull('project_id'),
                        )
                        ->orderBy('order')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->required()
                    ->helperText(__('Only tickets in the selected statuses will be exported.')),
                Toggle::make('include_comments')
                    ->label(__('Include comments'))
                    ->default(true),
            ])
            ->action(fn (array $data) => $this->streamTicketsJson($data));
    }

    protected function streamTicketsJson(array $data)
    {
        $query = Ticket::query()
            ->where('project_id', $this->project->id)
            ->whereIn('status_id', $data['status_ids'])
            ->with([
                'owner:id,name,email',
                'responsible:id,name,email',
                'status:id,name,color',
                'priority:id,name,color',
                'type:id,name',
                'project:id,name,ticket_prefix',
                'epic:id,name',
                'sprint:id,name',
            ]);

        if (! empty($data['include_comments'])) {
            $query->with(['comments' => function ($q) {
                $q->with('user:id,name,email')->orderBy('created_at');
            }]);
        }

        $tickets = $query->orderBy('code')->get();

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'exported_by' => [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
            'project' => [
                'id' => $this->project->id,
                'name' => $this->project->name,
                'ticket_prefix' => $this->project->ticket_prefix ?? null,
            ],
            'filters' => [
                'status_ids' => array_map('intval', $data['status_ids']),
                'include_comments' => (bool) ($data['include_comments'] ?? false),
            ],
            'count' => $tickets->count(),
            'tickets' => $tickets->map(function ($t) use ($data) {
                $row = [
                    'id' => $t->id,
                    'code' => $t->code,
                    'name' => $t->name,
                    'content' => $t->content,
                    'status' => $t->status ? [
                        'id' => $t->status->id,
                        'name' => $t->status->name,
                        'color' => $t->status->color,
                    ] : null,
                    'priority' => $t->priority ? [
                        'id' => $t->priority->id,
                        'name' => $t->priority->name,
                        'color' => $t->priority->color,
                    ] : null,
                    'type' => $t->type ? [
                        'id' => $t->type->id,
                        'name' => $t->type->name,
                    ] : null,
                    'epic' => $t->epic ? [
                        'id' => $t->epic->id,
                        'name' => $t->epic->name,
                    ] : null,
                    'sprint' => $t->sprint ? [
                        'id' => $t->sprint->id,
                        'name' => $t->sprint->name,
                    ] : null,
                    'owner' => $t->owner ? [
                        'id' => $t->owner->id,
                        'name' => $t->owner->name,
                        'email' => $t->owner->email,
                    ] : null,
                    'responsible' => $t->responsible ? [
                        'id' => $t->responsible->id,
                        'name' => $t->responsible->name,
                        'email' => $t->responsible->email,
                    ] : null,
                    'due_date' => optional($t->due_date)->toIso8601String(),
                    'created_at' => optional($t->created_at)->toIso8601String(),
                    'updated_at' => optional($t->updated_at)->toIso8601String(),
                ];

                if (! empty($data['include_comments'])) {
                    $row['comments'] = $t->comments->map(fn ($c) => [
                        'id' => $c->id,
                        'author' => $c->user ? [
                            'id' => $c->user->id,
                            'name' => $c->user->name,
                            'email' => $c->user->email,
                        ] : null,
                        'content' => $c->content,
                        'created_at' => optional($c->created_at)->toIso8601String(),
                    ])->values();
                }

                return $row;
            })->values(),
        ];

        $filename = 'project-' . ($this->project->ticket_prefix ?: $this->project->id) . '-export-' . now()->format('Ymd-His') . '.json';
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(
            fn () => print($json),
            $filename,
            ['Content-Type' => 'application/json'],
        );
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

        $arrow = '<svg class="pm-linkpill__arrow" width="9" height="9" viewBox="0 0 10 10" fill="none" aria-hidden="true">'
            . '<path d="M2.5 7.5L7.5 2.5M7.5 2.5H3.5M7.5 2.5V6.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>'
            . '</svg>';

        $html = $this->linkPillStyles();
        $html .= '<div class="pm-linkrail">';
        foreach ($items as $item) {
            if (!$item['url']) continue;

            $url = e($item['url']);
            $label = e($item['label']);
            $kind = $this->detectLinkKind($item['url']);
            $host = e(parse_url($item['url'], PHP_URL_HOST) ?: '');

            $html .= '<a href="' . $url . '" target="_blank" rel="noopener noreferrer"'
                . ' class="pm-linkpill pm-linkpill--' . $kind . '"'
                . ' data-host="' . $host . '">'
                . '<span class="pm-linkpill__dot" aria-hidden="true"></span>'
                . '<span class="pm-linkpill__label">' . $label . '</span>'
                . ($host !== '' ? '<span class="pm-linkpill__host">' . $host . '</span>' : '')
                . $arrow
                . '</a>';
        }
        $html .= '</div>';

        return $html;
    }

    protected function detectLinkKind(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');
        $full = $host . $path;

        if (preg_match('/(?:^|\.)(?:staging|stage|dev|uat|preview|test)\./', $host)
            || str_contains($full, '/staging') || str_contains($host, 'localhost')) {
            return 'staging';
        }
        if (str_contains($host, 'figma.com') || str_contains($host, 'penpot')
            || str_contains($host, 'framer.') || str_contains($full, 'design')) {
            return 'design';
        }
        if (str_contains($host, 'github.') || str_contains($host, 'gitlab.')
            || str_contains($host, 'bitbucket.')) {
            return 'repo';
        }
        if (str_starts_with($host, 'api.') || str_contains($full, '/api')
            || str_contains($full, 'swagger') || str_contains($host, 'postman')) {
            return 'api';
        }
        if (str_contains($host, 'notion.') || str_contains($host, 'confluence.')
            || str_contains($host, 'readme.') || str_contains($full, 'docs')
            || str_contains($full, 'documentation')) {
            return 'docs';
        }
        if (str_contains($host, 'slack.') || str_contains($host, 'discord.')
            || str_contains($host, 't.me') || str_contains($host, 'telegram')) {
            return 'chat';
        }

        return 'default';
    }

    protected function linkPillStyles(): string
    {
        return '<style>
        .pm-linkrail{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;align-items:center}
        .pm-linkpill{
            --accent:148,163,184;
            position:relative;display:inline-flex;align-items:center;gap:7px;
            padding:5px 11px 5px 9px;font-size:12px;font-weight:500;line-height:1;
            letter-spacing:-0.005em;color:#cbd5e1;text-decoration:none;
            background:linear-gradient(180deg,rgba(255,255,255,0.045) 0%,rgba(255,255,255,0.012) 45%,rgba(255,255,255,0) 100%),rgba(15,23,42,0.55);
            border:1px solid rgba(148,163,184,0.14);border-radius:8px;
            box-shadow:inset 0 1px 0 rgba(255,255,255,0.05),0 1px 0 rgba(0,0,0,0.28);
            transition:transform 180ms cubic-bezier(0.4,0,0.2,1),border-color 180ms ease,background 180ms ease,box-shadow 220ms ease,color 180ms ease;
            white-space:nowrap;
        }
        .pm-linkpill--docs    {--accent:96,165,250}
        .pm-linkpill--api     {--accent:52,211,153}
        .pm-linkpill--staging {--accent:245,158,11}
        .pm-linkpill--design  {--accent:236,72,153}
        .pm-linkpill--repo    {--accent:167,139,250}
        .pm-linkpill--chat    {--accent:168,85,247}
        .pm-linkpill__dot{
            width:6px;height:6px;border-radius:999px;flex-shrink:0;
            background:rgb(var(--accent));
            box-shadow:0 0 0 0 rgba(var(--accent),0);
            transition:box-shadow 260ms ease,transform 260ms cubic-bezier(0.34,1.56,0.64,1);
        }
        .pm-linkpill__label{position:relative;z-index:1}
        .pm-linkpill__host{
            color:rgba(148,163,184,0.55);
            font-family:ui-monospace,"SF Mono",Menlo,Consolas,monospace;
            font-size:10.5px;font-weight:500;letter-spacing:-0.02em;
            max-width:0;opacity:0;overflow:hidden;
            margin-left:-7px;
            transition:max-width 280ms ease,opacity 200ms ease,margin 280ms ease;
        }
        .pm-linkpill__arrow{
            color:rgba(148,163,184,0.7);flex-shrink:0;
            transition:transform 280ms cubic-bezier(0.34,1.56,0.64,1),color 180ms ease;
        }
        .pm-linkpill:hover{
            color:#f1f5f9;
            border-color:rgba(var(--accent),0.5);
            background:linear-gradient(180deg,rgba(255,255,255,0.065) 0%,rgba(255,255,255,0.02) 50%,rgba(255,255,255,0) 100%),rgba(15,23,42,0.75);
            box-shadow:inset 0 1px 0 rgba(255,255,255,0.08),0 8px 20px -12px rgba(var(--accent),0.45),0 0 0 1px rgba(var(--accent),0.22);
            transform:translateY(-1px);
        }
        .pm-linkpill:hover .pm-linkpill__dot{
            transform:scale(1.15);
            box-shadow:0 0 0 3px rgba(var(--accent),0.22);
        }
        .pm-linkpill:hover .pm-linkpill__host{max-width:220px;opacity:1;margin-left:0}
        .pm-linkpill:hover .pm-linkpill__arrow{
            transform:translate(2px,-2px);
            color:rgb(var(--accent));
        }
        .pm-linkpill:focus-visible{
            outline:none;
            box-shadow:inset 0 1px 0 rgba(255,255,255,0.08),0 0 0 2px rgba(15,23,42,1),0 0 0 4px rgba(var(--accent),0.75);
        }
        .pm-linkpill:active{transform:translateY(0)}
        html:not(.dark) .pm-linkpill{
            color:#475569;
            background:linear-gradient(180deg,rgba(15,23,42,0.02) 0%,rgba(15,23,42,0) 60%),#ffffff;
            border:1px solid rgba(15,23,42,0.09);
            box-shadow:inset 0 1px 0 rgba(255,255,255,0.8),0 1px 0 rgba(15,23,42,0.03);
        }
        html:not(.dark) .pm-linkpill__host{color:rgba(71,85,105,0.55)}
        html:not(.dark) .pm-linkpill__arrow{color:rgba(71,85,105,0.7)}
        html:not(.dark) .pm-linkpill:hover{
            color:#0f172a;
            background:linear-gradient(180deg,rgba(15,23,42,0.04) 0%,rgba(15,23,42,0) 60%),#ffffff;
            border-color:rgba(var(--accent),0.55);
            box-shadow:inset 0 1px 0 rgba(255,255,255,0.9),0 8px 20px -12px rgba(var(--accent),0.35),0 0 0 1px rgba(var(--accent),0.18);
        }
        @media (prefers-reduced-motion: reduce){
            .pm-linkpill,.pm-linkpill__dot,.pm-linkpill__arrow,.pm-linkpill__host{transition:none!important}
            .pm-linkpill:hover{transform:none}
            .pm-linkpill:hover .pm-linkpill__dot{transform:none}
            .pm-linkpill:hover .pm-linkpill__arrow{transform:none}
        }
        </style>';
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
