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
        'material_texture_id',
        'shoe_sole_id',
        'shoe_insole_id',
        'is_active',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($techCard) {
            // Загружаем модель вместе с её типом и цветом/текстурой
            $techCard->loadMissing(['shoeModel.shoeType', 'color', 'texture']);

            // Название модели: "Эстер"
            $modelName = $techCard->shoeModel?->name ?? 'Unknown Model';

            // Тип обуви в скобках: "Ботинки"
            $typeName = $techCard->shoeModel?->shoeType?->name;
            $typeString = $typeName ? " ({$typeName})" : "";

            // Цвет и текстура: "Красный (Кожа)"
            $colorName = $techCard->color?->name ?? 'Unknown Color';
            $textureName = $techCard->texture?->name ?? 'Unknown Texture';

            // Собираем всё вместе: "Эстер (Ботинки) / Красный (Кожа)"
            $techCard->name = "{$modelName}{$typeString} / {$colorName} ({$textureName})";
        });
    }

    public function texture(): BelongsTo
    {
        return $this->belongsTo(MaterialTexture::class, 'material_texture_id');
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

    public function shoeInsole(): BelongsTo
    {
        return $this->belongsTo(ShoeInsole::class);
    }

    public function techCardMaterials(): HasMany
    {
        return $this->hasMany(TechCardMaterial::class);
    }
}
