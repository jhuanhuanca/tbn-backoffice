<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BinaryPlacement;
use App\Models\Order;
use App\Services\BinaryService;
use Illuminate\Http\Request;

class BinaryPlacementSelfController extends Controller
{
    /**
     * El socio elige pierna izquierda o derecha bajo su patrocinador (tras activación pagada).
     */
    public function store(Request $request, BinaryService $binaryService)
    {
        $user = $request->user();
        if ($user->canAccessAdminPanel()) {
            return response()->json([
                'message' => 'Los administradores gestionan colocaciones desde el panel admin.',
            ], 422);
        }
        if (! $user->sponsor_id) {
            return response()->json(['message' => 'No tienes patrocinador asignado.'], 422);
        }
        if (! $user->activation_paid_at) {
            return response()->json(['message' => 'Completa primero el pago de activación (paquete).'], 422);
        }
        if ($user->binaryPlacement()->exists()) {
            return response()->json(['message' => 'Ya tienes colocación binaria.'], 422);
        }

        $data = $request->validate([
            'leg' => 'required|in:left,right',
        ]);

        $sponsorId = (int) $user->sponsor_id;
        $occupied = BinaryPlacement::query()
            ->where('parent_user_id', $sponsorId)
            ->where('leg_position', $data['leg'])
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($occupied) {
            return response()->json(['message' => 'Esa pierna ya está ocupada. Elige la otra.'], 422);
        }

        $placement = BinaryPlacement::query()->create([
            'user_id' => $user->id,
            'parent_user_id' => $sponsorId,
            'leg_position' => $data['leg'],
        ]);

        $binaryService->olvidarCacheArbol($user->id);
        $binaryService->olvidarCacheArbol($sponsorId);

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->where('estado', 'completado')
            ->orderBy('id')
            ->get();

        foreach ($orders as $o) {
            $binaryService->acumularVolumenBinarioPorPedido($o->fresh(['items.product', 'items.package']));
        }

        return response()->json($placement, 201);
    }
}
