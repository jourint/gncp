<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class FixedBatchDistributor extends BaseDistributor
{
    public function getLabel(): string
    {
        return "Пачками по 10";
    }
    public function getIcon(): string
    {
        return "heroicon-m-rectangle-stack";
    }
    public function getConfirmText(): string
    {
        return "Распределить работу строго пачками по 10 пар самому свободному сотруднику?";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        $maxBatchSize = 10;
        $loads = $this->getLoads($employees, $selectedDate);

        foreach ($pending as $pos) {
            $remaining = (int)$pos->remaining;
            while ($remaining > 0) {
                $qtyToAssign = min($remaining, $maxBatchSize);
                asort($loads);
                $leastLoadedId = key($loads);

                $this->assignWorkToEmployee($pos, $leastLoadedId, $qtyToAssign);
                $loads[$leastLoadedId] += $qtyToAssign;
                $remaining -= $qtyToAssign;
            }
        }
    }
}
