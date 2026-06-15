<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── AutoPulse AI Engine — Cron Schedule ──
// cPanel-এ এই command যোগ করুন:
// * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

// প্রতি মিনিটে: Active rules চেক করো এবং GenerateContentJob dispatch করো
Schedule::command('autopulse:heartbeat')->everyMinute();

// প্রতি মিনিটে: DB Queue থেকে সব pending job process করো (Shared Hosting Safe)
Schedule::command('queue:work', [
    '--stop-when-empty',
    '--tries=3',
    '--max-time=50', // cPanel process limit এর আগে স্টপ করতে
    '--rest=1'       // CPU অপ্টিমাইজেশন
])->everyMinute()->withoutOverlapping();
