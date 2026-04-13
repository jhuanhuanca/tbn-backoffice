<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BinaryLegVolumeWeekly;
use App\Models\CommissionEvent;
use App\Models\Order;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $pendingWithdrawals = Withdrawal::query()->where('estado', Withdrawal::ESTADO_PENDIENTE)->count();

        $ordersRevenue = (string) Order::query()
            ->where('estado', 'completado')
            ->sum('total');

        $commissionsPaid = (string) CommissionEvent::query()->sum('amount');

        $usersNewMonth = User::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $periodKey = now()->format('Y-m');
        $binaryVolumeMonth = (string) BinaryLegVolumeWeekly::query()
            ->where('week_key', $periodKey)
            ->sum('volume_pv');

        $salesLast6Months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->copy()->subMonths($i)->startOfMonth();
            $end = $m->copy()->endOfMonth();
            $label = $m->format('Y-m');
            $sum = Order::query()
                ->where('estado', 'completado')
                ->whereBetween('created_at', [$m, $end])
                ->sum('total');
            $cnt = Order::query()
                ->where('estado', 'completado')
                ->whereBetween('created_at', [$m, $end])
                ->count();
            $salesLast6Months[] = [
                'month' => $label,
                'total' => (string) $sum,
                'orders' => $cnt,
            ];
        }

        $commissionsByType = CommissionEvent::query()
            ->selectRaw('type, COALESCE(SUM(amount),0) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->map(fn ($v) => (string) $v)
            ->all();

        $rankDistribution = User::query()
            ->select(['ranks.name', 'ranks.slug', DB::raw('COUNT(users.id) as total')])
            ->leftJoin('ranks', 'users.rank_id', '=', 'ranks.id')
            ->groupBy('ranks.id', 'ranks.name', 'ranks.slug')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'slug' => $r->slug ?? 'sin_rango',
                'name' => $r->name ?? 'Sin rango',
                'total' => (int) $r->total,
            ])
            ->values()
            ->all();

        $topMembers = User::query()
            ->with(['rank:id,name'])
            ->orderByDesc('monthly_qualifying_pv')
            ->limit(10)
            ->get(['id', 'name', 'member_code', 'monthly_qualifying_pv', 'rank_id'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'member_code' => $u->member_code,
                'monthly_qualifying_pv' => (string) ($u->monthly_qualifying_pv ?? '0'),
                'rank_name' => $u->rank?->name ?? '—',
            ])
            ->values()
            ->all();

        return response()->json([
            'users_total' => User::query()->count(),
            'withdrawals_pending' => $pendingWithdrawals,
            'orders_today' => Order::query()
                ->whereDate('created_at', now()->toDateString())
                ->count(),
            'orders_revenue_total' => $ordersRevenue,
            'commissions_paid_total' => $commissionsPaid,
            'users_new_this_month' => $usersNewMonth,
            'binary_volume_current_period' => $binaryVolumeMonth,
            'binary_period_key' => $periodKey,
            'charts' => [
                'sales_last_6_months' => $salesLast6Months,
                'commissions_by_type' => $commissionsByType,
                'rank_distribution' => $rankDistribution,
            ],
            'top_members' => $topMembers,
        ]);
    }
}
