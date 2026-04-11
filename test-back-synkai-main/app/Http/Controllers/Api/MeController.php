<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BinaryLegVolumeWeekly;
use App\Models\BinaryPlacement;
use App\Models\BinaryWeeklyCarry;
use App\Models\CommissionEvent;
use App\Models\User;
use App\Services\BinaryService;
use App\Services\MlmBonusProgressService;
use App\Services\WalletService;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json(
            $request->user()->loadMissing('rank', 'sponsor', 'registrationPackage')
        );
    }

    public function dashboard(Request $request, WalletService $walletService, BinaryService $binaryService, MlmBonusProgressService $bonusProgress)
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('rank', 'sponsor');

        $weekKey = $binaryService->weekKey(now());
        $prevKey = $binaryService->previousWeekKey($weekKey);

        $leftPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $weekKey)
            ->where('leg_position', 'left')
            ->value('volume_pv') ?? '0';

        $rightPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $weekKey)
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
            ],
            'rank' => $user->rank ? ['id' => $user->rank->id, 'name' => $user->rank->name, 'slug' => $user->rank->slug] : null,
            'sponsor' => $user->sponsor ? [
                'name' => $user->sponsor->name,
                'referral_code' => $user->sponsor->referral_code,
            ] : null,
            'wallet' => [
                'available' => $walletService->saldoDisponible($user),
            ],
            'referrals_direct_count' => $directCount,
            'binary_week' => [
                'week_key' => $weekKey,
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
                'pierna' => $leg,
                'fecha_alta' => $u->created_at?->format('d/m/Y'),
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
     * Vista de árbol binario (2 niveles de profundidad) + KPIs para el front.
     */
    public function binaryTree(Request $request, BinaryService $binaryService, WalletService $walletService)
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('rank');
        $weekKey = $binaryService->weekKey(now());

        $leftPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $weekKey)
            ->where('leg_position', 'left')
            ->value('volume_pv') ?? '0';

        $rightPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $weekKey)
            ->where('leg_position', 'right')
            ->value('volume_pv') ?? '0';

        $binaryEarningsWeek = (string) CommissionEvent::query()
            ->where('beneficiary_user_id', $user->id)
            ->where('type', 'binary')
            ->where('period_key', $weekKey)
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
            'week_key' => $weekKey,
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
                return [
                    'id' => $e->id,
                    'type' => $e->type,
                    'type_label' => match ($e->type) {
                        'bir' => 'Bono inicio rápido',
                        'binary' => 'Bono binario',
                        'residual' => 'Residual',
                        'leadership' => 'Liderazgo',
                        default => strtoupper($e->type),
                    },
                    'amount' => $e->amount,
                    'currency' => $e->currency,
                    'level' => $e->level,
                    'period_key' => $e->period_key,
                    'created_at' => $e->created_at?->toIso8601String(),
                    'status' => $e->commission?->status ?? 'accrued',
                ];
            });

        return response()->json([
            'summary' => [
                'total_accrued' => $total,
            ],
            'bir_by_level' => $birByLevel,
            'bir_percentages' => config('mlm.bir.schedule', []),
            'items' => $items,
        ]);
    }
}
