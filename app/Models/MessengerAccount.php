<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\MessengerDriver;

class MessengerAccount extends Model
{
    protected $fillable = [
        'messengerable_id',
        'messengerable_type',
        'driver',
        'user_id',
        'chat_id',
        'identifier',
        'nickname',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'driver' => MessengerDriver::class,
            'user_id' => 'string',
            'chat_id' => 'string',
            'identifier' => 'string',
            'nickname' => 'string',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Получить модель, которой принадлежит этот аккаунт (Employee или Customer).
     */
    public function messengerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function messengerLogs(): HasMany
    {
        return $this->hasMany(MessengerLog::class, 'messenger_account_id');
    }

    public function messengerInvites(): HasMany
    {
        return $this->hasMany(MessengerInvite::class, 'messenger_account_id');
    }

    public function botState(): HasOne
    {
        return $this->hasOne(MessengerBotState::class, 'messenger_account_id');
    }
}
