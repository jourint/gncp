<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoeInsole extends Model
{
    protected $fillable = ['name', 'is_black', 'is_active', 'tech_card'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_black' => 'boolean',
            'tech_card' => 'array',
        ];
    }

    public function getDisplayNameAttribute()
    {
        return "{$this->name} " . ($this->is_black ? '(Черная)' : '(Бежевая)');
    }
}
