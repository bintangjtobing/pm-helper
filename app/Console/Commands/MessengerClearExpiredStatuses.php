<?php

namespace App\Console\Commands;

use App\Services\MessengerService;
use Illuminate\Console\Command;

class MessengerClearExpiredStatuses extends Command
{
    protected $signature = 'messenger:clear-expired-statuses';

    protected $description = 'Sweep expired manual statuses (in_meeting > status_until, on_leave > on_leave_until)';

    public function handle(MessengerService $messenger): int
    {
        $cleared = $messenger->clearExpiredStatuses();
        $this->info("Cleared {$cleared} expired status(es).");
        return self::SUCCESS;
    }
}
