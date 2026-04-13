<?php

use App\Jobs\CalculateBinaryCommissionsJob;
use App\Jobs\ProcessResidualCommissionsJob;
use App\Services\PeriodService;
use App\Services\RankService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mlm:close-binary {periodKey?}', function (?string $periodKey) {
    $key = $periodKey;
    if ($key === null || $key === '') {
        $key = config('mlm.binary.volume_period', 'monthly') === 'weekly'
            ? now()->subWeek()->format('o-\WW')
            : now()->subMonth()->format('Y-m');
    }
    CalculateBinaryCommissionsJob::dispatchSync($key);
    $this->info("Cierre binario ejecutado (sync) para {$key}");
})->purpose('Cierre binario (pierna débil 21 % + carry): mes Y-m por defecto, o semana ISO si MLM_BINARY_VOLUME_PERIOD=weekly');

Artisan::command('mlm:close-weekly-binary {weekKey?}', function (?string $weekKey) {
    $key = $weekKey ?? now()->subWeek()->format('o-\WW');
    CalculateBinaryCommissionsJob::dispatchSync($key);
    $this->info("Cierre binario (semana ISO) ejecutado para {$key}");
})->purpose('Alias retrocompatible: cierre binario semanal ISO');

Artisan::command('mlm:close-monthly-residual {monthKey?}', function (?string $monthKey) {
    $key = $monthKey ?? now()->subMonth()->format('Y-m');
    ProcessResidualCommissionsJob::dispatchSync($key);
    $this->info("Cierre mensual (auditoría residual) {$key}");
})->purpose('Cierre mensual / auditoría residual');

Artisan::command('mlm:accounting-period {action} {type} {periodKey}', function (string $action, string $type, string $periodKey) {
    $period = app(PeriodService::class);
    if ($action === 'close') {
        $period->cerrar($type, $periodKey, ['by' => 'cli']);
        $this->info("Periodo {$type} {$periodKey} cerrado.");
    } elseif ($action === 'open') {
        $period->abrir($type, $periodKey);
        $this->info("Periodo {$type} {$periodKey} abierto.");
    } else {
        $this->error('Acción: close | open');

        return 1;
    }

    return 0;
})->purpose('Cierra/abre periodos contables MLM (bloquea reproceso de comisiones)');

Artisan::command('mlm:reevaluate-ranks', function () {
    $n = app(RankService::class)->reevaluarTodosLosRangos();
    $this->info("Usuarios con cambio de rango: {$n}");
})->purpose('Re-evalúa rangos por PV mensual (batch)');
