<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\BinaryService;
use App\Services\CommissionService;
use App\Services\UserQualificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Encola el cálculo BIR, residual por pedido y acumulación binaria (idempotente).
 */
class ProcessOrderMlmAccrualsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId)
    {
        $this->onQueue(config('mlm.queues.binary', 'default'));
    }

    public function handle(
        CommissionService $commissionService,
        BinaryService $binaryService,
        UserQualificationService $qualificationService
    ): void {
        $order = Order::query()
            ->with(['items.package', 'items.product', 'user.rank'])
            ->find($this->orderId);

        if (! $order || $order->estado !== 'completado') {
            return;
        }

        $buyer = $order->user;
        foreach ($order->items as $item) {
            if ($item->package_id && $buyer->activation_paid_at === null && ! $buyer->canAccessAdminPanel()) {
                $buyer->forceFill(['activation_paid_at' => now()])->save();
                break;
            }
        }

        $buyer = $buyer->fresh();

        $commissionService->calcularBonoInicioRapido($order);
        $commissionService->calcularResidualPorPedido($order);
        if ($buyer->binaryPlacement()->exists()) {
            $binaryService->acumularVolumenBinarioPorPedido($order);
        }
        $qualificationService->actualizarCalificacionMensual($order->user->fresh());
    }
}
