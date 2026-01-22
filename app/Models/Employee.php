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
            'name' => 'string',
            'phone' => 'string',
            'job_position_id' => 'integer',
            'is_active' => 'boolean',
            'skill_level' => 'decimal:2',
        ];
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class)->withDefault([
            'name' => 'Должность не назначена',
        ]);
    }

    public function orderEmployees(): HasMany
    {
        return $this->hasMany(OrderEmployee::class, 'employee_id');
    }
}
