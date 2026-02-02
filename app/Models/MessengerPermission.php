<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessengerPermission extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'label'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'label' => 'string',
        ];
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(MessengerAccess::class, 'messenger_permission_id');
    }
}
