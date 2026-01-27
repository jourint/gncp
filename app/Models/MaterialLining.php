<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialLining extends Model
{
    protected $fillable = ['name', 'color_id', 'is_active'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'color_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('default_order', function (Builder $builder) {
            $builder->reorder()->orderBy('name', 'asc');
        });
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }

    public function getFullNameAttribute(): string
    {
        $name = $this->name ?? 'Unknown';
        $colorName = $this->color?->name ?? '';
        return trim("$name $colorName");
    }
}
