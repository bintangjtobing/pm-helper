<?php

namespace App\Filament\Resources\GoalResource\RelationManagers;

use App\Rules\KeyResultWeightFits;
use App\Services\GoalWeightValidator;
use Filament\Forms;
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
                    ->reactive()
                    ->rules(function ($livewire, $record) {
                        $goalId = $livewire->ownerRecord->id;
                        return [new KeyResultWeightFits((int) $goalId, $record?->id)];
                    }),
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
                    ->visible(fn (callable $get) => in_array($get('progress_mode'), ['auto', 'hybrid'])),
            ]),

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
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('alignment_note')
                    ->label('Alignment')
                    ->formatStateUsing(fn ($state) => $state ?: '—'),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
