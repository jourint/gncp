<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Material extends Model
{
    protected $fillable = [
        'name',
        'material_type_id',
        'color_id',
        'is_active',
        //    'stock_quantity'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'material_type_id' => 'integer',
            'color_id' => 'integer',
            'stock_quantity' => 'decimal:2',
        ];
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class, 'material_type_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class)->withDefault(['name' => 'Цвет не назначен']);
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(MaterialMovement::class, 'movable');
    }

    public function addStock(float $amount): void
    {
        if ($amount <= 0) return;
        $this->increment('stock_quantity', $amount);
    }

    public function deductStock(float $amount): void
    {
        if ($amount <= 0) return;
        if ($this->stock_quantity < $amount) {
            throw new \Exception("Недостаточно материала на складе");
        }
        $this->decrement('stock_quantity', $amount);
    }

    public function getFullNameAttribute()
    {
        $colorName = $this->color?->name;
        $baseName = $this->name;
        return $baseName . ($colorName ? " ({$colorName})" : "");
    }
}
