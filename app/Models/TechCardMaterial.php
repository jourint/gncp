<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechCardMaterial extends Model
{
    protected $fillable = ['shoe_tech_card_id', 'material_id', 'quantity'];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
        ];
    }

    public function shoeTechCard(): BelongsTo
    {
        return $this->belongsTo(ShoeTechCard::class, 'shoe_tech_card_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
