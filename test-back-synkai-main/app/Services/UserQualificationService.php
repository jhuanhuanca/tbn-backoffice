<?php

namespace App\Services;

use App\Events\UserActivated;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class UserQualificationService
{
    public function actualizarCalificacionMensual(User $user): void
    {
        $month = Carbon::now()->format('Y-m');
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $pv = (string) Order::query()
            ->where('user_id', $user->id)
            ->where('estado', 'completado')
            ->whereBetween('completed_at', [$start, $end])
            ->sum('total_pv');

        $pv = bcadd($pv, '0', 2);
        $threshold = (string) config('mlm.monthly_activation_pv', '200');
        $qualified = bccomp($pv, $threshold, 2) >= 0;

        $was = (bool) $user->is_mlm_qualified;

        $user->forceFill([
            'last_qualification_month' => $month,
            'monthly_qualifying_pv' => $pv,
            'is_mlm_qualified' => $qualified,
            'account_status' => $qualified ? 'active' : $user->account_status,
        ])->save();

        if (! $was && $qualified) {
            UserActivated::dispatch($user->fresh(), $month);
        }
    }
}
