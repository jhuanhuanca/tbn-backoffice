<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'package_id',
        'cantidad',
        'precio_unitario',
        'precio_total',
        'pv_points',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'precio_total' => 'decimal:2',
            'pv_points' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
