<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DirectMessages extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-alt-2';

    protected static ?string $slug = 'dm';

    protected static string $view = 'filament.pages.direct-messages';

    protected static ?int $navigationSort = 1;

    protected ?string $maxContentWidth = 'full';

    protected static function getNavigationLabel(): string
    {
        return __('Messenger');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Team');
    }

    public function getTitle(): string
    {
        return __('Messenger');
    }

    protected function getHeading(): string
    {
        return '';
    }
}
