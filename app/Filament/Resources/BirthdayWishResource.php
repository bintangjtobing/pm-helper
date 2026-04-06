<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BirthdayWishResource\Pages;
use App\Models\BirthdayWish;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class BirthdayWishResource extends Resource
{
    protected static ?string $model = BirthdayWish::class;
    protected static ?string $navigationIcon = 'heroicon-o-cake';
    protected static ?int $navigationSort = 6;
    protected static ?string $slug = 'birthday-wishes';

    protected static function getNavigationLabel(): string { return __('Birthday Wishes'); }
    protected static function getNavigationGroup(): ?string { return __('Settings'); }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\Textarea::make('wish')->label(__('Wish'))->required()->rows(3)->columnSpan('full'),
                Forms\Components\TextInput::make('tone')->label(__('Tone'))->placeholder('Warm, Inspirational, Funny...'),
                Forms\Components\Toggle::make('is_active')->label(__('Active'))->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('wish')->label(__('Wish'))
                    ->formatStateUsing(fn ($record) => new HtmlString(
                        '<div class="pl-2 text-sm italic text-gray-900 dark:text-gray-100 line-clamp-2">"' . e($record->wish) . '"</div>'
                    ))->searchable(),
                Tables\Columns\TextColumn::make('tone')->label(__('Tone')),
                Tables\Columns\BooleanColumn::make('is_active')->label(__('Active')),
            ])
            ->defaultSort('id')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBirthdayWishes::route('/'),
            'create' => Pages\CreateBirthdayWish::route('/create'),
            'edit' => Pages\EditBirthdayWish::route('/{record}/edit'),
        ];
    }
}
