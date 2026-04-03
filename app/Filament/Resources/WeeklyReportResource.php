<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeeklyReportResource\Pages;
use App\Models\Project;
use App\Models\User;
use App\Models\WeeklyReport;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class WeeklyReportResource extends Resource
{
    protected static ?string $model = WeeklyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'weekly-reports';

    protected static function getNavigationLabel(): string
    {
        return __('Weekly Reports');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Management');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('List weekly reports') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['user', 'project']);

        if ($user->hasRole(['Super Admin', 'Stakeholder'])) {
            return $query;
        }

        if ($user->hasRole('Project Manager')) {
            // PM sees own reports + team reports from shared projects
            $pmProjectIds = Project::where('owner_id', $user->id)->pluck('id')
                ->merge($user->projects()->pluck('projects.id'))
                ->unique();

            $teamUserIds = User::whereHas('projects', function ($q) use ($pmProjectIds) {
                $q->whereIn('projects.id', $pmProjectIds);
            })->pluck('id')->push($user->id)->unique();

            return $query->whereIn('user_id', $teamUserIds);
        }

        // Developer, QA, DevOps — own reports only
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
                                Forms\Components\Select::make('week_start')
                                    ->label(__('Report Week'))
                                    ->helperText(__('Select which week to report on'))
                                    ->required()
                                    ->default(now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d'))
                                    ->options(function () {
                                        $options = [];
                                        $current = now()->startOfWeek(\Carbon\Carbon::MONDAY);
                                        // Show last 8 weeks + current week
                                        for ($i = 0; $i < 9; $i++) {
                                            $monday = $current->copy()->subWeeks($i);
                                            $friday = $monday->copy()->addDays(4);
                                            $sunday = $monday->copy()->addDays(6);
                                            $label = $monday->format('M d') . ' – ' . $friday->format('M d, Y');
                                            if ($i === 0) $label .= ' (this week)';
                                            if ($i === 1) $label .= ' (last week)';
                                            $options[$monday->format('Y-m-d')] = $label;
                                        }
                                        return $options;
                                    })
                                    ->reactive(),

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

                        Forms\Components\RichEditor::make('content')
                            ->label(__('Report Content'))
                            ->helperText(__('Write your weekly report. Auto-generated summary is pre-filled below.'))
                            ->toolbarButtons([
                                'bold', 'italic', 'strike', 'link',
                                'orderedList', 'bulletList', 'blockquote',
                                'codeBlock', 'h2', 'h3', 'redo', 'undo',
                            ])
                            ->columnSpan('full'),

                        Forms\Components\SpatieMediaLibraryFileUpload::make('attachments')
                            ->label(__('Attachments'))
                            ->helperText(__('Upload PDF or DOCX files'))
                            ->collection('attachments')
                            ->multiple()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->maxSize(config('system.max_file_size'))
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
                                    <div class="text-xs text-gray-500 dark:text-gray-400">' . e($record->week_label) . '</div>
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

                Tables\Columns\TextColumn::make('feedbacks_count')
                    ->counts('feedbacks')
                    ->label(__('Feedback'))
                    ->formatStateUsing(fn ($state) => $state > 0
                        ? new HtmlString('<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-500/10 text-blue-500">' . $state . '</span>')
                        : new HtmlString('<span class="text-xs text-gray-400">0</span>'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label(__('Submitted'))
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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

                Tables\Actions\Action::make('acknowledge')
                    ->label(__('Acknowledge'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        $record->status === 'submitted' &&
                        auth()->user()->hasRole(['Super Admin', 'Project Manager'])
                    )
                    ->requiresConfirmation()
                    ->action(function (WeeklyReport $record) {
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
            'index' => Pages\ListWeeklyReports::route('/'),
            'create' => Pages\CreateWeeklyReport::route('/create'),
            'view' => Pages\ViewWeeklyReport::route('/{record}'),
            'edit' => Pages\EditWeeklyReport::route('/{record}/edit'),
        ];
    }
}
