<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
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
        // $schedule->command('command:crontab')->everyMinute()->runInBackground();
        // 5min 限制重置
        // $schedule->call(function () {
        //     DB::update('update user_records set count = 0;');
        // })->everyFiveMinutes()->runInBackground();
        // telescope 定期清理日志
        $hours = Config::get('telescope.expire_hours');
        if (!is_null($hours) && (int) $hours >= 0) {
            $schedule->command('telescope:prune', [
                '--hours' => $hours,
            ])->everyMinute();
        }
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
