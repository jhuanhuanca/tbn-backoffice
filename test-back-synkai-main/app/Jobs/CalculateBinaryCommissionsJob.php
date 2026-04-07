<?php

namespace App\Jobs;

use App\Services\BinaryService;
use App\Services\CommissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Cierre semanal del binario: paga pierna débil y persiste carry.
 */
class CalculateBinaryCommissionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $weekKey)
    {
        $this->onQueue(config('mlm.queues.binary', 'default'));
    }

    public function handle(BinaryService $binaryService, CommissionService $commissionService): void
    {
        $binaryService->procesarCierreSemanal($this->weekKey, $commissionService);
    }
}
