<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MessengerAccess extends Model
{
    protected $fillable = [
        'messenger_permission_id',
        'accessible_type',
        'accessible_id',


    ];

    protected function casts(): array
    {
        return [
            'messenger_permission_id' => 'integer',
            'accessible_type' => 'string',
            'accessible_id' => 'integer',
        ];
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(MessengerPermission::class, 'messenger_permission_id');
    }

    public function accessible(): MorphTo
    {
        return $this->morphTo();
    }
}
