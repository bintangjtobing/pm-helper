<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscussionResource\Pages;
use App\Models\Discussion;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class DiscussionResource extends Resource
{
    protected static ?string $model = Discussion::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-alt-2';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'discussions';

    protected static function getNavigationLabel(): string
    {
        return __('Discussions');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Reports');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('List discussions') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('Topic Title'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('project_id')
                                    ->label(__('Project'))
                                    ->helperText(__('Optional — leave empty for a general discussion'))
                                    ->searchable()
                                    ->options(function () {
                                        $user = auth()->user();
                                        if ($user->hasRole('Super Admin')) {
                                            return Project::all()->pluck('name', 'id');
                                        }
                                        $owned = Project::where('owner_id', $user->id)->pluck('name', 'id');
                                        $attached = $user->projects()->pluck('name', 'projects.id');
                                        return $owned->union($attached);
                                    })
                                    ->reactive(),

                                Forms\Components\Select::make('ticket_id')
                                    ->label(__('Related Ticket'))
                                    ->helperText(__('Optional — link to a specific ticket'))
                                    ->searchable()
                                    ->options(function (callable $get) {
                                        $query = Ticket::query();
                                        if ($projectId = $get('project_id')) {
                                            $query->where('project_id', $projectId);
                                        }
                                        return $query->limit(50)->get()->mapWithKeys(fn ($t) => [$t->id => "{$t->code} — {$t->name}"]);
                                    }),

                                Forms\Components\Select::make('priority')
                                    ->label(__('Priority'))
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                    ])
                                    ->default('medium')
                                    ->required(),
                            ]),

                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),

                        Forms\Components\RichEditor::make('content')
                            ->label(__('Description'))
                            ->helperText(__('Describe the topic you want to discuss'))
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'strike', 'link',
                                'orderedList', 'bulletList', 'blockquote',
                                'codeBlock', 'h2', 'h3', 'redo', 'undo',
                            ])
                            ->columnSpan('full'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Topic'))
                    ->formatStateUsing(function ($record) {
                        $user = $record->user;
                        $avatar = $user->getAttributes()['avatar_url']
                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
                        $replyCount = $record->replies_count ?? 0;

                        $badges = '';
                        if ($record->ticket) {
                            $badges .= '<span class="px-1.5 py-0.5 text-[10px] font-mono rounded bg-blue-500/10 text-blue-500">' . e($record->ticket->code) . '</span>';
                        }
                        if ($replyCount > 0) {
                            $badges .= '<span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-medium rounded-full bg-green-500/10 text-green-600 dark:text-green-400">'
                                . '<svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>'
                                . $replyCount . '</span>';
                        }

                        return new HtmlString(
                            '<div class="flex items-start gap-2.5 pl-2">'
                            . '<img src="' . e($avatar) . '" class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5" loading="lazy" />'
                            . '<div class="min-w-0">'
                            . '<div class="text-sm font-medium text-gray-900 dark:text-gray-100">' . e($record->title) . '</div>'
                            . '<div class="flex items-center gap-1.5 mt-0.5">'
                            . '<span class="text-xs text-gray-500">' . e($user->name) . '</span>'
                            . '<span class="text-xs text-gray-300 dark:text-gray-600">&middot;</span>'
                            . '<span class="text-xs text-gray-400">' . $record->created_at->diffForHumans() . '</span>'
                            . ($badges ? '<span class="text-xs text-gray-300 dark:text-gray-600">&middot;</span>' . $badges : '')
                            . '</div>'
                            . '</div>'
                            . '</div>'
                        );
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->label(__('Project'))
                    ->formatStateUsing(fn ($record) => new HtmlString(
                        '<span class="px-2 py-0.5 text-xs font-medium rounded bg-primary-500/10 text-primary-500">'
                        . e($record->project?->name ?? __('General'))
                        . '</span>'
                    )),

                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->formatStateUsing(fn ($record) => new HtmlString($record->priority_badge)),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($record) => new HtmlString($record->status_badge)),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_discussion' => 'In Discussion',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['user', 'project', 'ticket'])->withCount('replies');

        if ($user->hasRole(['Super Admin', 'Stakeholder'])) {
            return $query;
        }

        if ($user->hasRole('Project Manager')) {
            $pmProjectIds = Project::where('owner_id', $user->id)->pluck('id')
                ->merge($user->projects()->pluck('projects.id'))
                ->unique();

            return $query->where(function ($q) use ($user, $pmProjectIds) {
                $q->where('user_id', $user->id)
                    ->orWhereIn('project_id', $pmProjectIds)
                    ->orWhereNull('project_id');
            });
        }

        $userProjectIds = $user->projects()->pluck('projects.id');

        return $query->where(function ($q) use ($user, $userProjectIds) {
            $q->where('user_id', $user->id)
                ->orWhereIn('project_id', $userProjectIds)
                ->orWhereNull('project_id');
        });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscussions::route('/'),
            'create' => Pages\CreateDiscussion::route('/create'),
            'view' => Pages\ViewDiscussion::route('/{record}'),
            'edit' => Pages\EditDiscussion::route('/{record}/edit'),
        ];
    }
}
