<?php

namespace App\Filament\Widgets;

use App\Models\TicketComment;
use Filament\Forms\Components\RichEditor;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class LatestComments extends BaseWidget
{
    protected static ?int $sort = 8;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];

    public function mount(): void
    {
        self::$heading = __('Latest tickets comments');
    }

    public static function canView(): bool
    {
        return auth()->user()->can('List tickets');
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableQuery(): Builder
    {
        return TicketComment::query()
            ->limit(5)
            ->whereHas('ticket', function ($query) {
                return $query->where('owner_id', auth()->user()->id)
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhereHas('project', function ($query) {
                        return $query->where('owner_id', auth()->user()->id)
                            ->orWhereHas('users', function ($query) {
                                return $query->where('users.id', auth()->user()->id);
                            });
                    });
            })
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('ticket')
                ->label(__('Ticket'))
                ->formatStateUsing(function ($state) {
                    return new HtmlString('
                        <div class="flex flex-col gap-0.5">
                            <span class="px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded bg-primary-500/10 text-primary-500 self-start">'
                                . e($state->project->name) .
                            '</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-mono text-gray-400 dark:text-gray-500">' . e($state->code) . '</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">' . e($state->name) . '</span>
                            </div>
                        </div>
                    ');
                }),

            Tables\Columns\TextColumn::make('user.name')
                ->label(__('By'))
                ->formatStateUsing(function ($record) {
                    $user = $record->user;
                    if (!$user) return '-';
                    $avatar = $user->getAttributes()['avatar_url']
                        ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
                    return new HtmlString('
                        <div class="flex items-center gap-2">
                            <img src="' . e($avatar) . '" class="w-6 h-6 rounded-full" loading="lazy" />
                            <span class="text-xs text-gray-700 dark:text-gray-300">' . e($user->name) . '</span>
                        </div>
                    ');
                }),

            Tables\Columns\TextColumn::make('created_at')
                ->label(__('When'))
                ->since(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view')
                ->label(__('View'))
                ->icon('heroicon-s-eye')
                ->color('secondary')
                ->iconButton()
                ->modalHeading(__('Comment details'))
                ->modalButton(__('View ticket'))
                ->form([
                    RichEditor::make('content')
                        ->label(__('Content'))
                        ->default(fn($record) => $record->content)
                        ->disabled()
                ])
                ->action(
                    fn($record) =>
                        redirect()->to(route('filament.resources.tickets.share', $record->ticket->code))
                )
        ];
    }
}
