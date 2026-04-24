<?php

namespace App\Services;

use App\Models\Rank;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Ascenso de carrera post-fundador: PV de grupo (ligero), frontales activos, PV personal y rangos en línea directa.
 */
class CareerRankService
{
    /**
     * PV “grupo” simplificado para CARRERA/RANGO (histórico, NO reinicia):
     * - lifetime_qualifying_pv del usuario
     * - + lifetime_qualifying_pv de sus patrocinados directos (excluye preferred_customer)
     *
     * Importante:
     * - monthly_qualifying_pv se usa para snapshots/bonos mensuales (liderazgo).
     * - lifetime_qualifying_pv se usa para rangos/carrera (permanente).
     */
    public function groupQualifyingPvLight(User $user): string
    {
        // Regla: clientes preferentes NO cuentan para rangos/bonos de red.
        // Solo sumar directos con account_type member (o null legacy).
        $user->loadMissing('referrals');
        $sum = bcadd((string) ($user->lifetime_qualifying_pv ?? '0'), '0', 4);
        foreach ($user->referrals as $r) {
            if ($r->isPreferredCustomer()) {
                continue;
            }
            $sum = bcadd($sum, (string) ($r->lifetime_qualifying_pv ?? '0'), 4);
        }

        return bcadd($sum, '0', 2);
    }

    /**
     * Mayor slug de carrera al que el usuario cumple todos los requisitos (orden de mayor a menor).
     * Sin paquete fundador (≥1200 PV del paquete de registro) solo aplica rango base `activo`.
     */
    public function computeHighestEligibleRankSlug(User $user): string
    {
        if ($user->isPreferredCustomer() || $user->canAccessAdminPanel()) {
            return $user->rank?->slug ?? 'activo';
        }

        $minFundadorPv = (string) config('mlm.career.fundador_min_package_pv', '1200');
        $user->loadMissing('registrationPackage');
        $pkgPv = (string) ($user->registrationPackage?->pv_points ?? '0');
        if (bccomp($pkgPv, $minFundadorPv, 2) < 0) {
            return 'activo';
        }

        $order = config('mlm.career.rank_eval_order', []);
        $reqs = config('mlm.career.requirements', []);
        if ($order === []) {
            return $user->rank?->slug ?? 'activo';
        }

        $rankSort = $this->rankSortOrderBySlug();

        foreach (array_reverse($order) as $slug) {
            $cfg = $reqs[$slug] ?? null;
            if (! is_array($cfg)) {
                continue;
            }
            if ($this->meetsAll($user, (string) $slug, $cfg, $rankSort)) {
                return (string) $slug;
            }
        }

        return 'activo';
    }

    /**
     * @param  array<string, int|null>  $rankSort  slug => sort_order
     */
    protected function meetsAll(User $user, string $slug, array $cfg, array $rankSort): bool
    {
        $gv = $this->groupQualifyingPvLight($user);
        $minGv = (string) ($cfg['min_group_pv_light'] ?? '0');
        if (bccomp($gv, $minGv, 2) < 0) {
            return false;
        }

        $minPersonal = (string) ($cfg['min_personal_pv'] ?? '0');
        $personal = (string) ($user->lifetime_qualifying_pv ?? '0');
        if (bccomp($personal, $minPersonal, 2) < 0) {
            return false;
        }

        $needDirects = (int) ($cfg['min_direct_actives'] ?? 0);
        if ($needDirects > 0) {
            $minPv = (string) config('mlm.career.direct_active_min_pv', '50');
            $n = User::query()
                ->where('sponsor_id', $user->id)
                ->where(function ($w) {
                    $w->whereNull('account_type')->orWhere('account_type', 'member');
                })
                ->where('account_status', 'active')
                ->where('monthly_qualifying_pv', '>=', $minPv)
                ->count();
            if ($n < $needDirects) {
                return false;
            }
        }

        foreach ($cfg['min_directs_with_rank'] ?? [] as $rule) {
            if (! is_array($rule)) {
                continue;
            }
            $needSlug = (string) ($rule['slug'] ?? '');
            $needCount = (int) ($rule['min_count'] ?? 0);
            if ($needSlug === '' || $needCount < 1) {
                continue;
            }
            $minSo = (int) ($rankSort[$needSlug] ?? 999999);
            $count = User::query()
                ->where('sponsor_id', $user->id)
                ->where(function ($w) {
                    $w->whereNull('account_type')->orWhere('account_type', 'member');
                })
                ->whereHas('rank', fn ($q) => $q->where('sort_order', '>=', $minSo))
                ->count();
            if ($count < $needCount) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, int>
     */
    protected function rankSortOrderBySlug(): array
    {
        return Cache::remember('mlm:rank_sort_by_slug', 3600, function () {
            return Rank::query()->pluck('sort_order', 'slug')->all();
        });
    }

    public static function forgetRankSortCache(): void
    {
        Cache::forget('mlm:rank_sort_by_slug');
    }

    /**
     * Primer rango de carrera (en orden ascendente) que el usuario aún no cumple por completo.
     *
     * @return array{
     *   next_slug: ?string,
     *   next_min_group_pv: ?string,
     *   current_group_pv_light: string,
     *   percent_pv: float|null,
     *   missing_messages: list<string>,
     *   at_career_cap: bool
     * }
     */
    public function describeNextCareerStep(User $user): array
    {
        $order = config('mlm.career.rank_eval_order', []);
        $reqs = config('mlm.career.requirements', []);
        $rankSort = $this->rankSortOrderBySlug();

        $user->loadMissing('registrationPackage', 'referrals.rank', 'rank');
        $gv = $this->groupQualifyingPvLight($user);
        $minFundadorPv = (string) config('mlm.career.fundador_min_package_pv', '1200');
        $pkgPv = (string) ($user->registrationPackage?->pv_points ?? '0');

        if (bccomp($pkgPv, $minFundadorPv, 2) < 0) {
            return [
                'next_slug' => null,
                'next_min_group_pv' => null,
                'current_group_pv_light' => $gv,
                'percent_pv' => null,
                'missing_messages' => [
                    'Completa el paquete Fundador ('.$minFundadorPv.' PV) para acceder a rangos de carrera.',
                ],
                'at_career_cap' => false,
            ];
        }

        $nextSlug = null;
        $nextCfg = null;
        foreach ($order as $slug) {
            $cfg = $reqs[$slug] ?? null;
            if (! is_array($cfg)) {
                continue;
            }
            if (! $this->meetsAll($user, $slug, $cfg, $rankSort)) {
                $nextSlug = $slug;
                $nextCfg = $cfg;
                break;
            }
        }

        if ($nextSlug === null || $nextCfg === null) {
            return [
                'next_slug' => null,
                'next_min_group_pv' => null,
                'current_group_pv_light' => $gv,
                'percent_pv' => 100.0,
                'missing_messages' => [],
                'at_career_cap' => true,
            ];
        }

        $nextMin = (string) ($nextCfg['min_group_pv_light'] ?? '0');
        $prevMin = '0';
        foreach ($order as $s) {
            if ($s === $nextSlug) {
                break;
            }
            $c = $reqs[$s] ?? [];
            if (is_array($c) && isset($c['min_group_pv_light'])) {
                $prevMin = (string) $c['min_group_pv_light'];
            }
        }

        $percent = null;
        $span = bcsub($nextMin, $prevMin, 2);
        if (bccomp($span, '0', 2) === 1) {
            $done = bcsub($gv, $prevMin, 2);
            $percent = (float) bcmul(bcdiv($done, $span, 6), '100', 4);
            $percent = min(100.0, max(0.0, $percent));
        }

        $missing = $this->missingRequirementMessages($user, $nextSlug, $nextCfg, $rankSort);

        return [
            'next_slug' => $nextSlug,
            'next_min_group_pv' => $nextMin,
            'current_group_pv_light' => $gv,
            'percent_pv' => $percent,
            'missing_messages' => $missing,
            'at_career_cap' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @param  array<string, int|null>  $rankSort
     * @return list<string>
     */
    protected function missingRequirementMessages(User $user, string $slug, array $cfg, array $rankSort): array
    {
        $out = [];
        $gv = $this->groupQualifyingPvLight($user);
        $minGv = (string) ($cfg['min_group_pv_light'] ?? '0');
        if (bccomp($gv, $minGv, 2) < 0) {
            $out[] = 'PV de grupo (estimado): '.$gv.' / '.$minGv.' hacia '.$slug;
        }

        $minPersonal = (string) ($cfg['min_personal_pv'] ?? '0');
        $personal = (string) ($user->monthly_qualifying_pv ?? '0');
        if (bccomp($personal, $minPersonal, 2) < 0) {
            $out[] = 'Activación personal: '.$personal.' / '.$minPersonal.' PV este mes';
        }

        $needDirects = (int) ($cfg['min_direct_actives'] ?? 0);
        if ($needDirects > 0) {
            $minPv = (string) config('mlm.career.direct_active_min_pv', '50');
            $n = User::query()
                ->where('sponsor_id', $user->id)
                ->where(function ($w) {
                    $w->whereNull('account_type')->orWhere('account_type', 'member');
                })
                ->where('account_status', 'active')
                ->where('monthly_qualifying_pv', '>=', $minPv)
                ->count();
            if ($n < $needDirects) {
                $out[] = 'Frontales directos activos: '.$n.' / '.$needDirects.' (≥ '.$minPv.' PV c/u)';
            }
        }

        foreach ($cfg['min_directs_with_rank'] ?? [] as $rule) {
            if (! is_array($rule)) {
                continue;
            }
            $needSlug = (string) ($rule['slug'] ?? '');
            $needCount = (int) ($rule['min_count'] ?? 0);
            if ($needSlug === '' || $needCount < 1) {
                continue;
            }
            $minSo = (int) ($rankSort[$needSlug] ?? 999999);
            $count = User::query()
                ->where('sponsor_id', $user->id)
                ->where(function ($w) {
                    $w->whereNull('account_type')->orWhere('account_type', 'member');
                })
                ->whereHas('rank', fn ($q) => $q->where('sort_order', '>=', $minSo))
                ->count();
            if ($count < $needCount) {
                $out[] = 'Directos con rango ≥ '.$needSlug.': '.$count.' / '.$needCount;
            }
        }

        return $out;
    }
}
