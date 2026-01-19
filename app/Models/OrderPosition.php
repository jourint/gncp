<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPosition extends Model
{
    protected $fillable = [
        'order_id',
        'shoe_tech_card_id',
        'size_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shoeTechCard(): BelongsTo
    {
        return $this->belongsTo(ShoeTechCard::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function getActualSoleAttribute()
    {
        return ShoeSole::where('name', $this->shoeTechCard->shoeSole->name)
            ->where('size_id', $this->size_id)
            ->first();
    }
}
