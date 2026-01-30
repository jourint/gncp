<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\MessengerDriver;

class MessengerInvite extends Model
{
    protected $fillable = [
        'invitable_id',
        'invitable_type',
        'driver',
        'token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'driver' => MessengerDriver::class,
            'token' => 'string',
            'expires_at' => 'datetime',
        ];
    }

    public function invitable(): MorphTo
    {
        return $this->morphTo();
    }
}
