<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\User;
use Filament\Pages\Page;

class OrganizationTree extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Organization';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'organization';

    protected static string $view = 'filament.pages.organization-tree';

    protected static function getNavigationGroup(): ?string
    {
        return __('Team');
    }

    public function getViewData(): array
    {
        $departments = Department::with(['positions.users', 'users'])
            ->orderBy('sort_order')
            ->get();

        $totalUsers = User::count();

        return [
            'departments' => $departments,
            'totalUsers' => $totalUsers,
        ];
    }
}
