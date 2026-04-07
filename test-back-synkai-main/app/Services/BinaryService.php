<?php

namespace App\Services;

use App\Models\BinaryLegVolumeWeekly;
use App\Models\BinaryPlacement;
use App\Models\BinaryWeeklyCarry;
use App\Models\Order;
use App\Models\OrderBinaryVolumeApplied;
use App\Models\PeriodClosure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BinaryService
{
    public function weekKey(Carbon $date): string
    {
        return $date->format('o-\WW');
    }

    public function previousWeekKey(string $weekKey): string
    {
        if (! preg_match('/^(\d{4})-W(\d{2})$/', $weekKey, $m)) {
            return $weekKey;
        }

        $d = Carbon::now()->setISODate((int) $m[1], (int) $m[2])->subWeek();

        return $d->format('o-\WW');
    }

    /**
     * @return list<array{user_id:int, leg:string}>
     */
    public function ancestrosBinarioConPierna(int $userId): array
    {
        $ttl = (int) config('mlm.binary.cache_ttl_seconds', 600);
        $prefix = config('mlm.binary.cache_prefix', 'mlm:binary:');

        return Cache::remember("{$prefix}ancestors:{$userId}", $ttl, function () use ($userId) {
            $chain = [];
            $cursor = BinaryPlacement::query()->where('user_id', $userId)->first();

            while ($cursor && $cursor->parent_user_id && $cursor->leg_position) {
                $chain[] = [
                    'user_id' => (int) $cursor->parent_user_id,
                    'leg' => (string) $cursor->leg_position,
                ];
                $cursor = BinaryPlacement::query()->where('user_id', $cursor->parent_user_id)->first();
            }

            return $chain;
        });
    }

    public function olvidarCacheArbol(int $userId): void
    {
        $prefix = config('mlm.binary.cache_prefix', 'mlm:binary:');
        Cache::forget("{$prefix}ancestors:{$userId}");
    }

    /**
     * Idempotente por pedido: suma PV de la orden en la pierna correcta de cada ancestro binario.
     */
    public function acumularVolumenBinarioPorPedido(Order $order): void
    {
        if (OrderBinaryVolumeApplied::query()->where('order_id', $order->id)->exists()) {
            return;
        }

        $pv = (string) $order->total_pv;
        if (bccomp($pv, '0', 2) !== 1) {
            return;
        }

        $weekKey = $order->completed_at
            ? $this->weekKey(Carbon::parse($order->completed_at))
            : $this->weekKey(now());

        $chain = $this->ancestrosBinarioConPierna($order->user_id);
        if ($chain === []) {
            return;
        }

        DB::transaction(function () use ($order, $pv, $weekKey, $chain) {
            foreach ($chain as $hop) {
                $this->incrementarVolumenPierna((int) $hop['user_id'], $weekKey, (string) $hop['leg'], $pv);
                $this->olvidarCacheArbol((int) $hop['user_id']);
            }

            OrderBinaryVolumeApplied::query()->create([
                'order_id' => $order->id,
                'week_key' => $weekKey,
                'applied_at' => now(),
            ]);
        });

        $this->olvidarCacheArbol($order->user_id);
    }

    public function procesarCierreSemanal(string $weekKey, CommissionService $commissionService): void
    {
        $closure = PeriodClosure::query()->firstOrCreate(
            [
                'period_type' => 'weekly',
                'period_key' => $weekKey,
                'scope' => 'binary',
            ],
            ['status' => 'pending']
        );

        if ($closure->status === 'finished') {
            return;
        }

        $closure->update(['status' => 'running', 'started_at' => now()]);

        $prevKey = $this->previousWeekKey($weekKey);
        $payoutPerPv = (string) config('mlm.binary.payout_per_matched_pv', '1');

        $parentIdsVol = BinaryLegVolumeWeekly::query()
            ->where('week_key', $weekKey)
            ->distinct()
            ->pluck('parent_user_id');

        $parentIdsCarry = BinaryWeeklyCarry::query()
            ->where('week_key', $prevKey)
            ->pluck('user_id');

        $allParents = $parentIdsVol->merge($parentIdsCarry)->unique()->filter();

        foreach ($allParents as $parentId) {
            $rawL = $this->volumenPierna((int) $parentId, $weekKey, BinaryPlacement::LEG_LEFT);
            $rawR = $this->volumenPierna((int) $parentId, $weekKey, BinaryPlacement::LEG_RIGHT);

            $carry = BinaryWeeklyCarry::query()
                ->where('user_id', $parentId)
                ->where('week_key', $prevKey)
                ->first();

            $carryL = (string) ($carry?->left_carry_pv ?? '0');
            $carryR = (string) ($carry?->right_carry_pv ?? '0');

            $effL = bcadd($carryL, $rawL, 4);
            $effR = bcadd($carryR, $rawR, 4);

            $paidVol = bccomp($effL, $effR, 4) <= 0 ? $effL : $effR;
            $outL = bcsub($effL, $paidVol, 4);
            $outR = bcsub($effR, $paidVol, 4);

            $payout = bcmul($paidVol, $payoutPerPv, 4);
            $payout = bcadd($payout, '0', 2);

            BinaryWeeklyCarry::query()->updateOrCreate(
                [
                    'user_id' => $parentId,
                    'week_key' => $weekKey,
                ],
                [
                    'left_carry_pv' => bcadd($outL, '0', 2),
                    'right_carry_pv' => bcadd($outR, '0', 2),
                ]
            );

            if (bccomp($payout, '0', 2) === 1) {
                $commissionService->calcularBinario($weekKey, (int) $parentId, bcadd($paidVol, '0', 2), $payout);
            }
        }

        $closure->update([
            'status' => 'finished',
            'finished_at' => now(),
            'meta' => ['parents_processed' => $allParents->count()],
        ]);

        Log::info('MLM cierre binario', ['week' => $weekKey, 'parents' => $allParents->count()]);
    }

    protected function incrementarVolumenPierna(int $parentUserId, string $weekKey, string $leg, string $pv): void
    {
        $row = BinaryLegVolumeWeekly::query()->firstOrNew([
            'parent_user_id' => $parentUserId,
            'week_key' => $weekKey,
            'leg_position' => $leg,
        ]);

        $row->volume_pv = bcadd((string) ($row->volume_pv ?? '0'), $pv, 2);
        $row->save();
    }

    protected function volumenPierna(int $parentUserId, string $weekKey, string $leg): string
    {
        $row = BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $parentUserId)
            ->where('week_key', $weekKey)
            ->where('leg_position', $leg)
            ->first();

        return $row ? (string) $row->volume_pv : '0';
    }
}
