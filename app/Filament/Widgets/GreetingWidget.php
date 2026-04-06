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
        $hour = now()->hour;

        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Good Morning';
            $emoji = '☀️';
        } elseif ($hour >= 12 && $hour < 15) {
            $greeting = 'Good Afternoon';
            $emoji = '🌤️';
        } elseif ($hour >= 15 && $hour < 18) {
            $greeting = 'Good Evening';
            $emoji = '🌅';
        } else {
            $greeting = 'Good Night';
            $emoji = '🌙';
        }

        $quote = MotivationalQuote::random();

        return [
            'greeting' => $greeting,
            'emoji' => $emoji,
            'userName' => auth()->user()->name,
            'quote' => $quote,
        ];
    }
}
