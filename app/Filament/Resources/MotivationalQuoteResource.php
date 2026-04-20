<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MotivationalQuoteResource\Pages;
use App\Models\MotivationalQuote;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class MotivationalQuoteResource extends Resource
{
    protected static ?string $model = MotivationalQuote::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'motivational-quotes';

    protected static function getNavigationLabel(): string
    {
        return __('Motivational Quotes');
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
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Textarea::make('quote')
                            ->label(__('Quote'))
                            ->required()
                            ->rows(3)
                            ->columnSpan('full'),

                        Forms\Components\TextInput::make('author')
                            ->label(__('Author'))
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quote')
                    ->label(__('Quote'))
                    ->formatStateUsing(fn ($record) => new HtmlString(
                        '<div class="min-w-0 pl-2">'
                        . '<div class="text-sm text-gray-900 dark:text-gray-100 italic line-clamp-2">"' . e($record->quote) . '"</div>'
                        . '<div class="text-xs text-gray-500 mt-0.5">— ' . e($record->author ?? 'Unknown') . '</div>'
                        . '</div>'
                    ))
                    ->searchable(),

                Tables\Columns\BooleanColumn::make('is_active')
                    ->label(__('Active')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListMotivationalQuotes::route('/'),
            'create' => Pages\CreateMotivationalQuote::route('/create'),
            'edit' => Pages\EditMotivationalQuote::route('/{record}/edit'),
        ];
    }
}
