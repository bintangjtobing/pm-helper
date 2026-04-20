<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoalPeriodResource\Pages;
use App\Models\GoalPeriod;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class GoalPeriodResource extends Resource
{
    protected static ?string $model = GoalPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'goal-periods';

    protected static function getNavigationLabel(): string
    {
        return __('Periods');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Period Name'))
                    ->placeholder('e.g., Q2 2026')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('type')
                        ->label(__('Type'))
                        ->options([
                            'quarterly' => 'Quarterly (OKR)',
                            'monthly' => 'Monthly (KPI)',
                        ])
                        ->default('quarterly')
                        ->required(),

                    Forms\Components\DatePicker::make('start_date')
                        ->label(__('Start Date'))
                        ->required(),

                    Forms\Components\DatePicker::make('end_date')
                        ->label(__('End Date'))
                        ->required()
                        ->after('start_date'),
                ]),

                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'closed' => 'Closed',
                    ])
                    ->default('draft')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(2)
                    ->columnSpan('full'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Period'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => new HtmlString(
                        '<div class="text-sm font-medium text-gray-900 dark:text-gray-100">' . e($record->name) . '</div>'
                        . '<div class="text-xs text-gray-500">' . e(ucfirst($record->type)) . '</div>'
                    )),

                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('Start'))
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label(__('End'))
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($state) => new HtmlString(match ($state) {
                        'active' => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-green-500/10 text-green-500">Active</span>',
                        'closed' => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-gray-500/10 text-gray-500">Closed</span>',
                        default => '<span class="px-2 py-0.5 text-xs font-medium rounded bg-yellow-500/10 text-yellow-600">Draft</span>',
                    })),

                Tables\Columns\TextColumn::make('goals_count')
                    ->counts('goals')
                    ->label(__('Goals'))
                    ->sortable(),
            ])
            ->defaultSort('start_date', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoalPeriods::route('/'),
            'create' => Pages\CreateGoalPeriod::route('/create'),
            'edit' => Pages\EditGoalPeriod::route('/{record}/edit'),
        ];
    }
}
