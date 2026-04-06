<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class BirthdayCelebration extends Widget
{
    protected static ?int $sort = 5; // After Reports Overview + Discussions, before Activity Feed
    protected static string $view = 'filament.widgets.birthday-celebration';

    protected int|string|array $columnSpan = [
        'sm' => 2,
        'md' => 6,
        'lg' => 6
    ];

    public static function canView(): bool
    {
        // Only show if there are birthdays today
        return self::getTodayBirthdays()->isNotEmpty();
    }

    public static function getTodayBirthdays()
    {
        $tz = auth()->user()?->timezone ?? config('app.timezone');
        $today = now()->setTimezone($tz);

        return User::whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get();
    }

    public function getViewData(): array
    {
        return [
            'birthdayUsers' => self::getTodayBirthdays(),
        ];
    }
}
