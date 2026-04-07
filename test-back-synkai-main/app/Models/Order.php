<?php

namespace App\Models;

use App\Events\OrderCompleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'total_pv' => 'decimal:2',
            'completed_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function markCompleted(): void
    {
        if ($this->estado === 'completado') {
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
