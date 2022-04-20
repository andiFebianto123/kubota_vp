<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Configuration;
use Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SendMailVendor::class,
        Commands\ReminderPo::class,
        Commands\SendMailVendorRealTime::class,
        Commands\SendMailRevisionPoRealTime::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('vendor:daily')->dailyAt('12:15');
        // $schedule->command('vendor:daily')->dailyAt('18:15');
        $schedule->command('vendor:realtime')->cron('* * * * *');
        $schedule->command('vendor:realtime-revision-po')->cron('* * * * *');
        $schedule->command('reminder:po_line')->dailyAt("05:30");

        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('01:30');
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
