<?php

namespace App\Filament\Resources\GoalResource\RelationManagers;

use App\Models\Project;
use App\Models\TicketStatus;
use App\Services\GoalWeightValidator;
use App\Services\Goals\GoalProgressCalculator;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class KeyResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'keyResults';

    protected static ?string $title = 'Key Results';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Code')
                    ->placeholder('e.g., KR1')
                    ->maxLength(20),

                Forms\Components\TextInput::make('weight')
                    ->label('Weight (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->default(0)
                    ->required()
                    ->reactive(),
            ]),

            Forms\Components\TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(500)
                ->columnSpan('full'),

            Forms\Components\Textarea::make('how_to_measure')
                ->label('How to Measure')
                ->rows(2)
                ->columnSpan('full'),

            Forms\Components\Grid::make(4)->schema([
                Forms\Components\TextInput::make('target_value')
                    ->label('Target')
                    ->numeric()
                    ->step(0.01),

                Forms\Components\TextInput::make('current_value')
                    ->label('Current')
                    ->numeric()
                    ->step(0.01)
                    ->default(0),

                Forms\Components\TextInput::make('unit')
                    ->label('Unit')
                    ->placeholder('%, count, IDR, USD…')
                    ->maxLength(50),

                Forms\Components\Select::make('direction')
                    ->label('Direction')
                    ->options([
                        'increase' => 'Increase',
                        'decrease' => 'Decrease',
                        'maintain' => 'Maintain',
                    ])
                    ->default('increase')
                    ->required(),
            ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('progress_mode')
                    ->label('Progress Mode')
                    ->options([
                        'manual' => 'Manual',
                        'auto' => 'Auto (system-calculated)',
                        'hybrid' => 'Hybrid',
                    ])
                    ->default('manual')
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('auto_source')
                    ->label('Auto Source')
                    ->options([
                        'tickets' => 'Tickets',
                        'daily_reports' => 'Daily Reports',
                        'weekly_reports' => 'Weekly Reports',
                        'custom' => 'Custom',
                    ])
                    ->reactive()
                    ->visible(fn (callable $get) => in_array($get('progress_mode'), ['auto', 'hybrid'])),
            ]),

            // Tickets formula builder
            Forms\Components\Fieldset::make('Tickets Filter')
                ->visible(fn (callable $get) => in_array($get('progress_mode'), ['auto', 'hybrid']) && $get('auto_source') === 'tickets')
                ->schema([
                    Forms\Components\Select::make('auto_formula.filter.assignee')
                        ->label('Assignee')
                        ->options([
                            'owner' => 'Goal Owner (responsible_id)',
                            'any' => 'Any',
                        ])
                        ->default('owner'),

                    Forms\Components\MultiSelect::make('auto_formula.filter.status_ids')
                        ->label('Ticket Statuses (count these)')
                        ->options(fn () => TicketStatus::orderBy('order')->pluck('name', 'id'))
                        ->placeholder('Leave empty to count all statuses'),

                    Forms\Components\Select::make('auto_formula.filter.project_id')
                        ->label('Project (optional)')
                        ->options(fn () => Project::orderBy('name')->pluck('name', 'id'))
                        ->placeholder('All projects')
                        ->searchable(),

                    Forms\Components\Select::make('auto_formula.aggregate')
                        ->label('Aggregate')
                        ->options(['count' => 'Count'])
                        ->default('count')
                        ->required(),
                ])
                ->columns(2),

            // Daily / Weekly Reports formula builder
            Forms\Components\Fieldset::make('Reports Filter')
                ->visible(fn (callable $get) => in_array($get('progress_mode'), ['auto', 'hybrid']) && in_array($get('auto_source'), ['daily_reports', 'weekly_reports']))
                ->schema([
                    Forms\Components\Select::make('auto_formula.filter.user')
                        ->label('User')
                        ->options([
                            'owner' => 'Goal Owner',
                            'any' => 'Any',
                        ])
                        ->default('owner'),

                    Forms\Components\Select::make('auto_formula.filter.status')
                        ->label('Status')
                        ->options([
                            'submitted' => 'Submitted',
                            'acknowledged' => 'Acknowledged',
                            'any' => 'Any status',
                        ])
                        ->default('submitted'),

                    Forms\Components\Select::make('auto_formula.aggregate')
                        ->label('Aggregate')
                        ->options(['count' => 'Count'])
                        ->default('count')
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\TextInput::make('alignment_note')
                ->label('Alignment Note')
                ->placeholder('e.g., Amber O2-KR1')
                ->maxLength(255)
                ->columnSpan('full'),

            Forms\Components\TextInput::make('sort_order')
                ->label('Sort Order')
                ->numeric()
                ->default(0),

            Forms\Components\Placeholder::make('kr_weight_budget')
                ->label('KR Weight Budget')
                ->content(function ($livewire, $record, callable $get) {
                    $goalId = $livewire->ownerRecord->id;
                    $current = (float) ($get('weight') ?: 0);
                    $otherTotal = GoalWeightValidator::goalKeyResultsTotal((int) $goalId, $record?->id);
                    $newTotal = $otherTotal + $current;
                    $ok = GoalWeightValidator::isValidTotal($newTotal);
                    $color = $ok ? '#059669' : ($newTotal > 100 ? '#dc2626' : '#d97706');

                    return new HtmlString(
                        '<div style="font-size:12.5px;line-height:1.5;color:' . $color . ';">'
                        . 'Existing KR weight: <strong>' . number_format($otherTotal, 2) . '%</strong> • '
                        . 'This KR: <strong>' . number_format($current, 2) . '%</strong> • '
                        . 'New total: <strong>' . number_format($newTotal, 2) . '% / 100%</strong>'
                        . ($ok ? ' ✓' : ($newTotal > 100 ? ' ✗ exceeds' : ' — under-allocated'))
                        . '</div>'
                    );
                })
                ->columnSpan('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->formatStateUsing(fn ($state) => $state ?: '—'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Key Result')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Weight')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2) . '%'),

                Tables\Columns\TextColumn::make('progress_value')
                    ->label('Current / Target')
                    ->getStateUsing(function ($record) {
                        $unit = $record->unit ? ' ' . $record->unit : '';
                        $current = number_format((float) $record->current_value, 2);
                        $target = $record->target_value !== null ? number_format((float) $record->target_value, 2) : '—';
                        return "{$current}{$unit} / {$target}{$unit}";
                    }),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => $record->progress_percent)
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

                Tables\Columns\TextColumn::make('progress_mode')
                    ->label('Mode')
                    ->formatStateUsing(fn ($state) => new HtmlString(match ($state) {
                        'auto' => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-blue-500/10 text-blue-500">Auto</span>',
                        'hybrid' => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-purple-500/10 text-purple-500">Hybrid</span>',
                        default => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-gray-500/10 text-gray-500">Manual</span>',
                    })),

                Tables\Columns\TextColumn::make('alignment_note')
                    ->label('Alignment')
                    ->formatStateUsing(fn ($state) => $state ?: '—'),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('update_progress')
                    ->label('Update Progress')
                    ->icon('heroicon-o-trending-up')
                    ->color('success')
                    ->form(fn ($record) => [
                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content(new HtmlString(
                                '<div style="font-size:12.5px;color:#6b7280;">Current: <strong>'
                                . number_format((float) $record->current_value, 2)
                                . ($record->unit ? ' ' . e($record->unit) : '')
                                . '</strong> — Target: <strong>'
                                . ($record->target_value !== null ? number_format((float) $record->target_value, 2) : '—')
                                . ($record->unit ? ' ' . e($record->unit) : '')
                                . '</strong></div>'
                            )),

                        Forms\Components\TextInput::make('value')
                            ->label('New Value')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->default(fn () => $record->current_value),

                        Forms\Components\Textarea::make('note')
                            ->label('Note (optional)')
                            ->rows(2)
                            ->placeholder('What changed? Why?'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->recordUpdate(
                            value: (float) $data['value'],
                            source: 'manual',
                            userId: auth()->id(),
                            note: $data['note'] ?? null,
                        );

                        Notification::make()
                            ->title('Progress updated')
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn ($record) => 'Update Progress — ' . $record->title)
                    ->modalWidth('md'),

                Tables\Actions\Action::make('recalculate')
                    ->label('Recalculate')
                    ->icon('heroicon-o-refresh')
                    ->color('primary')
                    ->visible(fn ($record) => $record->isAuto())
                    ->action(function ($record) {
                        $update = app(GoalProgressCalculator::class)->recalculate($record);

                        Notification::make()
                            ->title($update
                                ? "Recalculated: {$update->previous_value} → {$update->value}"
                                : 'Recalculated — no change.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(false),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
