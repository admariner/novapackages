<?php

namespace App\Console;

use App\Console\Commands\CheckPackageUrls;
use App\Console\Commands\DeleteAbandonedScreenshots;
use App\Console\Commands\SyncPackagistData;
use App\Console\Commands\SyncRepositoryData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        SyncPackagistData::class,
        DeleteAbandonedScreenshots::class,
        SyncRepositoryData::class,
        CheckPackageUrls::class
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sync:packagist')->everyTwoHours();
        // Every two hours at minute 30.
        $schedule->command('sync:repo')->cron('30 */2 * * *');
        $schedule->command('purge:abandonedscreenshots')->dailyAt('1:00');
        $schedule->command('telescope:prune')->daily();

        // $schedule->command('novapackages:check-package-urls')->weeklyOn(7, '20:00');
        // $schedule->command('novapackages:send-unavailable-package-followup')->dailyAt('21:00');
        // $schedule->command('novapackages:disable-unavailable-packages')->dailyAt('21:30');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
