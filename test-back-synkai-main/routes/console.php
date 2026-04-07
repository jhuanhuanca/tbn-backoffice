<?php

use App\Jobs\CalculateBinaryCommissionsJob;
use App\Jobs\ProcessResidualCommissionsJob;
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
