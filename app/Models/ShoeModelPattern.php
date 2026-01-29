<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoeModelPattern extends Model
{
    protected $fillable = ['shoe_model_id', 'size_id', 'file_path', 'file_name', 'type', 'scale', 'note'];

    protected function casts(): array
    {
        return [
            'size_id' => 'integer',
            'file_path' => 'string',
            'file_name' => 'string',
            'type' => 'string',
            'scale' => 'decimal:2',
            'note' => 'string',
        ];
    }

    public function size(): BelongsTo
    {
        // Указываем связь с твоей Sushi моделью
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function shoeModel(): BelongsTo
    {
        return $this->belongsTo(ShoeModel::class, 'shoe_model_id');
    }
}
