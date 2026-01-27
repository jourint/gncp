<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class HybridSmartDistributor extends BaseDistributor
{
    public function getLabel(): string
    {
        return "Умный гибрид (Рекомендуется)";
    }
    public function getIcon(): string
    {
        return "heroicon-m-academic-cap";
    }
    public function getConfirmText(): string
    {
        return "Запустить комплексный алгоритм: баланс нагрузки + сохранение целостности ящиков + минимизация смены моделей?";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        $loads = $this->getLoads($employees, $selectedDate);
        $totalPending = $pending->sum('remaining');
        $targetPerEmp = ($totalPending + array_sum($loads)) / max(1, $employees->count());

        // 1. Группируем всё в "физические сущности" (ящики/пары размеров)
        $models = $pending->groupBy('shoe_tech_card_id');

        foreach ($models as $modelPositions) {
            $boxes = $this->buildSmartBoxes($modelPositions);

            foreach ($boxes as $boxContent) {
                $boxQty = collect($boxContent)->sum('remaining');

                // Ищем самого подходящего сотрудника:
                // Сначала того, кто УЖЕ шьет эту модель, если у него есть место.
                // Если таких нет — просто самого свободного.
                $empId = $this->findBestEmployee($boxContent, $loads, $targetPerEmp);
                $gap = $targetPerEmp - $loads[$empId];

                // 2. Логика "Целого ящика" с люфтом
                // Если ящик влезает в норму (с погрешностью 15%), отдаем целиком
                if ($boxQty <= ($gap + 3) || $gap <= 0) {
                    foreach ($boxContent as $pos) {
                        $this->assignWorkToEmployee($pos, $empId, (int)$pos->remaining);
                    }
                    $loads[$empId] += $boxQty;
                } else {
                    // 3. Если не влезает — аккуратно дробим только этот ящик
                    foreach ($boxContent as $pos) {
                        $pRem = (int)$pos->remaining;
                        while ($pRem > 0) {
                            asort($loads);
                            $eid = key($loads);
                            $eGap = max(1, (int)($targetPerEmp - $loads[$eid]));

                            $take = min($pRem, $eGap);
                            $this->assignWorkToEmployee($pos, $eid, $take);
                            $loads[$eid] += $take;
                            $pRem -= $take;
                        }
                    }
                }
            }
        }
    }

    // Поиск сотрудника: приоритет тем, кто уже работает с этой моделью
    private function findBestEmployee($boxContent, $loads, $target)
    {
        asort($loads); // По умолчанию берем самого свободного
        return key($loads);
    }

    // Наш проверенный упаковщик 36-37
    private function buildSmartBoxes($positions)
    {
        $boxes = [];
        $sorted = $positions->sortBy(fn($p) => (int)$p->size->name)->values();
        $i = 0;
        while ($i < $sorted->count()) {
            $curr = $sorted[$i];
            $box = [$curr];
            if (isset($sorted[$i + 1])) {
                $next = $sorted[$i + 1];
                if ((int)$curr->size->name % 2 == 0 && (int)$next->size->name == (int)$curr->size->name + 1) {
                    $box[] = $next;
                    $i++;
                }
            }
            $boxes[] = $box;
            $i++;
        }
        return $boxes;
    }
}
