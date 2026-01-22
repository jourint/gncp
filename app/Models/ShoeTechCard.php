<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoeTechCard extends Model
{
    protected $fillable = [
        'name',
        'shoe_model_id',
        'color_id',
        'shoe_sole_id',
        'is_active',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'image_path' => 'string',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($techCard) {
            $techCard->loadMissing(['shoeModel.shoeType', 'color']);
            $techCard->name = $techCard->fullName;
        });
    }

    public function getFullNameAttribute()
    {
        // Результат: Эстер (Ботинки) / Красный (+подкладка из заказа)
        $baseName = $this->name;
        $shoeTypeName = $this->shoeModel?->shoeType?->name ?? 'Unknown Type';
        $colorName = $this->color?->name ?? 'Unknown Color';
        return "{$baseName} ({$shoeTypeName}) / {$colorName}";
    }

    public function shoeModel(): BelongsTo
    {
        return $this->belongsTo(ShoeModel::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(TechCardMaterial::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function shoeSole(): BelongsTo
    {
        return $this->belongsTo(ShoeSole::class);
    }

    public function techCardMaterials(): HasMany
    {
        return $this->hasMany(TechCardMaterial::class);
    }
}
