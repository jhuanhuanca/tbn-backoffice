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

Artisan::command('mlm:close-weekly-binary {weekKey?}', function (?string $weekKey) {
    $key = $weekKey ?? now()->subWeek()->format('o-\WW');
    CalculateBinaryCommissionsJob::dispatchSync($key);
    $this->info("Cierre binario ejecutado (sync) para {$key}");
})->purpose('Cierre semanal del binario (pierna débil + carry)');

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
