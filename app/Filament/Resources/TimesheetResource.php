<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimesheetResource\Pages;
use App\Filament\Resources\TimesheetResource\RelationManagers;
use App\Models\Activity;
use App\Models\TicketHour;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class TimesheetResource extends Resource
{
    protected static ?string $model = TicketHour::class;

    protected static ?string $navigationIcon = 'heroicon-o-badge-check';

    protected static ?int $navigationSort = 4;

    protected static function getNavigationLabel(): string
    {
        return __('Timesheet');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Timesheet');
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('List timesheet data');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Select::make('activity_id')
                            ->label(__('Activity'))
                            ->searchable()
                            ->reactive()
                            ->options(function ($get, $set) {
                                return Activity::all()->pluck('name', 'id')->toArray();
                            }),
                        TextInput::make('value')
                            ->label(__('Time to log'))
                            ->numeric()
                            ->required(),

                        Textarea::make('comment')
                            ->label(__('Comment'))
                            ->rows(3),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User'))
                    ->formatStateUsing(function ($record) {
                        $user = $record->user;
                        if (!$user) return '-';
                        $avatar = $user->getAttributes()['avatar_url']
                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
                        return new HtmlString('
                            <div class="flex items-center gap-2">
                                <img src="' . e($avatar) . '" class="w-7 h-7 rounded-full object-cover" loading="lazy" />
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">' . e($user->name) . '</span>
                            </div>
                        ');
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ticket.name')
                    ->label(__('Ticket'))
                    ->formatStateUsing(function ($record) {
                        if (!$record->ticket) return new HtmlString('<span class="text-xs text-gray-400">-</span>');
                        return new HtmlString('
                            <div class="flex flex-col gap-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-mono text-gray-400 dark:text-gray-500">' . e($record->ticket->code) . '</span>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate">' . e($record->ticket->name) . '</span>
                                </div>
                            </div>
                        ');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label(__('Hours'))
                    ->formatStateUsing(fn ($state) => new HtmlString(
                        '<span class="text-sm font-semibold text-orange-600 dark:text-orange-400">' . $state . 'h</span>'
                    ))
                    ->sortable(),

                Tables\Columns\TextColumn::make('activity.name')
                    ->label(__('Activity'))
                    ->formatStateUsing(fn ($state) => $state
                        ? new HtmlString('<span class="px-2 py-0.5 text-xs font-medium rounded bg-violet-500/10 text-violet-500">' . e($state) . '</span>')
                        : new HtmlString('<span class="text-xs text-gray-400">-</span>'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label(__('Comment'))
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->since()
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
            'index' => Pages\ListTimesheet::route('/'),
            'edit' => Pages\EditTimesheet::route('/{record}/edit'),
        ];
    }
}
