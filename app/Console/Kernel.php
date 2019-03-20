<?php

namespace App\Console;

use App\Console\Commands\DropAllTables;
use App\Console\Commands\FetchExchangeRates;
use App\Console\Commands\GenerateReportsBaccaratBets;
use App\Console\Commands\GenerateReportsDealerShifts;
use App\Console\Commands\GenerateReportsRouletteBets;
use App\Console\Commands\GenerateReportsUserFinances;
use App\Console\Commands\GenerateReportsUserFinancesForPeriod;
use App\Console\Commands\RefreshUserBonusBalances;
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
        DropAllTables::class,
        FetchExchangeRates::class,
        GenerateReportsBaccaratBets::class,
        GenerateReportsRouletteBets::class,
        GenerateReportsDealerShifts::class,
        GenerateReportsUserFinances::class,
        GenerateReportsUserFinancesForPeriod::class,
        RefreshUserBonusBalances::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('reportsBaccaratBets:generate')->timezone('Asia/Bishkek')->between('00:00', '00:59');
        $schedule->command('reportsRouletteBets:generate')->timezone('Asia/Bishkek')->between('00:00', '00:59');
        $schedule->command('reportsDealerShifts:generate')->timezone('Asia/Bishkek')->between('00:00', '00:59');
        $schedule->command('reportsUserFinances:generate')->timezone('Asia/Bishkek')->between('00:00', '00:59');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
