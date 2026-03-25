<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketPriorityResource\Pages;
use App\Filament\Resources\TicketPriorityResource\RelationManagers;
use App\Models\TicketPriority;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Guava\FilamentIconPicker\Tables\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketPriorityResource extends Resource
{
    protected static ?string $model = TicketPriority::class;

    protected static ?string $navigationIcon = 'heroicon-o-badge-check';

    protected static ?int $navigationSort = 4;

    protected static function getNavigationLabel(): string
    {
        return __('Ticket priorities');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Referential');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Priority name'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('level')
                                    ->label(__('Level code'))
                                    ->placeholder('P0, P1, P2, P3')
                                    ->maxLength(10),

                                Forms\Components\ColorPicker::make('color')
                                    ->label(__('Priority color'))
                                    ->required(),

                                Forms\Components\Checkbox::make('is_default')
                                    ->label(__('Default priority'))
                                    ->helperText(
                                        __('If checked, this priority will be automatically affected to new tickets')
                                    ),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label(__('Description'))
                            ->helperText(__('What does this priority level mean?'))
                            ->rows(2),

                        Forms\Components\Textarea::make('examples')
                            ->label(__('Examples'))
                            ->helperText(__('Real-world examples of issues at this priority level'))
                            ->rows(2),

                        Forms\Components\TextInput::make('action')
                            ->label(__('Required action'))
                            ->helperText(__('What action should be taken for tickets at this priority?'))
                            ->maxLength(255),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('level')
                    ->label(__('Level'))
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\ColorColumn::make('color')
                    ->label(__('Color'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Priority name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(60)
                    ->tooltip(fn($record) => $record->description),

                Tables\Columns\TextColumn::make('action')
                    ->label(__('Required action'))
                    ->limit(40),

                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('Default'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListTicketPriorities::route('/'),
            'create' => Pages\CreateTicketPriority::route('/create'),
            'view' => Pages\ViewTicketPriority::route('/{record}'),
            'edit' => Pages\EditTicketPriority::route('/{record}/edit'),
        ];
    }
}
