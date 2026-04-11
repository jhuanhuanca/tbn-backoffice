<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BinaryService;
use Illuminate\Http\Request;

class BinaryPlacementSelfController extends Controller
{
    /**
     * Colocación automática en el primer hueco libre bajo el patrocinador (tras activación pagada).
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

        $placement = $binaryService->placeUserInFirstFreeSlot($user);
        if (! $placement) {
            return response()->json([
                'message' => 'No hay posición libre bajo tu patrocinador. Contacta a soporte.',
            ], 422);
        }

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
