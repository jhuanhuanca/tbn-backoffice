<?php

namespace App\Services;

use App\Jobs\ProcessOrderMlmAccrualsJob;
use App\Models\Order;

/**
 * Orquestación post-compra: factura + encolado de acreditaciones MLM (sin lógica en controladores).
 */
class OrderService
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function procesarOrdenFinalizada(Order $order): void
    {
        $order->loadMissing(['items.product', 'items.package', 'user']);
        $this->invoiceService->emitirDesdeOrdenSiNoExiste($order);
        ProcessOrderMlmAccrualsJob::dispatch($order->id);
    }
}
