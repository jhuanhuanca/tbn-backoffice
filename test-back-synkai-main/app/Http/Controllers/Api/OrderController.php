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
        $q = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['items.product', 'items.package'])
            ->orderByDesc('created_at');

        $estado = $request->query('estado');
        if (is_string($estado) && $estado !== '') {
            $q->where('estado', $estado);
        }
        $tipo = $request->query('tipo');
        if (is_string($tipo) && $tipo !== '') {
            $q->where('tipo', $tipo);
        }

        $orders = $q->paginate(25);

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
            'payment_settlement' => 'nullable|string|in:immediate,manual',
            'payment_method' => 'nullable|string|max:32',
        ]);

        $immediate = ($data['payment_settlement'] ?? 'immediate') === 'immediate';

        $buyer = $request->user();

        if ($buyer->isPreferredCustomer()) {
            foreach ($data['items'] as $row) {
                if (! empty($row['package_id'])) {
                    return response()->json([
                        'message' => 'Los clientes preferentes solo pueden comprar productos (no paquetes de socio).',
                    ], 422);
                }
            }
            if (($data['tipo'] ?? '') !== 'producto') {
                return response()->json([
                    'message' => 'Los clientes preferentes solo realizan pedidos de tipo producto.',
                ], 422);
            }
        }

        if (! $buyer->canAccessAdminPanel() && ! $buyer->isPreferredCustomer() && $buyer->activation_paid_at === null) {
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

            // Evitar confirmaciones reiteradas: si ya existe una activación pendiente de pago, reusar y no crear otra.
            if (($data['tipo'] ?? '') === 'paquete' && ! $immediate) {
                $existing = Order::query()
                    ->where('user_id', $buyer->id)
                    ->where('estado', 'pendiente_pago')
                    ->where('tipo', 'paquete')
                    ->orderByDesc('created_at')
                    ->with(['items.package'])
                    ->first();

                if ($existing) {
                    return response()->json([
                        'message' => 'Ya tienes un pedido de activación pendiente de confirmación. Espera la validación de administración.',
                        'order' => $existing,
                    ], 409);
                }
            }
        }

        $buyerId = $request->user()->id;

        $order = DB::transaction(function () use ($data, $buyerId, $immediate, $buyer) {
            $total = '0';
            $totalPv = '0';

            $order = Order::query()->create([
                'user_id' => $buyerId,
                'tipo' => $data['tipo'],
                'cantidad' => 0,
                'total' => 0,
                'total_pv' => 0,
                'estado' => $immediate ? 'pendiente' : 'pendiente_pago',
                'payment_method' => $data['payment_method'] ?? ($immediate ? 'online' : 'pendiente'),
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
                        'meta' => null,
                    ]);

                    $total = bcadd($total, $line, 2);
                    $totalPv = bcadd($totalPv, $pvLine, 2);
                } else {
                    $prod = Product::query()->findOrFail($row['product_id']);
                    if ($buyer->isPreferredCustomer()) {
                        $clienteUnit = $prod->price_cliente_preferente !== null
                            ? (string) $prod->price_cliente_preferente
                            : (string) $prod->price;
                        $socioUnit = bcadd((string) $prod->price, '0', 2);
                        $unit = bcadd($clienteUnit, '0', 2);
                        $meta = [
                            'preferred_customer_line' => true,
                            'precio_socio_unit' => $socioUnit,
                            'precio_cliente_unit' => $unit,
                        ];
                    } else {
                        $unit = (string) $prod->price;
                        $meta = null;
                    }
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
                        'meta' => $meta,
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

        if ($immediate) {
            $order->markCompleted();

            $order->load(['items.package', 'items.product']);
            /** @var User $buyer */
            $buyer = $request->user()->fresh();
            if (! $buyer->canAccessAdminPanel() && ! $buyer->isPreferredCustomer()) {
                foreach ($order->items as $item) {
                    if ($item->package_id && $buyer->activation_paid_at === null) {
                        $buyer->forceFill([
                            'activation_paid_at' => now(),
                            'account_status' => 'active',
                        ])->save();
                        break;
                    }
                }
            }
        } else {
            $order->load(['items.package', 'items.product']);
        }

        return $order->fresh(['items']);
    }
}
