<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurgeOldNotifications extends Command
{
    protected $signature = 'notifications:purge-old';

    protected $description = 'Delete database notifications older than 7 days';

    public function handle()
    {
        $threshold = Carbon::now()->subDays(7)->toDateTimeString();

        $deleted = DB::table('notifications')->where('created_at', '<', $threshold)->delete();

        $this->info("Purged {$deleted} old notifications older than 7 days.");

        return 0;
    }
}
