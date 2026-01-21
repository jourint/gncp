<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['name', 'job_position_id', 'phone', 'is_active', 'skill_level'];

    protected function casts(): array
    {
        return [
            'job_position_id' => 'integer',
            'is_active' => 'boolean',
            'skill_level' => 'float',
        ];
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class)->withDefault([
            'name' => 'Должность не назначена',
        ]);
    }

    /**
     * Работы, назначенные сотруднику
     */
    public function orderEmployees(): HasMany
    {
        return $this->hasMany(OrderEmployee::class, 'employee_id');
    }
}
