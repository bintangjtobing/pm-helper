<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyReportResource\Pages;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class DailyReportResource extends Resource
{
    protected static ?string $model = DailyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'daily-reports';

    protected static function getNavigationLabel(): string
    {
        return __('Daily Reports');
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
        return auth()->user()?->can('List daily reports') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['user', 'project']);

        if ($user->hasRole(['Super Admin', 'Stakeholder'])) {
            return $query;
        }

        if ($user->hasRole('Project Manager')) {
            $pmProjectIds = Project::where('owner_id', $user->id)->pluck('id')
                ->merge($user->projects()->pluck('projects.id'))
                ->unique();

            $teamUserIds = User::whereHas('projects', function ($q) use ($pmProjectIds) {
                $q->whereIn('projects.id', $pmProjectIds);
            })->pluck('id')->push($user->id)->unique();

            return $query->whereIn('user_id', $teamUserIds);
        }

        return $query->where('user_id', $user->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('report_date')
                                    ->label(__('Report Date'))
                                    ->required()
                                    ->default(now()->format('Y-m-d'))
                                    ->maxDate(now()),

                                Forms\Components\Select::make('project_id')
                                    ->label(__('Project'))
                                    ->helperText(__('Optional — leave empty for a general report'))
                                    ->searchable()
                                    ->options(function () {
                                        $user = auth()->user();
                                        if ($user->hasRole('Super Admin')) {
                                            return Project::all()->pluck('name', 'id');
                                        }
                                        $owned = Project::where('owner_id', $user->id)->pluck('name', 'id');
                                        $attached = $user->projects()->pluck('name', 'projects.id');
                                        return $owned->union($attached);
                                    }),

                                Forms\Components\Hidden::make('user_id')
                                    ->default(auth()->id()),
                            ]),

                        Forms\Components\RichEditor::make('accomplished')
                            ->label(__('What was accomplished today?'))
                            ->helperText(__('Describe what you worked on and completed today'))
                            ->toolbarButtons([
                                'bold', 'italic', 'strike', 'link',
                                'orderedList', 'bulletList', 'blockquote',
                                'h2', 'h3', 'redo', 'undo',
                            ])
                            ->columnSpan('full'),

                        Forms\Components\RichEditor::make('plans')
                            ->label(__('Plans for tomorrow'))
                            ->helperText(__('What do you plan to work on next?'))
                            ->toolbarButtons([
                                'bold', 'italic', 'strike', 'link',
                                'orderedList', 'bulletList',
                                'redo', 'undo',
                            ])
                            ->columnSpan('full'),

                        Forms\Components\RichEditor::make('blockers')
                            ->label(__('Blockers / Issues'))
                            ->helperText(__('Any blockers, impediments, or issues? Leave empty if none'))
                            ->toolbarButtons([
                                'bold', 'italic', 'strike', 'link',
                                'orderedList', 'bulletList',
                                'redo', 'undo',
                            ])
                            ->columnSpan('full'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Author'))
                    ->formatStateUsing(function ($record) {
                        $user = $record->user;
                        $avatar = $user->getAttributes()['avatar_url']
                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
                        return new HtmlString('
                            <div class="flex items-center gap-2.5">
                                <img src="' . e($avatar) . '" class="w-7 h-7 rounded-full object-cover" loading="lazy" />
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">' . e($user->name) . '</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">' . e($record->date_label) . '</div>
                                </div>
                            </div>
                        ');
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->label(__('Project'))
                    ->formatStateUsing(fn ($record) => new HtmlString(
                        '<span class="px-2 py-0.5 text-xs font-medium rounded bg-primary-500/10 text-primary-500">'
                        . e($record->project?->name ?? __('General'))
                        . '</span>'
                    ))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($record) => new HtmlString($record->status_badge))
                    ->sortable(),

                Tables\Columns\TextColumn::make('report_date')
                    ->label(__('Date'))
                    ->date('D, d M Y')
                    ->sortable(),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'acknowledged' => 'Acknowledged',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('Author'))
                    ->options(fn () => User::all()->pluck('name', 'id'))
                    ->visible(fn () => auth()->user()->hasRole(['Super Admin', 'Project Manager', 'Stakeholder'])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) =>
                        $record->user_id === auth()->id() && $record->status === 'draft'
                    ),

                Tables\Actions\Action::make('submit')
                    ->label(__('Submit'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn ($record) =>
                        $record->user_id === auth()->id() && $record->status === 'draft'
                    )
                    ->requiresConfirmation()
                    ->action(function (DailyReport $record) {
                        $record->update([
                            'status' => 'submitted',
                            'submitted_at' => now(),
                        ]);
                    }),

                Tables\Actions\Action::make('acknowledge')
                    ->label(__('Acknowledge'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        $record->status === 'submitted' &&
                        auth()->user()->hasRole(['Super Admin', 'Project Manager'])
                    )
                    ->requiresConfirmation()
                    ->action(function (DailyReport $record) {
                        $record->update([
                            'status' => 'acknowledged',
                            'acknowledged_by' => auth()->id(),
                            'acknowledged_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyReports::route('/'),
            'create' => Pages\CreateDailyReport::route('/create'),
            'view' => Pages\ViewDailyReport::route('/{record}'),
            'edit' => Pages\EditDailyReport::route('/{record}/edit'),
        ];
    }
}
