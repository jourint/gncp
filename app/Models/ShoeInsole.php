<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoeInsole extends Model
{
    protected $fillable = ['name', 'is_soft_texon', 'has_egg', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_soft_texon' => 'boolean',
            'has_egg' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function shoeModels(): HasMany
    {
        return $this->hasMany(ShoeModel::class);
    }
}
