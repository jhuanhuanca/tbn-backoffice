<?php

namespace App\Services;

use App\Events\UserActivated;
use App\Models\BinaryLegVolumeWeekly;
use App\Models\BinaryPlacement;
use App\Models\Order;
use App\Models\User;
use App\Models\UserMonthlyRankSnapshot;
use Carbon\Carbon;

class UserQualificationService
{
    public function __construct(
        protected RankService $rankService,
        protected BinaryService $binaryService,
        protected LeadershipStreakService $leadershipStreakService
    ) {}

    public function actualizarCalificacionMensual(User $user): void
    {
        $month = Carbon::now()->format('Y-m');
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $pv = Order::sumCommissionablePvForUserBetween((int) $user->id, $start, $end);
        $threshold = (string) config('mlm.monthly_activation_pv', '200');
        $qualified = bccomp($pv, $threshold, 2) >= 0;

        $was = (bool) $user->is_mlm_qualified;

        $user->forceFill([
            'last_qualification_month' => $month,
            'monthly_qualifying_pv' => $pv,
            'is_mlm_qualified' => $qualified,
            'account_status' => 'active',
        ])->save();

        $this->rankService->sincronizarRangoPorCalificacion($user->fresh());

        $userFresh = $user->fresh(['rank']);
        if ($userFresh) {
            $this->persistirSnapshotMensual($userFresh, $month, $pv);
        }

        if (! $was && $qualified) {
            UserActivated::dispatch($user->fresh(), $month);
        }
    }

    protected function persistirSnapshotMensual(User $user, string $month, string $qualifyingPv): void
    {
        $periodKey = $this->binaryService->volumePeriodKey(now());
        $leftPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $periodKey)
            ->where('leg_position', BinaryPlacement::LEG_LEFT)
            ->value('volume_pv') ?? '0';
        $rightPv = (string) BinaryLegVolumeWeekly::query()
            ->where('parent_user_id', $user->id)
            ->where('week_key', $periodKey)
            ->where('leg_position', BinaryPlacement::LEG_RIGHT)
            ->value('volume_pv') ?? '0';

        $streak = $this->leadershipStreakService->mesesConsecutivosMismoRango($user, $month);

        UserMonthlyRankSnapshot::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'month_key' => $month,
            ],
            [
                'rank_id' => $user->rank_id,
                'rank_slug' => $user->rank?->slug,
                'qualifying_pv' => $qualifyingPv,
                'leadership_streak_months' => $streak,
                'binary_left_pv' => (float) ($leftPv ?? 0),
                'binary_right_pv' => (float) ($rightPv ?? 0),
                'meta' => [
                    'binary_volume_period_key' => $periodKey,
                    'binary_volume_period' => $this->binaryService->isMonthlyBinaryVolume() ? 'monthly' : 'weekly',
                ],
            ]
        );
    }
}
