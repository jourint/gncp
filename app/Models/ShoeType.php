<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoeType extends Model
{
    protected $fillable = ['name', 'is_active', 'price_cutting', 'price_sewing', 'price_shoemaker'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_cutting' => 'float',
            'price_sewing' => 'float',
            'price_shoemaker' => 'float',
        ];
    }
}
