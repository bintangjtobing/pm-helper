<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Changelog extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $slug = 'changelog';

    protected static string $view = 'filament.pages.changelog';

    protected static ?int $navigationSort = 99;

    protected static function getNavigationLabel(): string
    {
        return __('Changelog');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Admin');
    }

    public function getTitle(): string
    {
        return __("What's new");
    }

    public function getViewData(): array
    {
        return [
            'entries' => config('changelog', []),
            'currentVersion' => config('changelog.0.version', '1.0.0'),
        ];
    }
}
