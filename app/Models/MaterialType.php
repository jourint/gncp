<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\Unit;

class MaterialType extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'unit_id', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'description' => 'string',
            'is_active' => 'boolean',
            'unit_id' => Unit::class,
        ];
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}
