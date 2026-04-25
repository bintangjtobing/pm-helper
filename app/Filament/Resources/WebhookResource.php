<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookResource\Pages;
use App\Models\UserWebhook;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class WebhookResource extends Resource
{
    protected static ?string $model = UserWebhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-lightning-bolt';

    protected static ?string $slug = 'webhooks';

    protected static ?int $navigationSort = 99;

    protected static ?string $modelLabel = 'Webhook';

    protected static ?string $pluralModelLabel = 'Webhooks';

    protected static function getNavigationLabel(): string
    {
        return __('Webhooks');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Admin');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\Placeholder::make('intro')
                    ->label('')
                    ->content(new HtmlString('
                        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-left:4px solid #3b82f6;border-radius:8px;padding:14px 16px;font-size:13px;line-height:1.6;color:#1e3a8a;">
                            <div style="font-weight:600;font-size:14px;margin-bottom:6px;color:#1d4ed8;">What is this?</div>
                            <p style="margin:0 0 8px 0;">
                                Webhooks let you push real-time notifications from PM Helper to your own automation tools
                                (n8n, Zapier, Make, custom scripts, etc.) the moment a ticket event happens — no polling required.
                            </p>
                            <div style="font-weight:600;margin-top:10px;margin-bottom:4px;color:#1d4ed8;">Currently supported event</div>
                            <ul style="margin:0;padding-left:18px;">
                                <li><strong>QA Failed</strong> — fires when a ticket assigned to <em>you</em> transitions to status <code style="background:#dbeafe;padding:1px 5px;border-radius:3px;">QA Failed (status_id&nbsp;=&nbsp;7)</code>.</li>
                            </ul>
                            <div style="font-weight:600;margin-top:10px;margin-bottom:4px;color:#1d4ed8;">How it works</div>
                            <ol style="margin:0;padding-left:18px;">
                                <li>Save your webhook URL below (e.g. an n8n trigger URL exposed via Cloudflare Tunnel).</li>
                                <li>Optionally narrow down which projects should fire by selecting them in <em>Project filter</em>.</li>
                                <li>Optionally set a <em>Secret</em> — it will be sent as the <code style="background:#dbeafe;padding:1px 5px;border-radius:3px;">X-Webhook-Secret</code> header so your endpoint can verify the request is from PM Helper.</li>
                                <li>Hit <em>Test fire</em> after saving to verify your endpoint accepts the payload.</li>
                            </ol>
                            <div style="font-weight:600;margin-top:10px;margin-bottom:4px;color:#1d4ed8;">Delivery semantics</div>
                            <ul style="margin:0;padding-left:18px;">
                                <li>Fire-and-forget: a slow or failing endpoint will not block the user changing the ticket status.</li>
                                <li>2-second timeout. Failures are logged below in <em>Last status / Last error</em> — there is no automatic retry.</li>
                                <li>Each user owns their own webhook. Other users cannot see, edit, or trigger yours.</li>
                            </ul>
                            <div style="font-weight:600;margin-top:10px;margin-bottom:4px;color:#1d4ed8;">Sample payload</div>
                            <pre style="margin:0;background:#1e293b;color:#e2e8f0;padding:10px 12px;border-radius:6px;font-size:11.5px;overflow:auto;">{
  "event": "ticket.status_changed",
  "new_status_id": 7,
  "old_status_id": 6,
  "ticket": {
    "id": 142,
    "code": "QOS-68",
    "name": "Creative Assets pagination broken",
    "project_id": 1,
    "responsible_id": 2,
    "owner_id": 4,
    "updated_at": "2026-04-26T10:23:45+07:00",
    "updated_by": 4
  },
  "timestamp": "2026-04-26T10:23:45+07:00"
}</pre>
                        </div>
                    ')),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),

                Forms\Components\Select::make('event')
                    ->label('Event')
                    ->options([
                        UserWebhook::EVENT_QA_FAILED => 'QA Failed (ticket → status_id 7)',
                    ])
                    ->default(UserWebhook::EVENT_QA_FAILED)
                    ->required()
                    ->disablePlaceholderSelection(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Turn off to temporarily stop firing without deleting the webhook.'),

                Forms\Components\TextInput::make('url')
                    ->label('Webhook URL')
                    ->url()
                    ->required()
                    ->maxLength(1024)
                    ->placeholder('https://your-tunnel.example.com/webhook/qa-failed')
                    ->helperText('Full HTTPS URL. PM Helper will POST a JSON payload here within 1 second of the event.'),

                Forms\Components\TextInput::make('secret')
                    ->label('Shared secret (optional)')
                    ->password()
                    ->maxLength(255)
                    ->helperText('Sent as the X-Webhook-Secret header. Use it on your endpoint to verify the request is genuine.'),

                Forms\Components\Select::make('project_ids')
                    ->label('Project filter (optional)')
                    ->multiple()
                    ->options(function () {
                        $user = auth()->user();
                        if (! $user) {
                            return [];
                        }
                        $owned = $user->ownedProjects()->pluck('projects.name', 'projects.id');
                        $member = $user->projects()->pluck('projects.name', 'projects.id');
                        return $owned->union($member)->toArray();
                    })
                    ->placeholder('Leave empty to fire for all your projects')
                    ->helperText('Pick specific projects to narrow down. Empty = fire for any project where you are the responsible person.'),
            ]),

            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\Placeholder::make('last_fired_at_view')
                        ->label('Last fired at')
                        ->content(fn ($record) => $record?->last_fired_at?->diffForHumans() ?? '— never fired —'),

                    Forms\Components\Placeholder::make('last_status_view')
                        ->label('Last HTTP status')
                        ->content(fn ($record) => $record?->last_status ?? '—'),

                    Forms\Components\Placeholder::make('last_error_view')
                        ->label('Last error')
                        ->content(fn ($record) => $record?->last_error ? new HtmlString('<code style="font-size:11.5px;color:#b91c1c;">' . e($record->last_error) . '</code>') : '—'),
                ])
                ->columns(3)
                ->visible(fn ($record) => $record !== null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->label('Event')
                    ->formatStateUsing(fn ($state) => $state === UserWebhook::EVENT_QA_FAILED ? 'QA Failed' : $state),

                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->url),

                Tables\Columns\TextColumn::make('last_fired_at')
                    ->label('Last fired')
                    ->dateTime('Y-m-d H:i')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i') : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ?: '—'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'edit' => Pages\EditWebhook::route('/{record}/edit'),
        ];
    }
}
