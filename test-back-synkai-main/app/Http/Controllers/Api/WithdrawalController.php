<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $rows = Withdrawal::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 25));

        return response()->json($rows);
    }

    public function store(Request $request, WithdrawalService $withdrawalService)
    {
        $data = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'notas' => 'nullable|string|max:2000',
        ]);

        try {
            $w = $withdrawalService->solicitar(
                $request->user(),
                bcadd((string) $data['monto'], '0', 2),
                $data['notas'] ?? null
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($w, 201);
    }
}
