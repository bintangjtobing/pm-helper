<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketRelation;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 2;

    protected static function getNavigationLabel(): string
    {
        return __('Tickets');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('project_id')
                                    ->label(__('Project'))
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($get, $set) {
                                        $project = Project::where('id', $get('project_id'))->first();
                                        if ($project?->status_type === 'custom') {
                                            $set(
                                                'status_id',
                                                TicketStatus::where('project_id', $project->id)
                                                    ->where('is_default', true)
                                                    ->first()
                                                    ?->id
                                            );
                                        } else {
                                            $set(
                                                'status_id',
                                                TicketStatus::whereNull('project_id')
                                                    ->where('is_default', true)
                                                    ->first()
                                                    ?->id
                                            );
                                        }
                                    })
                                    ->options(fn() => Project::where('owner_id', auth()->user()->id)
                                        ->orWhereHas('users', function ($query) {
                                            return $query->where('users.id', auth()->user()->id);
                                        })->pluck('name', 'id')->toArray()
                                    )
                                    ->default(fn() => request()->get('project'))
                                    ->required(),
                                Forms\Components\Select::make('epic_id')
                                    ->label(__('Epic'))
                                    ->searchable()
                                    ->reactive()
                                    ->options(function ($get, $set) {
                                        return Epic::where('project_id', $get('project_id'))->pluck('name', 'id')->toArray();
                                    }),
                                Forms\Components\Grid::make()
                                    ->columns(12)
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('code')
                                            ->label(__('Ticket code'))
                                            ->visible(fn($livewire) => !($livewire instanceof CreateRecord))
                                            ->columnSpan(2)
                                            ->disabled(),

                                        Forms\Components\TextInput::make('name')
                                            ->label(__('Ticket name'))
                                            ->required()
                                            ->columnSpan(
                                                fn($livewire) => !($livewire instanceof CreateRecord) ? 10 : 12
                                            )
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\Select::make('owner_id')
                                    ->label(__('Ticket owner'))
                                    ->searchable()
                                    ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                                    ->default(fn() => auth()->user()->id)
                                    ->required(),

                                Forms\Components\Select::make('responsible_id')
                                    ->label(__('Ticket responsible'))
                                    ->searchable()
                                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\Select::make('status_id')
                                            ->label(__('Ticket status'))
                                            ->searchable()
                                            ->options(function ($get) {
                                                $project = Project::where('id', $get('project_id'))->first();
                                                if ($project?->status_type === 'custom') {
                                                    return TicketStatus::where('project_id', $project->id)
                                                        ->get()
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                } else {
                                                    return TicketStatus::whereNull('project_id')
                                                        ->get()
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                }
                                            })
                                            ->default(function ($get) {
                                                $project = Project::where('id', $get('project_id'))->first();
                                                if ($project?->status_type === 'custom') {
                                                    return TicketStatus::where('project_id', $project->id)
                                                        ->where('is_default', true)
                                                        ->first()
                                                        ?->id;
                                                } else {
                                                    return TicketStatus::whereNull('project_id')
                                                        ->where('is_default', true)
                                                        ->first()
                                                        ?->id;
                                                }
                                            })
                                            ->required(),

                                        Forms\Components\Select::make('type_id')
                                            ->label(__('Ticket type'))
                                            ->searchable()
                                            ->options(fn() => TicketType::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => TicketType::where('is_default', true)->first()?->id)
                                            ->required(),

                                        Forms\Components\Select::make('priority_id')
                                            ->label(__('Ticket priority'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                // Auto set estimation dan due date berdasarkan priority
                                                $priority = TicketPriority::find($state);
                                                if ($priority) {
                                                    // Mapping estimation hours berdasarkan priority name
                                                    $estimationMapping = [
                                                        'Low' => 2,
                                                        'Normal' => 3,
                                                        'High' => 5,
                                                        'Critical' => 6,
                                                        'Blocker' => 7,
                                                    ];

                                                    $estimationHours = $estimationMapping[$priority->name] ?? 3;
                                                    $set('estimation', $estimationHours);

                                                    // Auto set due date berdasarkan estimation
                                                    // Asumsi 8 jam kerja per hari
                                                    $workingDays = ceil($estimationHours / 8);
                                                    if ($workingDays < 1) $workingDays = 1; // minimal 1 hari

                                                    $dueDate = now()->addWeekdays($workingDays);
                                                    $set('due_date', $dueDate->format('Y-m-d'));
                                                }
                                            })
                                            ->options(fn() => TicketPriority::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => TicketPriority::where('is_default', true)->first()?->id)
                                            ->required(),
                                        Forms\Components\Select::make('cc_users')
                                            ->label(__('CC Users'))
                                            ->multiple()
                                            ->searchable()
                                            ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                                            ->helperText(__('Select users to be CC\'d on this ticket'))
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Forms\Components\RichEditor::make('content')
                            ->label(__('Ticket content'))
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\Grid::make()
                            ->columnSpan(2)
                            ->columns(12)
                            ->schema([
                                Forms\Components\TextInput::make('estimation')
                                    ->label(__('Estimation time (hours)'))
                                    ->numeric()
                                    ->suffix('hours')
                                    ->helperText(__('Estimated time to complete this ticket in hours'))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Auto update due date ketika estimation berubah
                                        if ($state && is_numeric($state)) {
                                            $hours = (float) $state;
                                            // Asumsi 8 jam kerja per hari
                                            $workingDays = ceil($hours / 8);
                                            if ($workingDays < 1) $workingDays = 1; // minimal 1 hari

                                            $dueDate = now()->addWeekdays($workingDays);
                                            $set('due_date', $dueDate->format('Y-m-d'));
                                        }
                                    })
                                    ->columnSpan(4),

                                Forms\Components\DatePicker::make('due_date')
                                    ->label(__('Due Date'))
                                    ->helperText(__('Automatically calculated based on estimation time'))
                                    ->columnSpan(4),
                            ]),

                        Forms\Components\Repeater::make('relations')
                            ->itemLabel(function (array $state) {
                                $ticketRelation = TicketRelation::find($state['id'] ?? 0);
                                if ($ticketRelation) {
                                    return __(config('system.tickets.relations.list.' . $ticketRelation->type))
                                        . ' '
                                        . $ticketRelation->relation->name
                                        . ' (' . $ticketRelation->relation->code . ')';
                                }
                                return null;
                            })
                            ->relationship()
                            ->collapsible()
                            ->collapsed()
                            ->orderable()
                            ->defaultItems(0)
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label(__('Relation type'))
                                            ->required()
                                            ->searchable()
                                            ->options(config('system.tickets.relations.list'))
                                            ->default(fn() => config('system.tickets.relations.default')),

                                        Forms\Components\Select::make('relation_id')
                                            ->label(__('Related ticket'))
                                            ->required()
                                            ->searchable()
                                            ->columnSpan(2)
                                            ->options(function ($livewire) {
                                                $query = Ticket::query();
                                                if ($livewire instanceof EditRecord && $livewire->record) {
                                                    $query->where('id', '<>', $livewire->record->id);
                                                }
                                                return $query->get()->pluck('name', 'id')->toArray();
                                            }),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    // Sisanya tetap sama seperti kode asli...
    public static function tableColumns(bool $withProject = true): array
    {
        $columns = [];
        if ($withProject) {
            $columns[] = Tables\Columns\TextColumn::make('project.name')
                ->label(__('Project'))
                ->sortable()
                ->searchable();
        }
        $columns = array_merge($columns, [
            Tables\Columns\TextColumn::make('name')
                ->label(__('Ticket name'))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('owner.name')
                ->label(__('Owner'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->owner]))
                ->searchable(),

            Tables\Columns\TextColumn::make('responsible.name')
                ->label(__('Responsible'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->responsible]))
                ->searchable(),

            Tables\Columns\TextColumn::make('status.name')
                ->label(__('Status'))
                ->formatStateUsing(fn($record) => new HtmlString('
                            <div class="flex items-center gap-2 mt-1">
                                <span class="relative flex w-6 h-6 rounded-md filament-tables-color-column"
                                    style="background-color: ' . $record->status->color . '"></span>
                                <span>' . $record->status->name . '</span>
                            </div>
                        '))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('type.name')
                ->label(__('Type'))
                ->formatStateUsing(
                    fn($record) => view('partials.filament.resources.ticket-type', ['state' => $record->type])
                )
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('priority.name')
                ->label(__('Priority'))
                ->formatStateUsing(fn($record) => new HtmlString('
                            <div class="flex items-center gap-2 mt-1">
                                <span class="relative flex w-6 h-6 rounded-md filament-tables-color-column"
                                    style="background-color: ' . $record->priority->color . '"></span>
                                <span>' . $record->priority->name . '</span>
                            </div>
                        '))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('estimation')
                ->label(__('Estimation'))
                ->formatStateUsing(fn($state) => $state ? $state . ' hours' : '-')
                ->sortable(),

            Tables\Columns\TextColumn::make('due_date')
                ->label(__('Due Date'))
                ->date()
                ->sortable()
                ->color(fn ($record) => $record->due_date && $record->due_date->isPast() ? 'danger' : null),

            Tables\Columns\TextColumn::make('created_at')
                ->label(__('Created at'))
                ->dateTime()
                ->sortable()
                ->searchable(),
        ]);
        return $columns;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label(__('Project'))
                    ->multiple()
                    ->options(fn() => Project::where('owner_id', auth()->user()->id)
                        ->orWhereHas('users', function ($query) {
                            return $query->where('users.id', auth()->user()->id);
                        })->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('owner_id')
                    ->label(__('Owner'))
                    ->multiple()
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('responsible_id')
                    ->label(__('Responsible'))
                    ->multiple()
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->multiple()
                    ->options(fn() => TicketStatus::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('type_id')
                    ->label(__('Type'))
                    ->multiple()
                    ->options(fn() => TicketType::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('priority_id')
                    ->label(__('Priority'))
                    ->multiple()
                    ->options(fn() => TicketPriority::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now()))
                    ->label(__('Overdue')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
