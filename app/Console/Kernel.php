<?php

namespace App\Console;

use App\Jobs\DestroyAfkLoginUserCache;
use App\Jobs\DestroyUnusedResetPasswordToken;
use App\Jobs\ExpiredOtpAutoDelelete;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        //daily delete expired otp
        $schedule->job(new ExpiredOtpAutoDelelete())->everyTwoHours();
        $schedule->job(new DestroyAfkLoginUserCache())->hourly();
        $schedule->job(new DestroyUnusedResetPasswordToken())->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
