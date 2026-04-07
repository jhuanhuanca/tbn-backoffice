<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function balance(Request $request, WalletService $walletService)
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'available' => $walletService->saldoDisponible($user),
        ]);
    }

    public function transactions(Request $request, WalletService $walletService)
    {
        $wallet = $walletService->ensureWallet($request->user());

        $rows = WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get(['id', 'type', 'amount', 'reference', 'description', 'created_at']);

        return response()->json(['data' => $rows]);
    }
}
