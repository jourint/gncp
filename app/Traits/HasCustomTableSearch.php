<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCustomTableSearch
{
    /**
     * Поиск по связанным полям с учетом специфики PostgreSQL (ilike)
     */
    public static function searchRelation(string $relation, array|string $columns): \Closure
    {
        return function (Builder $query, string $search) use ($relation, $columns) {
            $columns = (array) $columns;

            // Используем where (а не orWhere), чтобы втиснуться в логику Filament правильно
            return $query->whereHas($relation, function (Builder $q) use ($columns, $search) {
                // Обязательная группировка условий внутри EXISTS
                $q->where(function (Builder $inner) use ($columns, $search) {
                    foreach ($columns as $index => $column) {
                        $method = $index === 0 ? 'where' : 'orWhere';

                        if (str_contains($column, '.')) {
                            [$subRel, $subCol] = explode('.', $column);
                            // Используем динамический вызов whereHas или orWhereHas
                            $inner->{$method . 'Has'}($subRel, fn($sq) => $sq->where($subCol, 'ilike', "%{$search}%"));
                        } else {
                            $inner->{$method}($column, 'ilike', "%{$search}%");
                        }
                    }
                });
            });
        };
    }

    /**
     * Сортировка по полю связанной таблицы
     */
    public static function sortRelation(string $table, string $foreignKey, string $sortBy, string $ownerKey = 'id'): \Closure
    {
        return function (Builder $query, string $direction) use ($table, $foreignKey, $sortBy, $ownerKey) {
            return $query->join($table, $foreignKey, '=', "{$table}.{$ownerKey}")
                ->orderBy("{$table}.{$sortBy}", $direction)
                ->select("{$query->getModel()->getTable()}.*"); // Важно: выбираем только основные поля
        };
    }
}
