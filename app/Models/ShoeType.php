<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoeType extends Model
{
    protected $fillable = ['name', 'is_active', 'price_cutting', 'price_sewing', 'price_shoemaker'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'is_active' => 'boolean',
            'price_cutting' => 'decimal:2',
            'price_sewing' => 'decimal:2',
            'price_shoemaker' => 'decimal:2',
        ];
    }

    public function models(): HasMany
    {
        return $this->hasMany(ShoeModel::class, 'shoe_type_id');
    }
}
