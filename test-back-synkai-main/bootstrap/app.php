<?php

use App\Jobs\CalculateBinaryCommissionsJob;
use App\Jobs\ProcessResidualCommissionsJob;
use App\Services\RankService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use App\Http\Middleware\EnsureMlmRole;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->append(HandleCors::class);
        $middleware->alias([
            'mlm.admin' => EnsureMlmRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        if (config('mlm.binary.volume_period', 'monthly') === 'weekly') {
            $schedule->call(function () {
                $weekKey = now()->subWeek()->format('o-\WW');
                CalculateBinaryCommissionsJob::dispatch($weekKey);
            })->weekly()->sundays()->at('03:00');
        } else {
            $schedule->call(function () {
                $monthKey = now()->subMonth()->format('Y-m');
                CalculateBinaryCommissionsJob::dispatch($monthKey);
            })->monthlyOn(1, '03:00');
        }

        $schedule->call(function () {
            $monthKey = now()->subMonth()->format('Y-m');
            ProcessResidualCommissionsJob::dispatch($monthKey);
        })->monthlyOn(1, '04:00');

        $schedule->call(function () {
            app(RankService::class)->reevaluarTodosLosRangos();
        })->monthlyOn(2, '05:00');

        $schedule->command('mlm:purge-inactive-members')->monthlyOn(7, '06:00');
    })
    ->create();
