<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoeModel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'shoe_type_id',
        'shoe_insole_id',
        'price_coeff_cutting',
        'price_coeff_sewing',
        'price_coeff_shoemaker',
        'available_sizes',
        'workflows',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'available_sizes' => 'array',
            'workflows' => 'array',
            'price_coeff_cutting' => 'decimal:2',
            'price_coeff_sewing' => 'decimal:2',
            'price_coeff_shoemaker' => 'decimal:2',
        ];
    }

    public function shoeType(): BelongsTo
    {
        return $this->belongsTo(ShoeType::class);
    }

    public function techCards(): HasMany
    {
        return $this->hasMany(ShoeTechCard::class);
    }

    public function shoeInsole(): BelongsTo
    {
        return $this->belongsTo(ShoeInsole::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function puff(): BelongsTo
    {
        return $this->belongsTo(Puff::class);
    }

    /**
     * Расчет полной стоимости работ по модели
     */
    public function getCalculatedPricesAttribute(): array
    {
        $base = $this->shoeType;

        if (!$base) {
            return [
                'cutting' => 0,
                'sewing' => 0,
                'shoemaker' => 0,
                'total' => 0
            ];
        }

        $prices = [
            'cutting' => $base->price_cutting * $this->price_coeff_cutting,
            'sewing' => $base->price_sewing * $this->price_coeff_sewing,
            'shoemaker' => $base->price_shoemaker * $this->price_coeff_shoemaker,
        ];

        $prices['total'] = array_sum($prices);

        return $prices;
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->shoeType?->name})";
    }
}
