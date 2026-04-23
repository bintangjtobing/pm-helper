<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketStatus;
use Filament\Forms;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportJson')
                ->label(__('Download JSON'))
                ->icon('heroicon-o-download')
                ->color('secondary')
                ->modalHeading(__('Export tickets as JSON'))
                ->modalSubmitActionLabel(__('Download'))
                ->form([
                    Forms\Components\Select::make('status_ids')
                        ->label(__('Statuses'))
                        ->multiple()
                        ->options(fn() => TicketStatus::orderBy('order')->pluck('name', 'id')->toArray())
                        ->required()
                        ->helperText(__('Only tickets in the selected statuses will be exported.')),
                    Forms\Components\Toggle::make('include_comments')
                        ->label(__('Include comments'))
                        ->default(true),
                ])
                ->action(function (array $data) {
                    $query = $this->getTableQuery()
                        ->whereIn('status_id', $data['status_ids'])
                        ->with([
                            'owner:id,name,email',
                            'responsible:id,name,email',
                            'status:id,name,color',
                            'priority:id,name,color',
                            'type:id,name',
                            'project:id,name,code',
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
                                'project' => $t->project ? [
                                    'id' => $t->project->id,
                                    'name' => $t->project->name,
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

                    $filename = 'tickets-export-' . now()->format('Ymd-His') . '.json';
                    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    return response()->streamDownload(
                        fn () => print($json),
                        $filename,
                        ['Content-Type' => 'application/json'],
                    );
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where(function ($query) {
                return $query->where('owner_id', auth()->user()->id)
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhereHas('project', function ($query) {
                        return $query->where('owner_id', auth()->user()->id)
                            ->orWhereHas('users', function ($query) {
                                return $query->where('users.id', auth()->user()->id);
                            });
                    });
            });
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $search = $this->getTableSearchQuery();

        if (blank($search)) {
            return $query;
        }

        $terms = array_values(array_filter(
            preg_split('/[\s,;]+/', trim($search)) ?: [],
            fn($t) => $t !== ''
        ));

        if (count($terms) > 1) {
            return $query->where(function ($q) use ($terms) {
                foreach ($terms as $t) {
                    $q->orWhere('code', 'like', "%{$t}%");
                }
            });
        }

        // First apply default column search
        $query = parent::applySearchToTableQuery($query);

        // Then extend with content, code, and comments search
        return $query->orWhere(function (Builder $q) use ($search) {
            $q->where(function ($sub) {
                // Re-apply access control inside orWhere
                return $sub->where('owner_id', auth()->user()->id)
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhereHas('project', function ($pq) {
                        return $pq->where('owner_id', auth()->user()->id)
                            ->orWhereHas('users', function ($uq) {
                                return $uq->where('users.id', auth()->user()->id);
                            });
                    });
            })->where(function ($sub) use ($search) {
                $sub->where('content', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('comments', function ($cq) use ($search) {
                        $cq->where('content', 'like', "%{$search}%");
                    });
            });
        });
    }
}
