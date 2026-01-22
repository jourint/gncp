<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $fillable = ['name', 'description', 'is_active', 'price'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'description' => 'string',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }
}
