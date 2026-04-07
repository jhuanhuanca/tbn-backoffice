<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionEvent;
use App\Models\Order;
use App\Models\PeriodClosure;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    public function calcularBonoInicioRapido(Order $order): void
    {
        $buyer = $order->user;
        if (! $buyer?->sponsor_id) {
            return;
        }

        $schedule = config('mlm.bir.schedule', []);

        foreach ($order->items as $item) {
            if (! $item->package_id || ! $item->package) {
                continue;
            }

            $base = bcmul($item->package->commissionableValue(), (string) $item->cantidad, 4);
            $base = $this->roundMoney($base);

            $sponsor = $buyer->sponsor;
            $level = 1;

            while ($sponsor && $level <= 3) {
                $rate = (string) ($schedule[$level] ?? '0');
                $amount = $this->roundMoney(bcmul($base, $rate, 4));

                if (bccomp($amount, '0', 2) === 1) {
                    $key = "bir:order:{$order->id}:pkg:{$item->id}:lvl:{$level}";
                    $this->registrarYAcreditar(
                        idempotencyKey: $key,
                        beneficiary: $sponsor,
                        origin: $buyer,
                        type: 'bir',
                        level: $level,
                        amount: $amount,
                        order: $order,
                        periodKey: $this->weekKey(now()),
                        periodType: 'weekly',
                        meta: ['package_id' => $item->package_id, 'order_item_id' => $item->id]
                    );
                }

                $sponsor = $sponsor->sponsor;
                $level++;
            }
        }
    }

    /**
     * Residual unilevel por pedido (hasta 12 generaciones según rango del beneficiario).
     */
    public function calcularResidualPorPedido(Order $order): void
    {
        $buyer = $order->user;
        if (! $buyer?->sponsor_id) {
            return;
        }

        $pv = $this->roundMoney((string) $order->total_pv);
        if (bccomp($pv, '0', 2) !== 1) {
            return;
        }

        $gens = config('mlm.residual.generations', []);
        $currentSponsorId = $buyer->sponsor_id;
        $gen = 1;

        while ($currentSponsorId && $gen <= 12) {
            $sponsor = User::query()->with('rank')->find($currentSponsorId);
            if (! $sponsor) {
                break;
            }

            $maxGen = (int) ($sponsor->rank?->max_residual_generations ?? 0);
            if ($maxGen >= $gen) {
                $rate = (string) ($gens[$gen] ?? '0');
                $amount = $this->roundMoney(bcmul($pv, $rate, 4));
                if (bccomp($amount, '0', 2) === 1) {
                    $key = "residual:order:{$order->id}:gen:{$gen}:u:{$sponsor->id}";
                    $this->registrarYAcreditar(
                        idempotencyKey: $key,
                        beneficiary: $sponsor,
                        origin: $buyer,
                        type: 'residual',
                        level: $gen,
                        amount: $amount,
                        order: $order,
                        periodKey: now()->format('Y-m'),
                        periodType: 'monthly',
                        meta: ['pv' => $pv]
                    );
                }
            }

            $currentSponsorId = $sponsor->sponsor_id;
            $gen++;
        }
    }

    /**
     * Binario: paga volumen emparejado en pierna débil y guarda carry para la siguiente semana ISO.
     */
    public function calcularBinario(string $weekKey, int $userId, string $matchedPv, string $payoutAmount): void
    {
        $user = User::query()->find($userId);
        if (! $user || bccomp($payoutAmount, '0', 2) !== 1) {
            return;
        }

        $key = "binary:{$userId}:{$weekKey}";
        $this->registrarYAcreditar(
            idempotencyKey: $key,
            beneficiary: $user,
            origin: null,
            type: 'binary',
            level: null,
            amount: $payoutAmount,
            order: null,
            periodKey: $weekKey,
            periodType: 'weekly',
            meta: ['matched_pv' => $matchedPv]
        );
    }

    /**
     * Bono de liderazgo: extensible; aplica tasa del rango del beneficiario sobre comisiones de downline (placeholder seguro).
     */
    public function calcularLiderazgo(User $beneficiary, string $monthKey, string $baseAmount): void
    {
        $rate = (string) ($beneficiary->rank?->leadership_rate ?? config('mlm.leadership.default_rate', 0));
        if (bccomp($rate, '0', 6) !== 1) {
            return;
        }

        $amount = $this->roundMoney(bcmul($baseAmount, $rate, 4));
        if (bccomp($amount, '0', 2) !== 1) {
            return;
        }

        $key = "leadership:u:{$beneficiary->id}:{$monthKey}";
        $this->registrarYAcreditar(
            idempotencyKey: $key,
            beneficiary: $beneficiary,
            origin: null,
            type: 'leadership',
            level: null,
            amount: $amount,
            order: null,
            periodKey: $monthKey,
            periodType: 'monthly',
            meta: ['base' => $baseAmount, 'rate' => $rate]
        );
    }

    public function procesarCierreMensual(string $monthKey): void
    {
        $closure = PeriodClosure::query()->firstOrCreate(
            [
                'period_type' => 'monthly',
                'period_key' => $monthKey,
                'scope' => 'residual_audit',
            ],
            ['status' => 'pending']
        );

        if ($closure->status === 'finished') {
            return;
        }

        $closure->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        Log::info('MLM cierre mensual ejecutado (auditoría / extensión)', ['month' => $monthKey]);

        $closure->update([
            'status' => 'finished',
            'finished_at' => now(),
            'meta' => ['note' => 'Residual principal se acredita por pedido; use este cierre para reportes o leadership.'],
        ]);
    }

    protected function registrarYAcreditar(
        string $idempotencyKey,
        User $beneficiary,
        ?User $origin,
        string $type,
        ?int $level,
        string $amount,
        ?Order $order,
        ?string $periodKey,
        ?string $periodType,
        array $meta = []
    ): void {
        DB::transaction(function () use ($idempotencyKey, $beneficiary, $origin, $type, $level, $amount, $order, $periodKey, $periodType, $meta) {
            if (CommissionEvent::query()->where('idempotency_key', $idempotencyKey)->exists()) {
                return;
            }

            $event = CommissionEvent::query()->create([
                'idempotency_key' => $idempotencyKey,
                'beneficiary_user_id' => $beneficiary->id,
                'origin_user_id' => $origin?->id,
                'type' => $type,
                'level' => $level,
                'amount' => $amount,
                'currency' => config('mlm.currency', 'BOB'),
                'period_key' => $periodKey,
                'period_type' => $periodType,
                'order_id' => $order?->id,
                'meta' => $meta,
                'created_at' => now(),
            ]);

            $walletKey = 'wallet:credit:'.$idempotencyKey;
            $tx = $this->walletService->acreditar(
                $beneficiary,
                $amount,
                $walletKey,
                $event,
                reference: $type,
                description: "Comisión {$type}",
                meta: array_merge($meta, ['commission_event_id' => $event->id])
            );

            Commission::query()->create([
                'commission_event_id' => $event->id,
                'user_id' => $beneficiary->id,
                'type' => $type,
                'level' => $level,
                'amount' => $amount,
                'status' => 'accrued',
                'wallet_transaction_id' => $tx?->id,
            ]);
        });
    }

    protected function weekKey(Carbon $date): string
    {
        return $date->format('o-\WW');
    }

    protected function roundMoney(string $value): string
    {
        return bcadd($value, '0', 2);
    }
}
