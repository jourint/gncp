<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoeSole extends Model
{
    protected $fillable = [
        'name',
        'color_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'color_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function shoeSoleItems(): HasMany
    {
        return $this->hasMany(ShoeSoleItem::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function getFullNameAttribute()
    {
        $colorName = $this->color?->name;
        return "{$this->name} ({$colorName})";
    }
}
