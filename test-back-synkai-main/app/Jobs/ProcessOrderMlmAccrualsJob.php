<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Services\BinaryService;
use App\Services\CommissionEngine;
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
        CommissionEngine $commissionEngine,
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

        User::query()->whereKey($order->user_id)->update(['last_mlm_activity_at' => now()]);

        $buyer = $order->user;
        if ($buyer && $buyer->isPreferredCustomer()) {
            $order->loadMissing(['items.product', 'user']);
            $commissionService->acreditarBonosVentaDirectaPreferente($order->fresh(['items.product', 'user']));

            return;
        }
        foreach ($order->items as $item) {
            if ($item->package_id && $buyer->activation_paid_at === null && ! $buyer->canAccessAdminPanel()) {
                $buyer->forceFill(['activation_paid_at' => now()])->save();
                break;
            }
        }

        $buyer = $buyer->fresh();
        if (! $buyer->canAccessAdminPanel()) {
            $binaryService->placeUserInFirstFreeSlot($buyer);
            $buyer = $buyer->fresh();
        }

        $commissionEngine->process($order);
        if ($buyer->binaryPlacement()->exists()) {
            $binaryService->acumularVolumenBinarioPorPedido($order);
        }
        $qualificationService->actualizarCalificacionMensual($order->user->fresh());
    }
}
