<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MessengerReport extends Model
{
    protected $fillable = [
        'reportable_id',
        'reportable_type',
        'production_date',
        'report_type',
        'content',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'report_type' => 'string',
            'content' => 'string',
            'sent_at' => 'datetime',
        ];
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }
}
