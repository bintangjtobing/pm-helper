<?php

namespace App\Filament\Widgets;

use App\Models\MotivationalQuote;
use Filament\Widgets\Widget;

class GreetingWidget extends Widget
{
    protected static ?int $sort = -1;
    protected static string $view = 'filament.widgets.greeting';

    protected int|string|array $columnSpan = [
        'sm' => 2,
        'md' => 6,
        'lg' => 6
    ];

    public function getViewData(): array
    {
        $user = auth()->user();
        $tz = $user->timezone ?: config('app.timezone', 'UTC');
        $hour = now($tz)->hour;

        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Good Morning';
        } elseif ($hour >= 12 && $hour < 17) {
            $greeting = 'Good Afternoon';
        } elseif ($hour >= 17 && $hour < 21) {
            $greeting = 'Good Evening';
        } else {
            $greeting = 'Good Night';
        }

        $quote = MotivationalQuote::random();

        return [
            'greeting' => $greeting,
            'userName' => $user->name,
            'quote' => $quote,
        ];
    }
}
