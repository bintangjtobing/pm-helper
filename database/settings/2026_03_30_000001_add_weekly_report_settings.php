<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddWeeklyReportSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.report_due_day', 'friday');
        $this->migrator->add('general.report_reminder_enabled', true);
    }
}
