<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MessengerStatus;

class MessengerLog extends Model
{
    protected $fillable = [
        'messenger_account_id',
        'title',
        'message',
        'status',
        'error_message',
        'sent_at'
    ];

    protected function casts(): array
    {
        return [
            'title' => 'string',
            'message' => 'string',
            'status' => MessengerStatus::class,
            'error_message' => 'string',
            'sent_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(MessengerAccount::class, 'messenger_account_id');
    }
}
