<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoeSoleItem extends Model
{
    protected $fillable = [
        'shoe_sole_id',
        'size_id',
        'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
        ];
    }

    public function shoeSole(): BelongsTo
    {
        return $this->belongsTo(ShoeSole::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class)->withDefault([
            'name' => 'Размер не назначен',
        ]);
    }

    public function addStock(int $amount): void
    {
        if ($amount <= 0) return;
        $this->increment('stock_quantity', $amount);
    }

    public function deductStock(int $amount): void
    {
        if ($amount <= 0) return;
        if ($this->stock_quantity < $amount) {
            throw new \Exception("Недостаточно материала на складе");
        }
        $this->decrement('stock_quantity', $amount);
    }
}
