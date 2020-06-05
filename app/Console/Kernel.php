<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by the kernel.
     *
     * @var array
     */
    protected $commands = [
        Commands\RemindOverdueTasks::class,
        Commands\RemindChecklistDue::class,
        Commands\RenewPlannedAudits::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //live configuration
        $schedule->command('task:checklistdue')->dailyAt('01:00');
        $schedule->command('task:taskoverdue')->dailyAt('02:00');
        $schedule->command('task:plannedaudits')->dailyAt('03:00');
    }
}
