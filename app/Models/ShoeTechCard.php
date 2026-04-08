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
    protected $appends = ['fullName'];

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
            // 1. Принудительно подгружаем свежие названия для формирования имени, 
            // даже если связи еще не были инициализированы или устарели.

            $model = \App\Models\ShoeModel::with('shoeType')->find($techCard->shoe_model_id);
            $color = \App\Models\Color::find($techCard->color_id);
            $material = \App\Models\Material::find($techCard->material_id);

            $modelName = $model?->name ?? 'Неизвестная модель';
            $typeName = $model?->shoeType?->name ? "({$model->shoeType->name})" : "";
            $matName = $material?->name ?? 'Без материала';
            $colorName = $color?->name ? "({$color->name})" : "";

            // Формируем результат: Эстер (Ботинки) / Кожа (Красный)
            $techCard->name = "{$modelName} {$typeName} / {$matName} {$colorName}";
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
        return $this->hasMany(TechCardMaterial::class, 'shoe_tech_card_id');
    }
}
