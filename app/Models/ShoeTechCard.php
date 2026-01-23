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
        'material_id',
        'material_two_id',
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
        // Результат: Эстер (Ботинки) / Кожа (Красный) (+подкладка из заказа)
        $baseName = $this->shoeModel?->fullName ?? 'Unknown Model';
        $materialName = $this->material?->fullName ?? 'Unknown Material';
        return "{$baseName} / {$materialName}";
    }

    public function shoeModel(): BelongsTo
    {
        return $this->belongsTo(ShoeModel::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function shoeSole(): BelongsTo
    {
        return $this->belongsTo(ShoeSole::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function materialTwo(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_two_id');
    }

    public function techCardMaterials(): HasMany
    {
        return $this->hasMany(TechCardMaterial::class);
    }
}
