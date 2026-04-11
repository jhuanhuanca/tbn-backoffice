<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderCommissionRun;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

/**
 * Orquestación central: periodo abierto, snapshot financiero por línea, BIR + residual, marca de ejecución idempotente.
 */
class CommissionEngine
{
    public function __construct(
        protected PeriodService $periodService,
        protected CommissionService $commissionService
    ) {}

    public function process(Order $order): void
    {
        $order->loadMissing(['items.package', 'items.product', 'user']);

        DB::transaction(function () use ($order) {
            Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (OrderCommissionRun::query()->where('order_id', $order->id)->exists()) {
                return;
            }

            $this->periodService->asegurarPeriodoAbiertoParaOrden($order);
            $this->sincronizarLineasComisionables($order);

            $order->refresh()->load(['items.package', 'items.product', 'user']);
            $this->commissionService->procesarAcreditacionesPorPedido($order);

            OrderCommissionRun::query()->create([
                'order_id' => $order->id,
                'unique_hash' => hash('sha256', 'order_commissions:'.$order->id),
                'processed_at' => now(),
                'engine_version' => '1',
                'meta' => [
                    'order_uuid' => $order->uuid,
                ],
            ]);
        });
    }

    protected function sincronizarLineasComisionables(Order $order): void
    {
        $currency = config('mlm.currency', 'BOB');

        foreach ($order->items as $item) {
            if (! $item->package_id || ! $item->package) {
                continue;
            }

            $qty = (string) max(1, (int) $item->cantidad);
            $puPv = (string) $item->package->pv_points;
            $commPv = bcmul($puPv, $qty, 4);
            $commAmt = bcmul($item->package->commissionableValue(), $qty, 4);

            OrderItem::query()->whereKey($item->id)->update([
                'commissionable_pv' => bcadd($commPv, '0', 4),
                'commissionable_amount' => bcadd($commAmt, '0', 4),
                'line_currency' => $currency,
                'fx_rate_to_wallet' => '1',
            ]);
        }
    }
}
