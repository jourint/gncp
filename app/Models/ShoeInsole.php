<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\InsolesType;

class ShoeInsole extends Model
{
    protected $fillable = ['name', 'is_soft_texon', 'has_egg', 'type', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_soft_texon' => 'boolean',
            'has_egg' => 'boolean',
            'type' => InsolesType::class,
            'is_active' => 'boolean',
        ];
    }

    public function shoeModels(): HasMany
    {
        return $this->hasMany(ShoeModel::class);
    }

    public function getFullNameAttribute()
    {
        $typeName = $this->type?->getLabel() ?? 'Unknown';
        $texonName = $this->is_soft_texon ? 'мягкий' : 'жёсткий';
        return "{$this->name} {$typeName} ({$texonName})";
    }
}
