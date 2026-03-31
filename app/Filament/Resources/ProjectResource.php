<?php

namespace App\Filament\Resources;

use App\Exports\ProjectHoursExport;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use App\Models\ProjectFavorite;
use App\Models\ProjectStatus;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive';

    protected static ?int $navigationSort = 1;

    protected static function getNavigationLabel(): string
    {
        return __('Projects');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(3)
                            ->schema([
                                Forms\Components\SpatieMediaLibraryFileUpload::make('cover')
                                    ->label(__('Cover image'))
                                    ->image()
                                    ->helperText(
                                        __('If not selected, an image will be generated based on the project name')
                                    )
                                    ->columnSpan(1),

                                Forms\Components\Grid::make()
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->columnSpan(2)
                                            ->columns(12)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label(__('Project name'))
                                                    ->required()
                                                    ->columnSpan(10)
                                                    ->maxLength(255),

                                                Forms\Components\TextInput::make('ticket_prefix')
                                                    ->label(__('Ticket prefix'))
                                                    ->maxLength(3)
                                                    ->columnSpan(2)
                                                    ->unique(Project::class, column: 'ticket_prefix', ignoreRecord: true)
                                                    ->disabled(
                                                        fn($record) => $record && $record->tickets()->count() != 0
                                                    )
                                                    ->required()
                                            ]),

                                        Forms\Components\Select::make('owner_id')
                                            ->label(__('Project owner'))
                                            ->searchable()
                                            ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => auth()->user()->id)
                                            ->required(),

                                        Forms\Components\Select::make('status_id')
                                            ->label(__('Project status'))
                                            ->searchable()
                                            ->options(fn() => ProjectStatus::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => ProjectStatus::where('is_default', true)->first()?->id)
                                            ->required(),
                                    ]),

                                Forms\Components\RichEditor::make('description')
                                    ->label(__('Project description'))
                                    ->columnSpan(3)
                                    ->rules(['max:2000'])
                                    ->reactive()
                                    ->hint(fn ($state) =>
                                        (2000 - mb_strlen(strip_tags($state ?? ''))) . ' characters remaining'
                                    ),

                                Forms\Components\Select::make('type')
                                    ->label(__('Project type'))
                                    ->searchable()
                                    ->options([
                                        'kanban' => __('Kanban'),
                                        'scrum' => __('Scrum')
                                    ])
                                    ->reactive()
                                    ->default(fn() => 'kanban')
                                    ->helperText(function ($state) {
                                        if ($state === 'kanban') {
                                            return __('Display and move your project forward with issues on a powerful board.');
                                        } elseif ($state === 'scrum') {
                                            return __('Achieve your project goals with a board, backlog, and roadmap.');
                                        }
                                        return '';
                                    })
                                    ->required(),

                                Forms\Components\Select::make('status_type')
                                    ->label(__('Statuses configuration'))
                                    ->helperText(
                                        __('If custom type selected, you need to configure project specific statuses')
                                    )
                                    ->searchable()
                                    ->reactive()
                                    ->options([
                                        'default' => __('Default'),
                                        'custom' => __('Custom configuration')
                                    ])
                                    ->default(fn() => 'default')
                                    ->disabled(fn($record) => $record && $record->tickets()->count())
                                    ->required(),
                            ]),
                    ]),

                // Project Knowledge Base
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('knowledge_base_heading')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">' . __('Project Knowledge Base') . '</h3>
                                    <p class="text-sm text-gray-600">' . __('Define project goals, requirements, and upload supporting documents. The PM Assistant bot will use this information to provide better assistance.') . '</p>
                                </div>
                            ')),

                        Forms\Components\SpatieMediaLibraryFileUpload::make('documents')
                            ->label(__('Upload Documents (PDF)'))
                            ->collection('documents')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(51200) // 50MB
                            ->preserveFilenames()
                            ->helperText(__('Upload PDF documents (PRD, specs, designs, etc.). Max 50MB per file.'))
                            ->columnSpan(2),

                        Forms\Components\Placeholder::make('document_list')
                            ->label(__('Uploaded Documents'))
                            ->content(function ($record) {
                                if (!$record) return new HtmlString('<span style="color:#6b7280;font-size:13px;">No documents yet.</span>');
                                $docs = $record->getMedia('documents');
                                if ($docs->isEmpty()) return new HtmlString('<span style="color:#6b7280;font-size:13px;">No documents uploaded.</span>');

                                $html = '<div style="display:flex;flex-direction:column;gap:8px;">';
                                foreach ($docs as $doc) {
                                    $url = $doc->getUrl();
                                    $name = e($doc->file_name);
                                    $size = $doc->human_readable_size;
                                    $html .= '<div style="display:flex;align-items:center;gap:10px;padding:10px 14px;'
                                        . 'background:rgba(31,41,55,0.5);border:1px solid rgba(55,65,81,0.5);border-radius:8px;">'
                                        . '<svg style="width:20px;height:20px;color:#ef4444;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20">'
                                        . '<path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>'
                                        . '</svg>'
                                        . '<div style="flex:1;min-width:0;">'
                                        . '<div style="font-size:13px;font-weight:500;color:#f3f4f6;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' . $name . '</div>'
                                        . '<div style="font-size:11px;color:#9ca3af;">' . $size . '</div>'
                                        . '</div>'
                                        . '<a href="' . e($url) . '" target="_blank" rel="noopener"'
                                        . ' style="padding:5px 12px;background:#2563eb;color:white;border-radius:6px;'
                                        . 'font-size:12px;font-weight:500;text-decoration:none;white-space:nowrap;">'
                                        . __('View') . '</a>'
                                        . '<a href="' . e($url) . '" download'
                                        . ' style="padding:5px 12px;background:#374151;color:#e5e7eb;border-radius:6px;'
                                        . 'font-size:12px;font-weight:500;text-decoration:none;white-space:nowrap;">'
                                        . __('Download') . '</a>'
                                        . '</div>';
                                }
                                $html .= '</div>';
                                return new HtmlString($html);
                            })
                            ->columnSpan(2),

                        Forms\Components\Placeholder::make('generate_goals_btn')
                            ->label('')
                            ->content(new HtmlString('
                                <button type="button"
                                    wire:click="generateGoalsFromDocuments"
                                    wire:loading.attr="disabled"
                                    wire:target="generateGoalsFromDocuments"
                                    style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;
                                           background:#2563eb;color:white;border:none;border-radius:8px;
                                           font-size:13px;font-weight:500;cursor:pointer;transition:background 0.15s;"
                                    onmouseover="this.style.background=\'#1d4ed8\'"
                                    onmouseout="this.style.background=\'#2563eb\'">
                                    <span wire:loading.remove wire:target="generateGoalsFromDocuments">
                                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="generateGoalsFromDocuments">
                                        <svg style="width:16px;height:16px;animation:spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="generateGoalsFromDocuments">'
                                        . __('Generate Goals from Documents (AI)') .
                                    '</span>
                                    <span wire:loading wire:target="generateGoalsFromDocuments">'
                                        . __('Analyzing documents...') .
                                    '</span>
                                </button>
                                <style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
                                <p style="margin-top:6px;font-size:12px;color:#9ca3af;">'
                                    . __('AI will read all uploaded PDFs and auto-generate goals and requirements summary.') .
                                '</p>
                            '))
                            ->visibleOn('edit'),

                        Forms\Components\RichEditor::make('goals')
                            ->label(__('Project Goals & Requirements'))
                            ->helperText(__('Auto-generated from documents or manually defined. The AI assistant uses this as context.'))
                            ->columnSpan(2),
                    ]),

                // Auto Complete Settings Card
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('auto_complete_heading')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">' . __('Auto Complete Settings') . '</h3>
                                    <p class="text-sm text-gray-600">' . __('Configure automatic ticket completion when cards stay in review status too long') . '</p>
                                </div>
                            ')),

                        Forms\Components\Grid::make()
                            ->columns(2)
                            ->schema([
                                Forms\Components\Checkbox::make('auto_complete_enabled')
                                    ->label(__('Enable Auto Complete'))
                                    ->helperText(__('Automatically move tickets to completed status when they stay too long in review'))
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('auto_complete_days')
                                    ->label(__('Days to wait'))
                                    ->helperText(__('Number of days a ticket can stay in the status before auto-completion'))
                                    ->numeric()
                                    ->default(3)
                                    ->minValue(1)
                                    ->maxValue(30)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('auto_complete_from_status')
                                    ->label(__('Monitor Status'))
                                    ->helperText(__('The status to monitor (e.g., "In Review")'))
                                    ->placeholder('e.g., In Review')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('auto_complete_to_status')
                                    ->label(__('Target Status'))
                                    ->helperText(__('The status to move tickets to (e.g., "Completed")'))
                                    ->placeholder('e.g., Completed')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Placeholder::make('auto_complete_info')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="p-4 mt-4 border border-blue-200 rounded-lg bg-blue-50">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-sm font-medium text-blue-800">
                                                ' . __('How Auto Complete Works') . '
                                            </h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <p>
                                                    ' . __('When enabled, this feature will automatically move tickets from the "Monitor Status" to the "Target Status" if they remain unchanged for the specified number of days.') . '
                                                </p>
                                                <p class="mt-1">
                                                    ' . __('This feature runs daily via scheduled command and helps prevent tickets from getting stuck in review.') . '
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ')),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')
                    ->label(__('Cover image'))
                    ->collection('cover')
                    ->rounded()
                    ->width(40)
                    ->height(40),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Project name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label(__('Project owner'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status.name')
                    ->label(__('Project status'))
                    ->formatStateUsing(fn($record) => new HtmlString('
                            <div class="flex items-center gap-2">
                                <span class="relative flex w-6 h-6 rounded-md filament-tables-color-column"
                                    style="background-color: ' . $record->status->color . '"></span>
                                <span>' . $record->status->name . '</span>
                            </div>
                        '))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TagsColumn::make('users.name')
                    ->label(__('Affected users'))
                    ->limit(2),

                Tables\Columns\BadgeColumn::make('type')
                    ->enum([
                        'kanban' => __('Kanban'),
                        'scrum' => __('Scrum')
                    ])
                    ->colors([
                        'secondary' => 'kanban',
                        'warning' => 'scrum',
                    ]),

                Tables\Columns\IconColumn::make('auto_complete_enabled')
                    ->label(__('Auto Complete'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('owner_id')
                    ->label(__('Owner'))
                    ->multiple()
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->multiple()
                    ->options(fn() => ProjectStatus::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\TernaryFilter::make('auto_complete_enabled')
                    ->label(__('Auto Complete'))
                    ->placeholder(__('All projects'))
                    ->trueLabel(__('Auto Complete Enabled'))
                    ->falseLabel(__('Auto Complete Disabled')),
            ])
            ->actions([

                Tables\Actions\Action::make('favorite')
                    ->label('')
                    ->icon('heroicon-o-star')
                    ->color(fn($record) => auth()->user()->favoriteProjects()
                        ->where('projects.id', $record->id)->count() ? 'success' : 'default')
                    ->action(function ($record) {
                        $projectId = $record->id;
                        $projectFavorite = ProjectFavorite::where('project_id', $projectId)
                            ->where('user_id', auth()->user()->id)
                            ->first();
                        if ($projectFavorite) {
                            $projectFavorite->delete();
                        } else {
                            ProjectFavorite::create([
                                'project_id' => $projectId,
                                'user_id' => auth()->user()->id
                            ]);
                        }
                        Filament::notify('success', __('Project updated'));
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('exportLogHours')
                        ->label(__('Export hours'))
                        ->icon('heroicon-o-document-download')
                        ->color('secondary')
                        ->action(fn($record) => Excel::download(
                            new ProjectHoursExport($record),
                            'time_' . Str::slug($record->name) . '.csv',
                            \Maatwebsite\Excel\Excel::CSV,
                            ['Content-Type' => 'text/csv']
                        )),

                    Tables\Actions\Action::make('kanban')
                        ->label(
                            fn ($record)
                                => ($record->type === 'scrum' ? __('Scrum board') : __('Kanban board'))
                        )
                        ->icon('heroicon-o-view-boards')
                        ->color('secondary')
                        ->url(function ($record) {
                            if ($record->type === 'scrum') {
                                return route('filament.pages.scrum/{project}', ['project' => $record->id]);
                            } else {
                                return route('filament.pages.kanban/{project}', ['project' => $record->id]);
                            }
                        }),

                    Tables\Actions\Action::make('autoCompleteStatus')
                        ->label(__('Auto Complete Status'))
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->visible(fn($record) => $record->auto_complete_enabled)
                        ->modalContent(function ($record) {
                            $eligibleTickets = $record->getTicketsForAutoCompletion();
                            $fromStatus = $record->getAutoCompleteFromStatus();
                            $toStatus = $record->getAutoCompleteToStatus();

                            return view('components.auto-complete-status-modal', [
                                'project' => $record,
                                'eligibleTickets' => $eligibleTickets,
                                'fromStatus' => $fromStatus,
                                'toStatus' => $toStatus,
                            ]);
                        })
                        ->modalHeading(__('Auto Complete Status'))
                        ->modalWidth('2xl'),
                ])->color('secondary'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SprintsRelationManager::class,
            RelationManagers\UsersRelationManager::class,
            RelationManagers\StatusesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
