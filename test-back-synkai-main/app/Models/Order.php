<?php

namespace App\Models;

use App\Events\OrderCompleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Order extends Model
{
    protected $fillable = [
        'uuid',
        'reference',
        'user_id',
        'tipo',
        'cantidad',
        'total',
        'total_pv',
        'estado',
        'completed_at',
        'payment_method',
        'payment_confirmed_at',
        'payment_confirmed_by',
        'payment_admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'total_pv' => 'decimal:2',
            'completed_at' => 'datetime',
            'payment_confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_confirmed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function commissionRun(): HasOne
    {
        return $this->hasOne(OrderCommissionRun::class);
    }

    /**
     * PV comisionable del pedido.
     * Nota: en `order_items`, `pv_points` ya es el PV total de la línea (unitario × cantidad), igual que al crear el pedido.
     */
    public function commissionablePvTotal(): string
    {
        $this->loadMissing('items');
        $sum = '0';
        foreach ($this->items as $item) {
            $comm = $item->commissionable_pv !== null && $item->commissionable_pv !== ''
                ? (string) $item->commissionable_pv
                : '';
            // Defensa: algunos ítems legacy guardaron "0" en commissionable_pv (ej. líneas fundador).
            // Si no es positivo, usamos pv_points.
            if ($comm !== '' && is_numeric($comm) && bccomp($comm, '0', 4) === 1) {
                $sum = bcadd($sum, $comm, 4);
            } else {
                $sum = bcadd($sum, (string) $item->pv_points, 4);
            }
        }

        return bcadd($sum, '0', 2);
    }

    /**
     * Suma PV comisionable de pedidos completados en un rango de fechas (para calificación mensual / onboarding).
     */
    public static function sumCommissionablePvForUserBetween(int $userId, Carbon $start, Carbon $end): string
    {
        $sum = '0';
        /** @var \Illuminate\Database\Eloquent\Collection<int, self> $orders */
        $orders = static::query()
            ->where('user_id', $userId)
            ->where('estado', 'completado')
            ->whereBetween('completed_at', [$start, $end])
            ->with('items')
            ->get();

        foreach ($orders as $order) {
            /** @var self $order */
            $sum = bcadd($sum, $order->commissionablePvTotal(), 2);
        }

        return bcadd($sum, '0', 2);
    }

    public function markCompleted(): void
    {
        if ($this->estado === 'completado') {
            return;
        }
        if (! in_array($this->estado, ['pendiente', 'pendiente_pago'], true)) {
            return;
        }
        $this->estado = 'completado';
        $this->completed_at = now();
        $this->save();
        $order = $this->fresh(['items.product', 'items.package', 'user.sponsor', 'user.rank']);
        if ($order) {
            OrderCompleted::dispatch($order);
        }
    }
}
