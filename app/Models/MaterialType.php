<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialType extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'unit_id', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unit_id' => 'integer',
        ];
    }
}
