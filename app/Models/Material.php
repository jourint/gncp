<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'stock_quantity' => 'float',
        ];
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class, 'material_type_id')->withDefault([
            'name' => 'Тип материала не назначен',
        ]);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class); // ->withDefault([ 'name' => 'Цвет не назначен',]);
    }

    public function texture(): BelongsTo
    {
        return $this->belongsTo(MaterialTexture::class, 'texture_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id')->withDefault([
            'name' => 'Ед. изм. не назначена',
        ]);
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

    public function movements()
    {
        return $this->morphMany(MaterialMovement::class, 'movable');
    }

    public function getDisplayNameAttribute()
    {
        $texture = $this->texture ? " ({$this->texture->name})" : "";

        // Результат: Кожа (флотар) красный
        return "{$this->materialType->name}{$texture} {$this->color->name}";
    }
}
