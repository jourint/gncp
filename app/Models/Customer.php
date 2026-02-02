<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasMessengerAccess;

class Customer extends Model
{
    use HasMessengerAccess;

    protected $fillable = ['name', 'phone', 'is_active'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'phone' => 'string',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function messengerAccounts(): MorphMany
    {
        return $this->morphMany(MessengerAccount::class, 'messengerable');
    }

    public function messengerInvites()
    {
        return $this->morphMany(MessengerInvite::class, 'invitable');
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }
}
