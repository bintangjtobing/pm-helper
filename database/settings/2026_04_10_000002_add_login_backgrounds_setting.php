<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddLoginBackgroundsSetting extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.login_backgrounds', []);
    }
}
