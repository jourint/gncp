<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'started_at',
        'status',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'status' => OrderStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }
}
