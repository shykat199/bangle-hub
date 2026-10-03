<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Queued jobs (ManyDial confirmation calls, notifications) only run
        // when a worker drains the `jobs` table. Shared hosting has no daemon,
        // so drain it from the scheduler instead: each run empties the queue
        // and exits, and withoutOverlapping keeps a single worker at a time.
        // Requires this cron on the server:
        //   * * * * * cd /home/USER/public_html && php artisan schedule:run >> /dev/null 2>&1
        $schedule->command('queue:work --stop-when-empty --tries=1 --max-time=55')
            ->everyMinute()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
