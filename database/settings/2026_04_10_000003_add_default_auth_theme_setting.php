<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddDefaultAuthThemeSetting extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.default_auth_theme', 'dark');
    }
}
