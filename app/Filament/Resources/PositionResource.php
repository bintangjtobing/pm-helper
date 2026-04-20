<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionResource\Pages;
use App\Models\Department;
use App\Models\Position;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'positions';

    protected static function getNavigationLabel(): string
    {
        return __('Positions');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Team');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('department_id')
                            ->label(__('Department'))
                            ->options(Department::orderBy('sort_order')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('name')
                            ->label(__('Position Name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('sub_division')
                                    ->label(__('Sub-Division'))
                                    ->helperText(__('e.g. SEO Team, Paid Ads Team'))
                                    ->placeholder(__('Optional')),

                                Forms\Components\Select::make('level')
                                    ->label(__('Level'))
                                    ->options([
                                        0 => 'Staff',
                                        1 => 'Lead',
                                        2 => 'Manager',
                                        3 => 'Head',
                                        4 => 'C-Level',
                                    ])
                                    ->default(0)
                                    ->required(),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label(__('Sort Order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Position'))
                    ->formatStateUsing(function ($record) {
                        $sub = $record->sub_division ? '<div class="text-xs text-gray-400">' . e($record->sub_division) . '</div>' : '';
                        return new HtmlString(
                            '<div class="pl-2">'
                            . '<div class="text-sm font-medium text-gray-900 dark:text-gray-100">' . e($record->name) . '</div>'
                            . $sub
                            . '</div>'
                        );
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->formatStateUsing(function ($record) {
                        return new HtmlString(
                            '<div class="flex items-center gap-1.5">'
                            . '<span style="width:8px;height:8px;border-radius:50%;background:' . e($record->department->color) . ';display:inline-block;"></span>'
                            . '<span class="text-xs">' . e($record->department->name) . '</span>'
                            . '</div>'
                        );
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('level')
                    ->label(__('Level'))
                    ->formatStateUsing(fn ($state) => match((int)$state) {
                        4 => new HtmlString('<span class="px-2 py-0.5 text-xs font-medium rounded bg-red-500/10 text-red-500">C-Level</span>'),
                        3 => new HtmlString('<span class="px-2 py-0.5 text-xs font-medium rounded bg-orange-500/10 text-orange-500">Head</span>'),
                        2 => new HtmlString('<span class="px-2 py-0.5 text-xs font-medium rounded bg-blue-500/10 text-blue-500">Manager</span>'),
                        1 => new HtmlString('<span class="px-2 py-0.5 text-xs font-medium rounded bg-green-500/10 text-green-500">Lead</span>'),
                        default => new HtmlString('<span class="px-2 py-0.5 text-xs font-medium rounded bg-gray-500/10 text-gray-500">Staff</span>'),
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('Members'))
                    ->sortable(),
            ])
            ->defaultSort('department_id')
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label(__('Department'))
                    ->options(Department::orderBy('sort_order')->pluck('name', 'id')),
            ])
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
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
