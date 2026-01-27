<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class ProportionalDistributor extends BaseDistributor
{
    public function getLabel(): string
    {
        return "Поровну + остатки";
    }

    public function getIcon(): string
    {
        return "heroicon-m-sparkles";
    }

    public function getConfirmText(): string
    {
        return "Система раздаст базовую часть всем поровну, а остатки распределит между самыми свободными сотрудниками. Продолжить?";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        $loads = $this->getLoads($employees, $selectedDate);

        foreach ($pending as $pos) {
            if ($pos->price <= 0 || (int)$pos->remaining <= 0) continue;

            $remaining = (int)$pos->remaining;
            $empCount = $employees->count();

            // Базовая целая часть
            $base = floor($remaining / $empCount);
            // Хвост (остаток от деления)
            $extra = $remaining % $empCount;

            foreach ($employees as $emp) {
                if ($base > 0) {
                    $this->assignWorkToEmployee($pos, $emp->id, (int)$base);
                    $loads[$emp->id] += $base;
                }
            }

            // Раздаем хвосты тем, у кого на данный момент меньше всего пар в loads
            if ($extra > 0) {
                for ($i = 0; $i < $extra; $i++) {
                    asort($loads);
                    $id = key($loads);
                    $this->assignWorkToEmployee($pos, $id, 1);
                    $loads[$id] += 1;
                }
            }
        }
    }
}
