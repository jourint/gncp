<?php

namespace App\Traits;

use App\Models\MessengerAccess;
use App\Models\MessengerPermission;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasMessengerAccess
{
    /**
     * Прямая морф-связь с таблицей доступов
     */
    public function messengerAccesses(): MorphMany
    {
        return $this->morphMany(MessengerAccess::class, 'accessible');
    }

    /**
     * Связь для Filament: дает прямой доступ к моделям разрешений
     */
    public function messengerPermissions(): MorphToMany
    {
        return $this->morphToMany(
            MessengerPermission::class,
            'accessible',           // Название из миграции $table->morphs('accessible')
            'messenger_accesses',   // Таблица
            null,                   // По умолчанию
            'messenger_permission_id'
        )->withTimestamps();
    }

    /**
     * Проверка наличия права
     */
    public function hasMessengerPermission(string $permissionName): bool
    {
        return $this->messengerAccesses()
            ->whereHas('permission', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })->exists();
    }
}
