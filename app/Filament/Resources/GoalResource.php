<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoalResource\Pages;
use App\Filament\Resources\GoalResource\RelationManagers;
use App\Models\Department;
use App\Models\Goal;
use App\Models\GoalPeriod;
use App\Models\User;
use App\Rules\ObjectiveWeightFits;
use App\Services\GoalWeightValidator;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class GoalResource extends Resource
{
    protected static ?string $model = Goal::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'goals';

    protected static function getNavigationLabel(): string
    {
        return __('Goals');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Performance');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Restricted while module is in development. Widen in a later sprint.
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('period_id')
                        ->label(__('Period'))
                        ->options(fn () => GoalPeriod::orderBy('start_date', 'desc')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->reactive(),

                    Forms\Components\Select::make('type')
                        ->label(__('Type'))
                        ->options([
                            'objective' => 'OKR Objective',
                            'kpi' => 'KPI',
                        ])
                        ->default('objective')
                        ->required(),
                ]),

                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('level')
                        ->label(__('Level'))
                        ->options([
                            'company' => 'Company',
                            'department' => 'Department',
                            'individual' => 'Individual',
                        ])
                        ->default('individual')
                        ->required()
                        ->reactive(),

                    Forms\Components\Select::make('department_id')
                        ->label(__('Department'))
                        ->options(fn () => Department::orderBy('sort_order')->pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn (callable $get) => in_array($get('level'), ['department', 'individual'])),

                    Forms\Components\Select::make('owner_id')
                        ->label(__('Owner'))
                        ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->reactive()
                        ->visible(fn (callable $get) => $get('level') === 'individual'),
                ]),

                Forms\Components\Select::make('parent_id')
                    ->label(__('Parent Goal (cascade)'))
                    ->placeholder(__('Optional — link to a parent Company/Department goal'))
                    ->options(function (callable $get, $record) {
                        $periodId = $get('period_id');
                        if (! $periodId) {
                            return [];
                        }

                        $q = Goal::query()
                            ->where('period_id', $periodId)
                            ->where('type', 'objective')
                            ->whereIn('level', ['company', 'department']);

                        if ($record) {
                            $q->where('id', '!=', $record->id);
                        }

                        return $q->orderBy('level')->orderBy('code')->get()
                            ->mapWithKeys(fn ($g) => [$g->id => "[{$g->level}] " . ($g->code ? $g->code . ' — ' : '') . $g->title])
                            ->toArray();
                    })
                    ->searchable(),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('code')
                        ->label(__('Code'))
                        ->placeholder('e.g., O1')
                        ->maxLength(20),

                    Forms\Components\Select::make('visibility')
                        ->label(__('Visibility'))
                        ->options([
                            'public' => 'Public (transparent)',
                            'private' => 'Private',
                        ])
                        ->default('public')
                        ->required(),
                ]),

                Forms\Components\TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(500)
                    ->columnSpan('full'),

                Forms\Components\Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(2)
                    ->columnSpan('full'),

                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('weight')
                        ->label(__('Weight (%)'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.01)
                        ->default(0)
                        ->required()
                        ->reactive()
                        ->rules(function (callable $get, $record) {
                            $ownerId = $get('owner_id');
                            $periodId = $get('period_id');
                            $level = $get('level');
                            $type = $get('type');

                            if ($type !== 'objective' || $level !== 'individual' || ! $ownerId || ! $periodId) {
                                return [];
                            }

                            return [new ObjectiveWeightFits(
                                (int) $ownerId,
                                (int) $periodId,
                                $record?->id,
                            )];
                        }),

                    Forms\Components\Select::make('status')
                        ->label(__('Status'))
                        ->options([
                            'draft' => 'Draft',
                            'active' => 'Active',
                            'achieved' => 'Achieved',
                            'missed' => 'Missed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('draft')
                        ->required(),

                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('Sort Order'))
                        ->numeric()
                        ->default(0),
                ]),

                Forms\Components\Placeholder::make('weight_budget')
                    ->label(__('Weight Budget'))
                    ->content(function (callable $get, $record) {
                        $ownerId = $get('owner_id');
                        $periodId = $get('period_id');
                        $level = $get('level');
                        $type = $get('type');
                        $current = (float) ($get('weight') ?: 0);

                        if ($type !== 'objective' || $level !== 'individual' || ! $ownerId || ! $periodId) {
                            return new HtmlString('<span style="color:#6b7280;">Budget tracker applies to Individual Objectives only.</span>');
                        }

                        $otherTotal = GoalWeightValidator::userObjectivesTotal(
                            (int) $ownerId,
                            (int) $periodId,
                            $record?->id,
                        );
                        $newTotal = $otherTotal + $current;
                        $ok = GoalWeightValidator::isValidTotal($newTotal);
                        $color = $ok ? '#059669' : ($newTotal > 100 ? '#dc2626' : '#d97706');

                        return new HtmlString(
                            '<div style="font-size:12.5px;line-height:1.5;color:' . $color . ';">'
                            . 'Existing: <strong>' . number_format($otherTotal, 2) . '%</strong> • '
                            . 'This: <strong>' . number_format($current, 2) . '%</strong> • '
                            . 'New total: <strong>' . number_format($newTotal, 2) . '% / 100%</strong>'
                            . ($ok ? ' ✓' : ($newTotal > 100 ? ' ✗ exceeds' : ' — under-allocated'))
                            . '</div>'
                        );
                    })
                    ->columnSpan('full'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('Code'))
                    ->formatStateUsing(fn ($state) => $state ?: '—'),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('Goal'))
                    ->searchable()
                    ->wrap()
                    ->formatStateUsing(function ($record) {
                        $badgeColor = match ($record->level) {
                            'company' => 'bg-purple-500/10 text-purple-500',
                            'department' => 'bg-blue-500/10 text-blue-500',
                            default => 'bg-gray-500/10 text-gray-500',
                        };
                        $typeLabel = $record->type === 'kpi' ? 'KPI' : 'OKR';

                        return new HtmlString(
                            '<div class="flex items-start gap-2">'
                            . '<span class="px-2 py-0.5 text-xs font-medium rounded ' . $badgeColor . ' whitespace-nowrap">' . e(ucfirst($record->level)) . '</span>'
                            . '<span class="px-2 py-0.5 text-xs font-medium rounded bg-emerald-500/10 text-emerald-500 whitespace-nowrap">' . $typeLabel . '</span>'
                            . '<div class="text-sm text-gray-900 dark:text-gray-100">' . e($record->title) . '</div>'
                            . '</div>'
                        );
                    }),

                Tables\Columns\TextColumn::make('period.name')
                    ->label(__('Period'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label(__('Owner'))
                    ->formatStateUsing(fn ($state) => $state ?: '—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label(__('Weight'))
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('achievement')
                    ->label(__('Progress'))
                    ->getStateUsing(fn ($record) => $record->achievement)
                    ->formatStateUsing(function ($state) {
                        $pct = (float) $state;
                        $color = $pct >= 70 ? '#059669' : ($pct >= 40 ? '#d97706' : '#dc2626');
                        return new HtmlString(
                            '<div style="display:flex;align-items:center;gap:6px;">'
                            . '<div style="width:80px;height:6px;background:#e5e7eb;border-radius:3px;overflow:hidden;">'
                            . '<div style="width:' . $pct . '%;height:100%;background:' . $color . ';"></div>'
                            . '</div>'
                            . '<span style="font-size:12px;color:' . $color . ';font-weight:600;">' . number_format($pct, 1) . '%</span>'
                            . '</div>'
                        );
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($state) => new HtmlString(match ($state) {
                        'active' => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-green-500/10 text-green-500">Active</span>',
                        'achieved' => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-blue-500/10 text-blue-500">Achieved</span>',
                        'missed' => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-red-500/10 text-red-500">Missed</span>',
                        'cancelled' => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-gray-500/10 text-gray-500">Cancelled</span>',
                        default => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-yellow-500/10 text-yellow-600">Draft</span>',
                    })),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period_id')
                    ->label(__('Period'))
                    ->options(fn () => GoalPeriod::orderBy('start_date', 'desc')->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('level')
                    ->label(__('Level'))
                    ->options([
                        'company' => 'Company',
                        'department' => 'Department',
                        'individual' => 'Individual',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'objective' => 'Objective',
                        'kpi' => 'KPI',
                    ]),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\KeyResultsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoals::route('/'),
            'create' => Pages\CreateGoal::route('/create'),
            'edit' => Pages\EditGoal::route('/{record}/edit'),
        ];
    }
}
