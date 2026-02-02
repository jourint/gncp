<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessengerBotState extends Model
{
    protected $fillable = [
        'messenger_account_id',
        'command_name',
        'step',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'command_name' => 'string',
            'step' => 'string',
            'context' => 'array',
        ];
    }
}
