<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Emisión idempotente de factura a partir de un pedido completado.
 */
class InvoiceService
{
    public function emitirDesdeOrdenSiNoExiste(Order $order): ?Invoice
    {
        $existing = Invoice::query()->where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        $order->loadMissing(['user', 'items.product', 'items.package']);

        return DB::transaction(function () use ($order) {
            $taxRatePct = (string) config('mlm.invoice.default_tax_rate', '0');
            $sub = '0';
            foreach ($order->items as $line) {
                $sub = bcadd($sub, (string) $line->precio_total, 2);
            }

            $tax = '0';
            if (bccomp($taxRatePct, '0', 4) === 1) {
                $tax = bcmul($sub, bcdiv($taxRatePct, '100', 6), 2);
            }
            $total = bcadd($sub, $tax, 2);

            $buyer = $order->user;

            $invoice = Invoice::query()->create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'numero_factura' => 'INV-'.$order->id.'-'.substr((string) $order->uuid, 0, 8),
                'issuer_nit' => env('MLM_INVOICE_ISSUER_NIT'),
                'issuer_business_name' => env('MLM_INVOICE_ISSUER_NAME'),
                'customer_document' => $buyer?->document_id,
                'customer_business_name' => $buyer?->name,
                'authorization_code' => env('MLM_INVOICE_AUTH_CODE'),
                'cuf' => null,
                'electronic_invoice_status' => 'pending_integration',
                'fecha_emision' => now()->toDateString(),
                'sub_total' => $sub,
                'tax_amount' => $tax,
                'tax_rate' => $taxRatePct,
                'total' => $total,
                'impuestos' => bccomp($tax, '0', 2) === 1 ? 'Tasa '.$taxRatePct.'%' : null,
                'estado' => 'emitida',
            ]);

            foreach ($order->items as $line) {
                $desc = $line->product?->name ?? $line->package?->name ?? 'Ítem';
                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $line->product_id,
                    'package_id' => $line->package_id,
                    'descripcion' => $desc,
                    'cantidad' => (int) $line->cantidad,
                    'unit_precio' => (string) $line->precio_unitario,
                    'total_precio' => (string) $line->precio_total,
                ]);
            }

            return $invoice->fresh('items');
        });
    }
}
