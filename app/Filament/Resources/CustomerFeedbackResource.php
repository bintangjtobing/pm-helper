<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerFeedbackResource\Pages;
use App\Models\CustomerFeedback;
use App\Models\CustomerFeedbackActivity;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\TicketPriority;
use App\Models\User;
use App\Notifications\FeedbackConverted;
use App\Notifications\FeedbackUpdated;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class CustomerFeedbackResource extends Resource
{
    protected static ?string $model = CustomerFeedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-annotation';

    protected static ?int $navigationSort = 4;

    // PERBAIKAN: Set slug yang benar
    protected static ?string $slug = 'customer-feedbacks';

    protected static function getNavigationLabel(): string
    {
        return __('Customer Feedback');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Team');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Debug log
        \Log::info('CustomerFeedback Navigation Check', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'roles' => $user->roles->pluck('name'),
            'can_list' => $user->can('List customer feedbacks'),
            'has_role' => $user->hasRole(['Super Admin', 'Admin', 'Client'])
        ]);

        return $user->hasRole(['Super Admin', 'Admin', 'Client','Default role']) &&
               $user->can('List customer feedbacks');
    }
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole(['Super Admin', 'Admin', 'Client']);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        // Jika user adalah Client, hanya bisa lihat feedback dari project yang dia terlibat
        if ($user->hasRole('Client')) {
            // Ambil project IDs dimana user adalah:
            // 1. Owner project
            // 2. Attached user di project (melalui pivot table project_users)
            $ownedProjectIds = Project::where('owner_id', $user->id)->pluck('id');
            $attachedProjectIds = $user->projects()->pluck('projects.id');
            $accessibleProjectIds = $ownedProjectIds->merge($attachedProjectIds)->unique();

            $query->where('user_id', $user->id)
                  ->whereIn('project_id', $accessibleProjectIds);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('project_id')
                                    ->label(__('Project'))
                                    ->searchable()
                                    ->required()
                                    ->options(function () {
                                        $user = auth()->user();
                                        if ($user->hasRole(['Super Admin', 'Admin'])) {
                                            return Project::all()->pluck('name', 'id');
                                        }
                                        // Untuk Client, hanya project yang dia terlibat:
                                        // 1. Project yang dia own
                                        // 2. Project yang dia attached sebagai user
                                        $ownedProjectIds = Project::where('owner_id', $user->id)->pluck('id');
                                        $attachedProjectIds = $user->projects()->pluck('projects.id');
                                        $accessibleProjectIds = $ownedProjectIds->merge($attachedProjectIds)->unique();

                                        return Project::whereIn('id', $accessibleProjectIds)->pluck('name', 'id');
                                    })
                                    ->disabled(fn ($record) => $record !== null), // Disable edit jika sudah ada

                                Forms\Components\Hidden::make('user_id')
                                    ->default(auth()->id()),

                                Forms\Components\TextInput::make('title')
                                    ->label(__('Feedback Title'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('description')
                                    ->label(__('Feedback Description'))
                                    ->required()
                                    ->rows(4),

                                Forms\Components\Select::make('status')
                                    ->label(__('Status'))
                                    ->options([
                                        'pending' => 'Pending',
                                        'converted_to_ticket' => 'Converted to Ticket',
                                        'rejected' => 'Rejected',
                                    ])
                                    ->default('pending')
                                    ->visible(fn () => auth()->user()->hasRole(['Super Admin', 'Admin']))
                                    ->disabled(fn ($record) => $record?->status === 'converted_to_ticket'),

                                Forms\Components\Select::make('converted_ticket_id')
                                    ->label(__('Converted Ticket'))
                                    ->searchable()
                                    ->options(fn () => Ticket::all()->pluck('name', 'id'))
                                    ->visible(fn ($get) => $get('status') === 'converted_to_ticket')
                                    ->disabled(),
                            ]),
                    ]),

                // Proposed Changes Preview
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('proposed_changes_preview')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record || !$record->change_type || !$record->proposed_data) return '';

                                $type = $record->change_type === 'project_description'
                                    ? __('Project Description')
                                    : __('Project Goals & Requirements');
                                $data = $record->proposed_data;

                                $html = '<div style="margin-bottom:16px;">'
                                    . '<h3 style="font-size:16px;font-weight:600;margin-bottom:4px;">'
                                    . __('Proposed Changes') . ': ' . e($type) . '</h3>'
                                    . '<p style="font-size:12px;color:#9ca3af;">Submitted by stakeholder for approval</p>'
                                    . '</div>';

                                $html .= '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">';

                                // Current value
                                $html .= '<div>'
                                    . '<div style="font-size:12px;font-weight:600;color:#ef4444;margin-bottom:6px;">' . __('Current') . '</div>'
                                    . '<div style="padding:12px;background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.2);border-radius:8px;font-size:13px;max-height:300px;overflow-y:auto;">'
                                    . ($data['old_value'] ?: '<em style="color:#6b7280;">Empty</em>')
                                    . '</div></div>';

                                // Proposed value
                                $html .= '<div>'
                                    . '<div style="font-size:12px;font-weight:600;color:#22c55e;margin-bottom:6px;">' . __('Proposed') . '</div>'
                                    . '<div style="padding:12px;background:rgba(34,197,94,0.05);border:1px solid rgba(34,197,94,0.2);border-radius:8px;font-size:13px;max-height:300px;overflow-y:auto;">'
                                    . ($data['new_value'] ?: '<em style="color:#6b7280;">Empty</em>')
                                    . '</div></div>';

                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                    ])
                    ->visible(fn ($record) => $record?->change_type && $record?->proposed_data),

                // Attachments Card
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('attachments_list')
                            ->label(__('Attachments'))
                            ->content(function ($record) {
                                if (!$record) return '';
                                $items = $record->attachments()->orderBy('id')->get();
                                if ($items->isEmpty()) {
                                    return new HtmlString('<div style="font-size:13px;color:#9ca3af;">No attachments.</div>');
                                }

                                $html = '<div style="display:flex;flex-direction:column;gap:8px;">';
                                foreach ($items as $att) {
                                    $isPdf = $att->isPdf();
                                    $badge = $isPdf ? 'PDF' : 'DOCX';
                                    $bgColor = $isPdf ? '#dc2626' : '#2563eb';

                                    $html .= '<a href="' . e($att->url) . '" target="_blank" rel="noopener" '
                                        . 'style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fafafa;border:1px solid #e4e4e7;border-radius:8px;text-decoration:none;color:#3f3f46;transition:all 140ms;">';
                                    $html .= '<div style="flex-shrink:0;width:32px;height:32px;border-radius:5px;background:' . $bgColor . ';color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;letter-spacing:0.5px;">' . $badge . '</div>';
                                    $html .= '<div style="flex:1;min-width:0;">';
                                    $html .= '<div style="font-size:13px;font-weight:500;color:#18181b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' . e($att->filename_original) . '</div>';
                                    $html .= '<div style="font-size:11px;color:#a1a1aa;">' . e($att->human_size) . ' · ' . $att->created_at->diffForHumans() . '</div>';
                                    $html .= '</div>';
                                    $html .= '<div style="flex-shrink:0;font-size:11px;color:#3b82f6;font-weight:600;">Download &rarr;</div>';
                                    $html .= '</a>';
                                }
                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                    ])
                    ->visible(fn ($record) => $record !== null && $record->attachments()->exists()),

                // Activity Log Card - Hanya tampil di edit/view
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('activities_log')
                            ->label(__('Activity Log'))
                            ->content(function ($record) {
                                if (!$record) return '';

                                $activities = $record->activities()->with('user')->latest()->get();
                                $html = '<div class="space-y-3">';

                                foreach ($activities as $activity) {
                                    $html .= '<div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">';
                                    $html .= '<div class="flex-1">';
                                    $html .= '<div class="font-medium text-sm">' . $activity->action_label . '</div>';
                                    $html .= '<div class="text-xs text-gray-500">by ' . $activity->user->name . ' • ' . $activity->created_at->diffForHumans() . '</div>';
                                    if ($activity->notes) {
                                        $html .= '<div class="text-sm text-gray-700 mt-1">' . $activity->notes . '</div>';
                                    }
                                    $html .= '</div></div>';
                                }

                                $html .= '</div>';
                                return new HtmlString($html);
                            })
                    ])
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.name')
                    ->label(__('Project'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Customer'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => new HtmlString('<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>'),
                        'converted_to_ticket' => new HtmlString('<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Converted</span>'),
                        'rejected' => new HtmlString('<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejected</span>'),
                        default => $state
                    })
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('change_type')
                    ->label(__('Type'))
                    ->enum([
                        'project_description' => 'Description Change',
                        'project_goals' => 'Goals Change',
                    ])
                    ->colors([
                        'primary' => fn ($state) => $state !== null,
                    ]),

                Tables\Columns\TextColumn::make('convertedTicket.code')
                    ->label(__('Ticket Code'))
                    ->sortable()
                    ->url(fn ($record) => $record->convertedTicket ?
                        route('filament.resources.tickets.view', $record->convertedTicket) : null)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'converted_to_ticket' => 'Converted to Ticket',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label(__('Project'))
                    ->options(fn () => Project::all()->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => auth()->user()->hasRole(['Super Admin', 'Admin']) ||
                        ($record->user_id === auth()->id() && $record->status === 'pending')),

                Tables\Actions\Action::make('convert_to_ticket')
                    ->label(__('Convert to Ticket'))
                    ->icon('heroicon-o-ticket')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending' && auth()->user()->hasRole(['Super Admin', 'Admin']))
                    ->form([
                        Forms\Components\TextInput::make('ticket_title')
                            ->label(__('Ticket Title'))
                            ->default(fn ($record) => $record->title)
                            ->required(),

                        Forms\Components\Textarea::make('ticket_description')
                            ->label(__('Ticket Description'))
                            ->default(fn ($record) => $record->description . "\n\n[From direct customer feedback]")
                            ->required(),

                        Forms\Components\Select::make('ticket_type_id')
                            ->label(__('Ticket Type'))
                            ->options(fn () => TicketType::all()->pluck('name', 'id'))
                            ->required(),

                        Forms\Components\Select::make('ticket_priority_id')
                            ->label(__('Ticket Priority'))
                            ->options(fn () => TicketPriority::all()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (CustomerFeedback $record, array $data) {
                        // Get backlog status
                        $backlogStatus = TicketStatus::where('name', 'LIKE', '%backlog%')
                            ->orWhere('name', 'LIKE', '%Backlog%')
                            ->first();

                        if (!$backlogStatus) {
                            $backlogStatus = TicketStatus::where('is_default', true)->first();
                        }

                        // Create ticket
                        $ticket = Ticket::create([
                            'name' => $data['ticket_title'],
                            'content' => $data['ticket_description'],
                            'project_id' => $record->project_id,
                            'owner_id' => auth()->id(),
                            'responsible_id' => $record->user_id,
                            'status_id' => $backlogStatus->id,
                            'type_id' => $data['ticket_type_id'],
                            'priority_id' => $data['ticket_priority_id'],
                        ]);

                        // Update feedback
                        $record->update([
                            'status' => 'converted_to_ticket',
                            'converted_ticket_id' => $ticket->id,
                        ]);

                        // Log activity
                        CustomerFeedbackActivity::create([
                            'feedback_id' => $record->id,
                            'user_id' => auth()->id(),
                            'action' => 'converted_to_ticket',
                            'notes' => "Converted to ticket: {$ticket->code}"
                        ]);

                        // Notify customer
                        $record->user->notify(new FeedbackConverted($record));

                        return redirect()->route('filament.resources.tickets.view', $ticket);
                    }),

                Tables\Actions\Action::make('apply_changes')
                    ->label(__('Apply Changes'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        $record->status === 'pending'
                        && $record->change_type
                        && $record->proposed_data
                        && auth()->user()->hasRole(['Super Admin', 'Admin', 'Project Manager'])
                    )
                    ->requiresConfirmation()
                    ->modalHeading(__('Apply Proposed Changes'))
                    ->modalSubheading(fn ($record) => __('This will update the project :field with the stakeholder\'s proposed changes.', [
                        'field' => $record->change_type === 'project_description' ? 'description' : 'goals'
                    ]))
                    ->action(function (CustomerFeedback $record) {
                        $project = $record->project;
                        $data = $record->proposed_data;

                        if (!$project || !$data) return;

                        $field = $data['field'] ?? null;
                        $newValue = $data['new_value'] ?? null;

                        if ($field && in_array($field, ['description', 'goals'])) {
                            $project->update([$field => $newValue]);

                            $record->update(['status' => 'converted_to_ticket']);

                            CustomerFeedbackActivity::create([
                                'feedback_id' => $record->id,
                                'user_id' => auth()->id(),
                                'action' => 'changes_applied',
                                'notes' => 'Proposed changes applied to project ' . $field,
                            ]);

                            $record->user->notify(new FeedbackUpdated(
                                $record,
                                'Your proposed changes to the project ' . $field . ' have been approved and applied.'
                            ));

                            Filament::notify('success', __('Changes applied to project successfully.'));
                        }
                    }),

                Tables\Actions\Action::make('reject_changes')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) =>
                        $record->status === 'pending'
                        && $record->change_type
                        && auth()->user()->hasRole(['Super Admin', 'Admin', 'Project Manager'])
                    )
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label(__('Reason for Rejection'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (CustomerFeedback $record, array $data) {
                        $record->update(['status' => 'rejected']);

                        CustomerFeedbackActivity::create([
                            'feedback_id' => $record->id,
                            'user_id' => auth()->id(),
                            'action' => 'rejected',
                            'notes' => $data['rejection_reason'],
                        ]);

                        $record->user->notify(new FeedbackUpdated($record, 'Rejected: ' . $data['rejection_reason']));
                        Filament::notify('success', __('Feedback rejected.'));
                    }),

                Tables\Actions\Action::make('add_note')
                    ->label(__('Add Note'))
                    ->icon('heroicon-o-annotation')
                    ->color('warning')
                    ->visible(fn () => auth()->user()->hasRole(['Super Admin', 'Admin']))
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label(__('Note'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (CustomerFeedback $record, array $data) {
                        CustomerFeedbackActivity::create([
                            'feedback_id' => $record->id,
                            'user_id' => auth()->id(),
                            'action' => 'noted',
                            'notes' => $data['note']
                        ]);

                        $record->user->notify(new FeedbackUpdated($record, $data['note']));
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['Super Admin', 'Admin'])),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\CustomerFeedbackResource\RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerFeedback::route('/'),
            'create' => Pages\CreateCustomerFeedback::route('/create'),
            'view' => Pages\ViewCustomerFeedback::route('/{record}'),
            'edit' => Pages\EditCustomerFeedback::route('/{record}/edit'),
        ];
    }
}
