<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\JobPosition;

class Employee extends Model
{
    protected $fillable = ['name', 'job_position_id', 'phone', 'is_active', 'skill_level'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'phone' => 'string',
            'job_position_id' => JobPosition::class,
            'is_active' => 'boolean',
            'skill_level' => 'decimal:2',
        ];
    }

    public function messengerAccounts(): MorphMany
    {
        return $this->morphMany(MessengerAccount::class, 'messengerable');
    }

    public function messengerInvites()
    {
        return $this->morphMany(MessengerInvite::class, 'invitable');
    }

    public function orderEmployees(): HasMany
    {
        return $this->hasMany(OrderEmployee::class, 'employee_id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->getJobPositionLabel()})";
    }

    public function getJobPositionLabel(): string
    {
        // Если в базе NULL или что-то не то, вернем дефолт
        return $this->job_position_id?->getLabel() ?? 'Должность не назначена';
    }
}
