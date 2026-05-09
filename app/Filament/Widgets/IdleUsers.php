<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class IdleUsers extends Widget
{
    protected static ?int $sort = 8;
    protected static string $view = 'filament.widgets.idle-users';

    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3,
    ];

    public static function canView(): bool
    {
        return auth()->user()?->can('Update user') ?? false;
    }

    public function getViewData(): array
    {
        $threshold = now()->subDays(7);

        $idleUsers = User::query()
            ->with(['department', 'position'])
            ->where(function ($q) use ($threshold) {
                $q->whereNull('last_seen_at')
                  ->orWhere('last_seen_at', '<', $threshold);
            })
            ->where('id', '!=', auth()->id())
            ->orderByRaw('last_seen_at IS NULL')
            ->orderBy('last_seen_at')
            ->limit(8)
            ->get();

        $totalIdle = User::query()
            ->where(function ($q) use ($threshold) {
                $q->whereNull('last_seen_at')
                  ->orWhere('last_seen_at', '<', $threshold);
            })
            ->where('id', '!=', auth()->id())
            ->count();

        return [
            'idleUsers' => $idleUsers,
            'totalIdle' => $totalIdle,
        ];
    }
}
