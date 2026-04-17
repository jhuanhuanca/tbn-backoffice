<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BinaryService;
use Illuminate\Http\Request;

class BinaryPlacementSelfController extends Controller
{
    /**
     * Colocación binaria tras activación pagada.
     * Opciones: left | right | auto.
     */
    public function store(Request $request, BinaryService $binaryService)
    {
        $data = $request->validate([
            'placement' => 'nullable|string|in:left,right,auto',
        ]);

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

        // Si el usuario ya definió preferencia al registrarse, usarla por defecto.
        $placementPref = $data['placement'] ?? ($user->preferred_binary_leg ?: 'auto');
        if ($placementPref === 'left') {
            $placement = $binaryService->placeUserDirectUnderSponsor($user, \App\Models\BinaryPlacement::LEG_LEFT);
            if (! $placement) {
                return response()->json(['message' => 'La pierna izquierda de tu patrocinador ya está ocupada.'], 422);
            }
        } elseif ($placementPref === 'right') {
            $placement = $binaryService->placeUserDirectUnderSponsor($user, \App\Models\BinaryPlacement::LEG_RIGHT);
            if (! $placement) {
                return response()->json(['message' => 'La pierna derecha de tu patrocinador ya está ocupada.'], 422);
            }
        } else {
            $placement = $binaryService->placeUserInFirstFreeSlot($user);
        }
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
            /** @var Order $o */
            $o->load(['items.product', 'items.package']);
            $binaryService->acumularVolumenBinarioPorPedido($o);
        }

        return response()->json($placement, 201);
    }
}
