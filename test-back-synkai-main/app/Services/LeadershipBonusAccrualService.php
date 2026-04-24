<?php

namespace App\Services;

use App\Models\Rank;
use App\Models\User;
use App\Models\UserMonthlyRankSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Bono de liderazgo (según fórmula pedida) integrado a BD:
 * - Usa snapshots mensuales (`user_monthly_rank_snapshots`) para PV del mes y racha de rango.
 * - Calcula PV equipo mensual (light): PV propio + suma PV de directos (excluye preferred_customer).
 * - Si teamPV >= requiredPV y streak >= N, entonces:
 *      bonusPV = requiredPV * leadershipRate
 *      bonusBOB = bonusPV * bobPerPv
 * - Acredita como CommissionEvent type=leadership, period_type=monthly, period_key=monthKey.
 */
class LeadershipBonusAccrualService
{
    public function __construct(
        protected CommissionService $commissionService
    ) {}

    public function processMonth(string $monthKey): void
    {
        // Mes anterior por defecto si viene vacío/incorrecto
        if (! preg_match('/^\d{4}-\d{2}$/', $monthKey)) {
            $monthKey = Carbon::now()->subMonth()->format('Y-m');
        }

        $bobPerPv = (string) config('mlm.pv_value.bob_per_pv', '7');

        // Cache simple en memoria por ejecución (evita queries repetidas).
        $rankById = [];

        User::query()
            ->with('rank')
            ->where(function ($w) {
                // Solo socios (member o legacy null)
                $w->whereNull('account_type')->orWhere('account_type', 'member');
            })
            ->where('account_status', 'active')
            ->whereHas('rank', fn ($q) => $q->where('leadership_rate', '>', 0))
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($monthKey, $bobPerPv, &$rankById) {
                foreach ($users as $u) {
                    $snap = UserMonthlyRankSnapshot::query()
                        ->where('user_id', $u->id)
                        ->where('month_key', $monthKey)
                        ->first();
                    if (! $snap) {
                        // Sin snapshot mensual no hay base confiable para liderazgo.
                        continue;
                    }

                    // Regla corregida:
                    // - El bono se evalúa MES A MES.
                    // - Se paga solo si en ese mes alcanzó el PV requerido del rango (según snapshot del mes).
                    // - No requiere "racha de 3 meses"; cada mes es independiente.

                    $rankId = (int) ($snap->rank_id ?? 0);
                    if ($rankId <= 0) {
                        continue;
                    }

                    if (! isset($rankById[$rankId])) {
                        $rankById[$rankId] = Rank::query()->find($rankId);
                    }
                    /** @var Rank|null $rank */
                    $rank = $rankById[$rankId];
                    if (! $rank) {
                        continue;
                    }

                    $rate = $this->clampBetweenZeroOne((string) ($rank->leadership_rate ?? '0'), 6);
                    if (bccomp($rate, '0', 6) !== 1) {
                        continue;
                    }

                    // El PV requerido se evalúa según el rango del SNAPSHOT del mes (no el rango actual).
                    // Esto hace el cálculo consistente históricamente aunque el usuario cambie de rango después
                    // o se ajusten requisitos en config.
                    $requiredPv = $this->requiredPvForRankSlug((string) ($snap->rank_slug ?? $rank->slug ?? ''));
                    $requiredPv = $this->clampNonNegativeDecimal($requiredPv, 4);
                    if (bccomp($requiredPv, '0', 4) !== 1) {
                        continue;
                    }

                    $teamPv = $this->teamPvLightFromSnapshots((int) $u->id, $monthKey);
                    if (bccomp($teamPv, $requiredPv, 4) === -1) {
                        continue;
                    }

                    $bonusPv = bcmul($requiredPv, $rate, 6);
                    $amount = $this->roundMoney(bcmul($bonusPv, (string) $bobPerPv, 4));
                    if (bccomp($amount, '0', 2) !== 1) {
                        continue;
                    }

                    // Idempotencia: CommissionService ya usa "leadership:u:{id}:{month}".
                    $this->commissionService->calcularLiderazgo($u, $monthKey, $requiredPv);
                }
            });
    }

    protected function teamPvLightFromSnapshots(int $userId, string $monthKey): string
    {
        $own = (string) UserMonthlyRankSnapshot::query()
            ->where('user_id', $userId)
            ->where('month_key', $monthKey)
            ->value('qualifying_pv') ?? '0';
        $own = $this->clampNonNegativeDecimal($own, 4);

        // PV de directos (account_type member/null)
        $directIds = User::query()
            ->where('sponsor_id', $userId)
            ->where(function ($w) {
                $w->whereNull('account_type')->orWhere('account_type', 'member');
            })
            ->pluck('id')
            ->all();

        if ($directIds === []) {
            return $own;
        }

        $sumDirect = (string) UserMonthlyRankSnapshot::query()
            ->where('month_key', $monthKey)
            ->whereIn('user_id', $directIds)
            ->sum('qualifying_pv');

        $sumDirect = $this->clampNonNegativeDecimal($sumDirect, 4);
        return bcadd($own, $sumDirect, 4);
    }

    protected function requiredPvForRankSlug(string $slug): string
    {
        $map = config('mlm.leadership.required_pv_by_rank_slug', []);
        if ($slug !== '' && isset($map[$slug])) {
            return (string) $map[$slug];
        }

        $career = config("mlm.career.requirements.{$slug}.min_group_pv_light");
        if ($career !== null) {
            return (string) $career;
        }

        return '0';
    }

    protected function roundMoney(string $amount): string
    {
        return bcadd($amount, '0', 2);
    }

    protected function clampNonNegativeDecimal(string $v, int $scale): string
    {
        $n = trim($v);
        if ($n === '' || ! is_numeric($n)) {
            return bcadd('0', '0', $scale);
        }
        if (str_starts_with($n, '-')) {
            return bcadd('0', '0', $scale);
        }

        return bcadd($n, '0', $scale);
    }

    protected function clampBetweenZeroOne(string $v, int $scale): string
    {
        $n = $this->clampNonNegativeDecimal($v, $scale);
        if (bccomp($n, '1', $scale) === 1) {
            return bcadd('1', '0', $scale);
        }

        return $n;
    }
}

