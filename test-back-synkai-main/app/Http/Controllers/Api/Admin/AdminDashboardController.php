<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Withdrawal;
class AdminDashboardController extends Controller
{
    public function index()
    {
        $pendingWithdrawals = Withdrawal::query()->where('estado', Withdrawal::ESTADO_PENDIENTE)->count();

        return response()->json([
            'users_total' => User::query()->count(),
            'withdrawals_pending' => $pendingWithdrawals,
            'orders_today' => Order::query()
                ->whereDate('created_at', now()->toDateString())
                ->count(),
        ]);
    }
}
