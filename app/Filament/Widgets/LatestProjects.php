<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class LatestProjects extends BaseWidget
{
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];

    public function mount(): void
    {
        self::$heading = __('Latest projects');
    }

    public static function canView(): bool
    {
        return auth()->user()->can('List projects');
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableQuery(): Builder
    {
        return Project::query()
            ->limit(5)
            ->where(function ($query) {
                return $query->where('owner_id', auth()->user()->id)
                    ->orWhereHas('users', function ($query) {
                        return $query->where('users.id', auth()->user()->id);
                    });
            })
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('Project'))
                ->formatStateUsing(function ($record) {
                    $cover = $record->getFirstMediaUrl('cover');
                    $initials = strtoupper(substr($record->name, 0, 2));
                    $bg = substr(md5($record->name), 0, 6);
                    $avatar = $cover
                        ? '<img src="' . e($cover) . '" class="w-9 h-9 rounded-lg object-cover shrink-0" loading="lazy" />'
                        : '<div class="flex items-center justify-center w-9 h-9 text-xs font-bold text-white rounded-lg shrink-0" style="background:linear-gradient(135deg,#' . $bg . ',#' . substr(md5($record->name . 'x'), 0, 6) . ')">' . $initials . '</div>';

                    $ownerAvatar = '';
                    if ($record->owner) {
                        $ownerUrl = $record->owner->getAttributes()['avatar_url']
                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($record->owner->name) . '&size=64&background=' . substr(md5($record->owner->id), 0, 6) . '&color=ffffff');
                        $ownerAvatar = '<img src="' . e($ownerUrl) . '" class="w-4 h-4 rounded-full" loading="lazy" />';
                    }

                    return new HtmlString('
                        <div class="flex items-center gap-3">
                            ' . $avatar . '
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">' . e($record->name) . '</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    ' . $ownerAvatar . '
                                    <span class="text-xs text-gray-500 dark:text-gray-400">' . e($record->owner?->name) . '</span>
                                </div>
                            </div>
                        </div>
                    ');
                }),

            Tables\Columns\TextColumn::make('status.name')
                ->label(__('Status'))
                ->formatStateUsing(fn($record) => new HtmlString(
                    '<span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-medium rounded-md" style="background-color: ' . $record->status->color . '20; color: ' . $record->status->color . '">'
                    . '<span class="w-1.5 h-1.5 rounded-full" style="background-color: ' . $record->status->color . '"></span>'
                    . e($record->status->name)
                    . '</span>'
                )),
        ];
    }
}
