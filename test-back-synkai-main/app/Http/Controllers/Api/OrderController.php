<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['items.product', 'items.package'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return response()->json($orders);
    }

    /**
     * Crear pedido y marcarlo completado (dispara comisiones vía evento + cola).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo' => 'required|string|in:producto,paquete,mixto',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.package_id' => 'nullable|exists:packages,id',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        $buyer = $request->user();
        if (! $buyer->canAccessAdminPanel() && $buyer->activation_paid_at === null) {
            $hasPackage = false;
            foreach ($data['items'] as $row) {
                if (! empty($row['package_id'])) {
                    $hasPackage = true;
                    break;
                }
            }
            if (! $hasPackage) {
                return response()->json([
                    'message' => 'Para activar tu cuenta el pedido debe incluir al menos un paquete.',
                ], 422);
            }
        }

        $buyerId = $request->user()->id;

        $order = DB::transaction(function () use ($data, $buyerId) {
            $total = '0';
            $totalPv = '0';

            $order = Order::query()->create([
                'user_id' => $buyerId,
                'tipo' => $data['tipo'],
                'cantidad' => 0,
                'total' => 0,
                'total_pv' => 0,
                'estado' => 'pendiente',
            ]);

            $qtySum = 0;

            foreach ($data['items'] as $row) {
                if (empty($row['product_id']) && empty($row['package_id'])) {
                    throw new \InvalidArgumentException('Cada ítem debe tener product_id o package_id');
                }

                $qty = (int) $row['cantidad'];
                $qtySum += $qty;

                if (! empty($row['package_id'])) {
                    $pkg = Package::query()->findOrFail($row['package_id']);
                    $unit = (string) $pkg->price;
                    $line = bcmul($unit, (string) $qty, 2);
                    $pvLine = bcmul((string) $pkg->pv_points, (string) $qty, 2);

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => null,
                        'package_id' => $pkg->id,
                        'cantidad' => $qty,
                        'precio_unitario' => $unit,
                        'precio_total' => $line,
                        'pv_points' => $pvLine,
                    ]);

                    $total = bcadd($total, $line, 2);
                    $totalPv = bcadd($totalPv, $pvLine, 2);
                } else {
                    $prod = Product::query()->findOrFail($row['product_id']);
                    $unit = (string) $prod->price;
                    $line = bcmul($unit, (string) $qty, 2);
                    $pvLine = bcmul((string) $prod->pv_points, (string) $qty, 2);

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $prod->id,
                        'package_id' => null,
                        'cantidad' => $qty,
                        'precio_unitario' => $unit,
                        'precio_total' => $line,
                        'pv_points' => $pvLine,
                    ]);

                    $total = bcadd($total, $line, 2);
                    $totalPv = bcadd($totalPv, $pvLine, 2);
                }
            }

            $order->update([
                'cantidad' => $qtySum,
                'total' => $total,
                'total_pv' => $totalPv,
            ]);

            return $order->fresh(['items']);
        });

        $order->markCompleted();

        $order->load(['items.package', 'items.product']);
        /** @var User $buyer */
        $buyer = $request->user()->fresh();
        if (! $buyer->canAccessAdminPanel()) {
            foreach ($order->items as $item) {
                if ($item->package_id && $buyer->activation_paid_at === null) {
                    $buyer->forceFill(['activation_paid_at' => now()])->save();
                    break;
                }
            }
        }

        return $order->fresh(['items']);
    }
}
