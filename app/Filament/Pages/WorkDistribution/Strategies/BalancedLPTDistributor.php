<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class BalancedLPTDistributor extends BaseDistributor
{
    public function getLabel(): string
    {
        return "Баланс нагрузки (LPT)";
    }

    public function getIcon(): string
    {
        return "heroicon-m-scale";
    }

    public function getConfirmText(): string
    {
        return "Распределить работу, минимизируя дробление ящиков и выравнивая общую нагрузку сотрудников?";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        $totalQty = $pending->sum('remaining');
        $avgPosSize = $totalQty / max(1, $pending->count());
        $targetPerEmp = $totalQty / max(1, $employees->count());

        $loads = $this->getLoads($employees, $selectedDate);

        // Сортировка позиций от больших к малым
        $sortedPending = $pending->sortByDesc('remaining');

        foreach ($sortedPending as $pos) {
            $remaining = (int)$pos->remaining;

            while ($remaining > 0) {
                asort($loads);
                $leastLoadedId = key($loads);
                $gap = $targetPerEmp - $loads[$leastLoadedId];

                // Если позиция огромная, отдаем только часть до нормы
                $isTooBig = $remaining > ($avgPosSize * 1.5);

                if ($isTooBig && $remaining > $gap && $gap > 0) {
                    $qtyToAssign = (int) $gap;
                } else {
                    $qtyToAssign = $remaining;
                }

                // Защита от микро-остатков
                if (($remaining - $qtyToAssign) < ($avgPosSize * 0.2)) {
                    $qtyToAssign = $remaining;
                }

                $this->assignWorkToEmployee($pos, $leastLoadedId, $qtyToAssign);
                $loads[$leastLoadedId] += $qtyToAssign;
                $remaining -= $qtyToAssign;
            }
        }
    }
}
