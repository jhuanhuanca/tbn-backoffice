<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BinaryLegVolumeWeekly;
use App\Models\BinaryPlacement;
use App\Models\BinaryDailyPayout;
use App\Models\BinaryWeeklyBonus;
use App\Models\BinaryWeeklyCarry;
use App\Models\CommissionEvent;
use App\Models\Rank;
use App\Models\User;
use App\Services\BinaryService;
use App\Services\CareerRankService;
use App\Services\MlmBonusProgressService;
use App\Services\WalletService;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function profile(Request $request, CareerRankService $careerRankService)
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('rank', 'sponsor', 'registrationPackage', 'referrals.rank');

        $computedSlug = $careerRankService->computeHighestEligibleRankSlug($user);
        $computedRank = Rank::query()->where('slug', $computedSlug)->first();
        $computedName = $computedRank?->name ?? $computedSlug;

        $payload = $user->toArray();
        $payload['computed_rank'] = [
            'slug' => $computedSlug,
            'name' => $computedName,
        ];
        // Conveniencia para el front (compatibilidad con `rank_name`).
        $payload['rank_name'] = $computedName;

        return response()->json($payload);
    }

    public function dashboard(
        Request $request,
        WalletService $walletService,
        BinaryService $binaryService,
        MlmBonusProgressService $bonusProgress,
        CareerRankService $careerRankService
    )
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('rank', 'sponsor');

        $periodKey = $binaryService->volumePeriodKey(now());
        $prevKey = $binaryService->previousVolumePeriodKey($periodKey);

        $leftPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $periodKey)
            ->where('leg_position', 'left')
            ->value('volume_pv') ?? '0';

        $rightPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $periodKey)
            ->where('leg_position', 'right')
            ->value('volume_pv') ?? '0';

        $carry = BinaryWeeklyCarry::query()
            ->where('user_id', $user->id)
            ->where('week_key', $prevKey)
            ->first();

        $directCount = User::query()->where('sponsor_id', $user->id)->count();

        $commissionsTotal = (string) CommissionEvent::query()
            ->where('beneficiary_user_id', $user->id)
            ->sum('amount');

        $computedSlug = $careerRankService->computeHighestEligibleRankSlug($user->loadMissing('registrationPackage', 'referrals.rank'));
        $computedRank = Rank::query()->where('slug', $computedSlug)->first();
        $computedName = $computedRank?->name ?? $computedSlug;

        return response()->json([
            'commissions_total' => $commissionsTotal,
            'bonus_progress' => $bonusProgress->resumen($user),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'referral_code' => $user->referral_code,
                'member_code' => $user->member_code,
                'document_id' => $user->document_id,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
                'is_mlm_qualified' => $user->is_mlm_qualified,
                'monthly_qualifying_pv' => $user->monthly_qualifying_pv,
                'account_status' => $user->account_status,
                'rank_name' => $computedName,
            ],
            // Rango “vigente” calculado por reglas (no depende solo de rank_id).
            'rank' => [
                'id' => $computedRank?->id,
                'name' => $computedName,
                'slug' => $computedSlug,
            ],
            'sponsor' => $user->sponsor ? [
                'name' => $user->sponsor->name,
                'referral_code' => $user->sponsor->referral_code,
            ] : null,
            'wallet' => [
                'available' => $walletService->saldoDisponible($user),
            ],
            'referrals_direct_count' => $directCount,
            'binary_week' => [
                'week_key' => $periodKey,
                'volume_period' => $binaryService->isMonthlyBinaryVolume() ? 'monthly' : 'weekly',
                'left_pv' => $leftPv,
                'right_pv' => $rightPv,
                'left_carry_in' => $carry ? (string) $carry->left_carry_pv : '0',
                'right_carry_in' => $carry ? (string) $carry->right_carry_pv : '0',
            ],
        ]);
    }

    public function referrals(Request $request)
    {
        $rows = User::query()
            ->where('sponsor_id', $request->user()->id)
            ->with(['rank:id,name,slug', 'binaryPlacement'])
            ->orderByDesc('created_at')
            ->get();

        $items = $rows->map(function (User $u) {
            $leg = $u->binaryPlacement?->leg_position;

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'referral_code' => $u->referral_code,
                'member_code' => $u->member_code,
                'preferred_binary_leg' => $u->preferred_binary_leg,
                'pierna' => $leg,
                'fecha_alta' => $u->created_at?->format('d/m/Y'),
                'joined_at' => $u->created_at?->toIso8601String(),
                'monthly_qualifying_pv' => $u->monthly_qualifying_pv,
                'account_status' => $u->account_status,
                'rank_name' => $u->rank?->name ?? '—',
            ];
        });

        $activos = $items->where('account_status', 'active')->count();
        $pendientes = $items->where('account_status', 'pending')->count();
        $izq = $items->where('pierna', BinaryPlacement::LEG_LEFT)->count();
        $der = $items->where('pierna', BinaryPlacement::LEG_RIGHT)->count();

        return response()->json([
            'items' => $items->values(),
            'summary' => [
                'total' => $items->count(),
                'activos' => $activos,
                'pendientes' => $pendientes,
                'izquierda' => $izq,
                'derecha' => $der,
            ],
        ]);
    }

    /**
     * Árbol UNILEVEL hasta N generaciones (por sponsor_id).
     * Retorna niveles (1..depth) y una estructura children_by_sponsor para render.
     */
    public function unilevelTree(Request $request)
    {
        /** @var User $me */
        $me = $request->user();

        $depth = (int) ($request->query('depth', 3));
        if ($depth < 1) {
            $depth = 1;
        }
        if ($depth > 6) {
            $depth = 6; // límite defensivo
        }

        $levels = [];
        $childrenBySponsor = [];

        $currentSponsorIds = [$me->id];

        for ($gen = 1; $gen <= $depth; $gen++) {
            $rows = User::query()
                ->whereIn('sponsor_id', $currentSponsorIds)
                ->with(['rank:id,name,slug'])
                ->orderBy('created_at')
                ->get();

            $items = $rows->map(function (User $u) {
                return [
                    'id' => $u->id,
                    'sponsor_id' => $u->sponsor_id,
                    'name' => $u->name,
                    'member_code' => $u->member_code,
                    'joined_at' => $u->created_at?->toIso8601String(),
                    'fecha_alta' => $u->created_at?->format('d/m/Y'),
                    'monthly_qualifying_pv' => $u->monthly_qualifying_pv,
                    'account_status' => $u->account_status,
                    'rank_name' => $u->rank?->name ?? '—',
                ];
            })->values();

            $levels[(string) $gen] = $items;

            foreach ($items as $it) {
                $sid = (string) ($it['sponsor_id'] ?? 0);
                if (! isset($childrenBySponsor[$sid])) {
                    $childrenBySponsor[$sid] = [];
                }
                $childrenBySponsor[$sid][] = $it;
            }

            $currentSponsorIds = $rows->pluck('id')->all();
            if (empty($currentSponsorIds)) {
                break;
            }
        }

        return response()->json([
            'depth' => $depth,
            'root' => [
                'id' => $me->id,
                'name' => $me->name,
                'member_code' => $me->member_code,
            ],
            'levels' => $levels,
            'children_by_sponsor' => $childrenBySponsor,
        ]);
    }

    /**
     * Vista de árbol binario (2 niveles de profundidad) + KPIs para el front.
     */
    public function binaryTree(Request $request, BinaryService $binaryService, WalletService $walletService)
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('rank');
        $periodKey = $binaryService->volumePeriodKey(now());

        $leftPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $periodKey)
            ->where('leg_position', 'left')
            ->value('volume_pv') ?? '0';

        $rightPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $periodKey)
            ->where('leg_position', 'right')
            ->value('volume_pv') ?? '0';

        $binaryEarningsWeek = (string) CommissionEvent::query()
            ->where('beneficiary_user_id', $user->id)
            ->where('type', 'binary')
            ->where('period_key', $periodKey)
            ->sum('amount');

        $birByLevel = [];
        for ($level = 1; $level <= 3; $level++) {
            $birByLevel[$level] = (string) CommissionEvent::query()
                ->where('beneficiary_user_id', $user->id)
                ->where('type', 'bir')
                ->where('level', $level)
                ->sum('amount');
        }

        $birTotal = (string) CommissionEvent::query()
            ->where('beneficiary_user_id', $user->id)
            ->where('type', 'bir')
            ->sum('amount');

        $residualTotal = (string) CommissionEvent::query()
            ->where('beneficiary_user_id', $user->id)
            ->where('type', 'residual')
            ->sum('amount');

        $rankName = $user->rank?->name ?? '—';

        return response()->json([
            'week_key' => $periodKey,
            'volume_period' => $binaryService->isMonthlyBinaryVolume() ? 'monthly' : 'weekly',
            'wallet_available' => $walletService->saldoDisponible($user),
            'stats' => [
                'left_pv' => $leftPv,
                'right_pv' => $rightPv,
                'binary_earnings_week' => $binaryEarningsWeek,
                'bir_total' => $birTotal,
                'bir_by_level' => $birByLevel,
                'residual_total' => $residualTotal,
                'current_rank' => $rankName,
                'binary_network_count' => $this->countBinaryDownlineUsers($user->id),
            ],
            'bir_percentages' => config('mlm.bir.schedule', []),
            'tree' => $this->binarySubtreePayload($user->id, 2),
        ]);
    }

    private function countBinaryDownlineUsers(int $parentUserId): int
    {
        $left = BinaryPlacement::query()
            ->where('parent_user_id', $parentUserId)
            ->where('leg_position', BinaryPlacement::LEG_LEFT)
            ->first();
        $right = BinaryPlacement::query()
            ->where('parent_user_id', $parentUserId)
            ->where('leg_position', BinaryPlacement::LEG_RIGHT)
            ->first();

        $n = 0;
        if ($left) {
            $n++;
            $n += $this->countBinaryDownlineUsers($left->user_id);
        }
        if ($right) {
            $n++;
            $n += $this->countBinaryDownlineUsers($right->user_id);
        }

        return $n;
    }

    private function binarySubtreePayload(?int $userId, int $levelsBelow): ?array
    {
        if (! $userId) {
            return null;
        }

        $u = User::query()->find($userId);
        if (! $u) {
            return null;
        }

        $node = [
            'user' => [
                'id' => $u->id,
                'name' => $u->name,
                'member_code' => $u->member_code,
                'initials' => $this->initialsFromName($u->name),
            ],
            'left' => null,
            'right' => null,
        ];

        if ($levelsBelow <= 0) {
            return $node;
        }

        $leftPl = BinaryPlacement::query()
            ->where('parent_user_id', $userId)
            ->where('leg_position', BinaryPlacement::LEG_LEFT)
            ->first();
        $rightPl = BinaryPlacement::query()
            ->where('parent_user_id', $userId)
            ->where('leg_position', BinaryPlacement::LEG_RIGHT)
            ->first();

        $node['left'] = $leftPl
            ? $this->binarySubtreePayload($leftPl->user_id, $levelsBelow - 1)
            : null;
        $node['right'] = $rightPl
            ? $this->binarySubtreePayload($rightPl->user_id, $levelsBelow - 1)
            : null;

        return $node;
    }

    private function initialsFromName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
        $a = $parts[0][0] ?? 'U';
        $b = isset($parts[1]) ? $parts[1][0] : '';

        return strtoupper($a.$b);
    }

    public function commissions(Request $request)
    {
        $userId = $request->user()->id;
        $bobPerPv = (string) config('mlm.pv_value.bob_per_pv', '7');

        $total = (string) CommissionEvent::query()
            ->where('beneficiary_user_id', $userId)
            ->sum('amount');

        $birByLevel = [];
        for ($level = 1; $level <= 3; $level++) {
            $birByLevel[$level] = (string) CommissionEvent::query()
                ->where('beneficiary_user_id', $userId)
                ->where('type', 'bir')
                ->where('level', $level)
                ->sum('amount');
        }

        $items = CommissionEvent::query()
            ->where('beneficiary_user_id', $userId)
            ->with('commission')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(function (CommissionEvent $e) {
                $bobPerPv = (string) config('mlm.pv_value.bob_per_pv', '7');
                $meta = is_array($e->meta) ? $e->meta : [];
                $pv = null;
                if (isset($meta['pv_comision_nivel'])) {
                    $pv = (string) $meta['pv_comision_nivel'];
                } elseif (isset($meta['pv_comision'])) {
                    $pv = (string) $meta['pv_comision'];
                } elseif (isset($meta['matched_pv'])) {
                    $pv = (string) $meta['matched_pv'];
                } elseif (bccomp((string) $bobPerPv, '0', 2) === 1) {
                    // PV aproximado si solo tenemos Bs (retrocompat).
                    $pv = bcdiv((string) $e->amount, (string) $bobPerPv, 4);
                }

                return [
                    'id' => $e->id,
                    'type' => $e->type,
                    'type_label' => match ($e->type) {
                        'bir' => 'Bono inicio rápido',
                        'binary' => 'Bono binario',
                        'residual' => 'Residual',
                        'leadership' => 'Liderazgo',
                        'venta_directa' => 'Bono venta directa',
                        default => strtoupper($e->type),
                    },
                    'amount_bob' => (string) $e->amount,
                    'pv_amount' => $pv,
                    'bob_per_pv' => $meta['bob_per_pv'] ?? $bobPerPv,
                    'currency' => $e->currency,
                    'level' => $e->level,
                    'period_key' => $e->period_key,
                    'created_at' => $e->created_at?->toIso8601String(),
                    'period_display' => $e->created_at ? $e->created_at->format('d/m/y') : null,
                    'status' => $e->commission?->status ?? 'accrued',
                ];
            });

        $binaryHybrid = null;
        if ((bool) config('mlm.binary.hybrid_daily.enabled', false)) {
            $days = [];
            for ($i = 0; $i < 14; $i++) {
                $days[] = now()->subDays($i)->toDateString();
            }

            $daily = BinaryDailyPayout::query()
                ->where('user_id', $userId)
                ->whereIn('day_key', $days)
                ->orderByDesc('day_key')
                ->get()
                ->map(fn (BinaryDailyPayout $p) => [
                    'day_key' => $p->day_key?->format('Y-m-d') ?? (string) $p->day_key,
                    'matched_pv' => (string) $p->matched_pv,
                    'daily_bonus_bob' => (string) $p->daily_bonus_bob,
                    'rate' => (string) (($p->meta ?? [])['rate'] ?? config('mlm.binary.hybrid_daily.rate', '0.21')),
                    'bob_per_pv' => (string) (($p->meta ?? [])['bob_per_pv'] ?? $bobPerPv),
                ])
                ->values()
                ->all();

            $weekly = BinaryWeeklyBonus::query()
                ->where('user_id', $userId)
                ->orderByDesc('week_key')
                ->limit(8)
                ->get()
                ->map(fn (BinaryWeeklyBonus $w) => [
                    'week_key' => (string) $w->week_key,
                    'weekly_bonus_bob' => (string) $w->weekly_bonus_bob,
                    'paid_weekly_bonus_bob' => (string) $w->paid_weekly_bonus_bob,
                    'accumulated_unpaid_bob' => (string) $w->accumulated_unpaid_bob,
                    'final_accumulated_bob' => (string) $w->final_accumulated_bob,
                    'month_key' => (string) $w->month_key,
                    'month_penalty_applied' => (bool) $w->month_penalty_applied,
                    'meta' => $w->meta,
                ])
                ->values()
                ->all();

            $binaryHybrid = [
                'enabled' => true,
                'mode' => 'hybrid_daily',
                'days_window' => 14,
                'weeks_window' => 8,
                'daily' => $daily,
                'weekly' => $weekly,
                'config' => [
                    'rate' => (string) config('mlm.binary.hybrid_daily.rate', '0.21'),
                    'weekly_cap_usd' => (string) config('mlm.binary.hybrid_daily.weekly_cap_usd', '2500'),
                    'weekly_cap_bob_override' => (string) config('mlm.binary.hybrid_daily.weekly_cap_bob', ''),
                    'month_penalty' => (string) config('mlm.binary.hybrid_daily.month_penalty', '0.10'),
                    'bob_per_usd' => (string) config('mlm.auto_okm.bob_per_usd', '7'),
                    'bob_per_pv' => (string) $bobPerPv,
                ],
            ];
        }

        return response()->json([
            'summary' => [
                'total_accrued' => $total,
            ],
            'exchange' => [
                'label' => 'Tipo de cambio interno',
                'note' => '1 PV = 7 Bs',
                'bob_per_pv' => $bobPerPv,
            ],
            'bir_by_level' => $birByLevel,
            'bir_percentages' => config('mlm.bir.schedule', []),
            'items' => $items,
            'binary_hybrid' => $binaryHybrid,
        ]);
    }
}
