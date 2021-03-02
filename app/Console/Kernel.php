<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\EveryFiveMinutes\GetStatistics::class,
        Commands\EveryFiveMinutes\Streamate::class,
        Commands\EveryFiveMinutes\Cams::class,
        Commands\EveryFiveMinutes\Imlive::class,
        Commands\EveryFiveMinutes\Firecams::class,
        Commands\EverySunday\attendance::class,
        Commands\EveryDay\Alarms::class,
        Commands\EveryDay\Cafeteria::class,
        Commands\EveryHalfHour\CloseInactivatedUserTasks::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $this->runEveryFiveMinutes($schedule);
        $schedule->command('get:statistics')->everyFiveMinutes();
        $schedule->command('alarm:show')->daily();
        $schedule->command('cafeteria:tasks')->daily();
//        $schedule->command('update:profile')->daily();
        $schedule->command('create:attendance')->sundays()->at('00:00');
        $schedule->command('close-inactivate-users:tasks')->everyThirtyMinutes();
    }

    protected function runEveryFiveMinutes($schedule)
    {
        $schedule->command('get:statistics')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
