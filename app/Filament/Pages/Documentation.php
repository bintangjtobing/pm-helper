<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Documentation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Documentation';

    protected static ?int $navigationSort = 99;

    protected static ?string $slug = 'docs';

    protected static string $view = 'filament.pages.documentation';

    protected static function getNavigationGroup(): ?string
    {
        return null; // Top-level, no group
    }

    protected function getTitle(): string
    {
        return __('Documentation & Help Center');
    }
}
