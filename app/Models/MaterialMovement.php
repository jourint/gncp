<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use App\Enums\MovementType;

class MaterialMovement extends Model
{
    protected $fillable = [
        'movable_id',
        'movable_type',
        'type',
        'description',
        'quantity',
        //    'user_id'
    ];

    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'quantity' => 'float',
            'user_id' => 'integer',
        ];
    }
    public function movable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($movement) {
            if (!$movement->user_id) {
                $movement->user_id = Auth::id();
            }
        });
    }

    public function getMovableTypeLabelAttribute()
    {
        return match ($this->movable_type) {
            \App\Models\Material::class => 'Материал',
            \App\Models\ShoeSoleItem::class => 'Подошва',
            default => 'Неизвестно',
        };
    }
}
