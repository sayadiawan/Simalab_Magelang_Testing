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
        \App\Console\Commands\FixSpesimenJump6000::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        
        

        $schedule->command('mqtt:cleanup-subscribe-log')
                 ->daily()
                 ->at('00:15')
                 ->withoutOverlapping();

        // Daily backup at 2 AM
        $schedule->command('backup:create')
                 ->daily()
                 ->at('02:00')
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Cleanup old backups weekly on Sunday at 3 AM
        $schedule->command('backup:cleanup')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->withoutOverlapping();
        
        // Cleanup old Google Drive backups weekly on Sunday at 3:30 AM
        // $schedule->command('backup:google-cleanup')
        //          ->weekly()
        //          ->sundays()
        //          ->at('03:30')
        //          ->withoutOverlapping();
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