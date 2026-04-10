<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddSiteFaviconSetting extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_favicon', null);
    }
}
