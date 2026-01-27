<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class FairBatchDistributor extends BaseDistributor
{
    public function getLabel(): string
    {
        return "Динамические пачки";
    }
    public function getIcon(): string
    {
        return "heroicon-m-arrows-right-left";
    }
    public function getConfirmText(): string
    {
        return "Система рассчитает оптимальный размер пачки для каждой позиции, чтобы нагрузка была максимально равной. Продолжить?";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        $empCount = $employees->count();
        $loads = $this->getLoads($employees, $selectedDate);

        foreach ($pending as $pos) {
            $remaining = (int)$pos->remaining;
            if ($remaining <= 0) continue;

            $batchSize = max(1, (int) ceil($remaining / $empCount));

            while ($remaining > 0) {
                $qtyToAssign = min($remaining, $batchSize);
                asort($loads);
                $leastLoadedId = key($loads);

                $this->assignWorkToEmployee($pos, $leastLoadedId, $qtyToAssign);
                $loads[$leastLoadedId] += $qtyToAssign;
                $remaining -= $qtyToAssign;
            }
        }
    }
}
