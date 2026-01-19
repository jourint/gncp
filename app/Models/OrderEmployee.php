<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEmployee extends Model
{
    protected $fillable = [
        'order_id',
        'order_position_id',
        'employee_id',
        'quantity',
        'price_per_pair',
        'is_paid',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price_per_pair' => 'float',
            'is_paid' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::updating(function ($item) {
            // Если запись оплачена — отменяем обновление
            if ($item->is_paid) {
                return false;
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getPriceAttribute()
    {
        return $this->price_per_pair * $this->quantity;
    }

    public function getIsPaidLabelAttribute()
    {
        return $this->isPaid ? 'Да' : 'Нет';
    }
}
