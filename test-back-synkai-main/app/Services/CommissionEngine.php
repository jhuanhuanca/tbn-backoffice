<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderCommissionRun;
use App\Models\OrderItem;
use App\Models\User;
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
            $this->acumularPvMensualYAcumulado($order);
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

    /**
     * PV “mensual” vs PV “acumulado” (lifetime).
     *
     * - monthly_qualifying_pv: base para calificación del mes y snapshots (bonos).
     * - lifetime_purchase_pv: PV acumulado por compras/paquetes (NO usado para rangos). Se usa para el modal de paquetes.
     *
     * Se aplica dentro de la misma transacción idempotente del motor (solo una vez por pedido).
     */
    protected function acumularPvMensualYAcumulado(Order $order): void
    {
        /** @var User|null $u */
        $u = $order->user;
        if (! $u || $u->canAccessAdminPanel() || $u->isPreferredCustomer()) {
            return;
        }

        $pvCompra = $order->commissionablePvTotal(); // string scale 2
        if (bccomp($pvCompra, '0', 2) !== 1) {
            return;
        }

        $u = User::query()->whereKey($u->id)->lockForUpdate()->first();
        if (! $u) {
            return;
        }

        $monthKey = now()->format('Y-m');
        $lastMonth = (string) ($u->last_qualification_month ?? '');
        // Defensivo: si cambió el mes y aún no se reseteó, empezamos desde 0 para el mes nuevo.
        if ($lastMonth !== '' && $lastMonth !== $monthKey) {
            $u->monthly_qualifying_pv = '0';
        }

        $u->forceFill([
            'last_qualification_month' => $monthKey,
            'monthly_qualifying_pv' => bcadd((string) ($u->monthly_qualifying_pv ?? '0'), $pvCompra, 2),
            'lifetime_purchase_pv' => bcadd((string) ($u->lifetime_purchase_pv ?? '0'), $pvCompra, 2),
        ])->save();
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
