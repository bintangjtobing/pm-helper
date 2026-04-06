<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'departments';

    protected static function getNavigationLabel(): string
    {
        return __('Departments');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Organization');
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
                        Forms\Components\TextInput::make('name')
                            ->label(__('Department Name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\ColorPicker::make('color')
                                    ->label(__('Color'))
                                    ->default('#6B7280'),

                                Forms\Components\Select::make('category')
                                    ->label(__('Category'))
                                    ->options([
                                        'core' => 'Core',
                                        'advanced' => 'Advanced',
                                    ])
                                    ->default('core')
                                    ->required(),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label(__('Sort Order'))
                                    ->numeric()
                                    ->default(0),
                            ]),

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
                    ->label(__('Department'))
                    ->formatStateUsing(function ($record) {
                        return new HtmlString(
                            '<div class="flex items-center gap-2 pl-2">'
                            . '<span style="width:10px;height:10px;border-radius:50%;background:' . e($record->color) . ';display:inline-block;flex-shrink:0;"></span>'
                            . '<div>'
                            . '<div class="text-sm font-medium text-gray-900 dark:text-gray-100">' . e($record->name) . '</div>'
                            . '<div class="text-xs text-gray-500">' . e($record->description ?? '') . '</div>'
                            . '</div>'
                            . '</div>'
                        );
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label(__('Category'))
                    ->formatStateUsing(fn ($state) => new HtmlString(
                        $state === 'core'
                            ? '<span class="px-2 py-0.5 text-xs font-medium rounded bg-blue-500/10 text-blue-500">Core</span>'
                            : '<span class="px-2 py-0.5 text-xs font-medium rounded bg-orange-500/10 text-orange-500">Advanced</span>'
                    )),

                Tables\Columns\TextColumn::make('positions_count')
                    ->counts('positions')
                    ->label(__('Positions'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('Members'))
                    ->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
