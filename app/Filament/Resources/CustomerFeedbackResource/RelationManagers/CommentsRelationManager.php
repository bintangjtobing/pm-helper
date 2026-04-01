<?php

namespace App\Filament\Resources\CustomerFeedbackResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = 'content';

    public static function getTitle(): string
    {
        return __('Conversation');
    }

    protected static function getModelLabel(): string
    {
        return __('Comment');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->label(__('Message'))
                    ->required()
                    ->rows(3)
                    ->placeholder(__('Type your message...'))
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('From'))
                    ->formatStateUsing(function ($record) {
                        $name = $record->user->name ?? 'Unknown';
                        $roles = $record->user->roles->pluck('name')->implode(', ');
                        $avatar = $record->user->avatar_url ?? '';
                        $time = $record->created_at->diffForHumans();

                        $avatarHtml = $avatar
                            ? '<img src="' . e($avatar) . '" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">'
                            : '<div style="width:28px;height:28px;border-radius:50%;background:#374151;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:12px;font-weight:600;">' . strtoupper(substr($name, 0, 1)) . '</div>';

                        return new HtmlString('
                            <div style="display:flex;align-items:center;gap:8px;">
                                ' . $avatarHtml . '
                                <div>
                                    <div style="font-weight:600;font-size:13px;">' . e($name) . '</div>
                                    <div style="font-size:11px;color:#9ca3af;">' . e($roles) . ' - ' . $time . '</div>
                                </div>
                            </div>
                        ');
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('content')
                    ->label(__('Message'))
                    ->wrap()
                    ->formatStateUsing(fn ($state) => new HtmlString(
                        '<div style="white-space:pre-wrap;font-size:13px;line-height:1.5;">' . e($state) . '</div>'
                    )),
            ])
            ->defaultSort('created_at', 'asc')
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Reply'))
                    ->modalHeading(__('Add Comment'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) =>
                        $record->user_id === auth()->id()
                        || auth()->user()->hasRole(['Super Admin', 'Admin'])
                    ),
            ])
            ->bulkActions([]);
    }
}
