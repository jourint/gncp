<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class BoxIntegrityDistributor extends BaseDistributor
{
    public function getLabel(): string
    {
        return "Целостность ящиков";
    }
    public function getIcon(): string
    {
        return "heroicon-m-archive-box";
    }
    public function getConfirmText(): string
    {
        return "Распределить работу целыми ящиками, допуская небольшие отклонения в нагрузке ради удобства мастеров?";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        $empCount = $employees->count();
        foreach ($pending->groupBy('shoe_tech_card_id') as $modelPositions) {
            $targetShare = $modelPositions->sum('remaining') / $empCount;
            $modelLoads = array_fill_keys($employees->pluck('id')->toArray(), 0);

            foreach ($modelPositions as $pos) {
                $remaining = (int)$pos->remaining;
                while ($remaining > 0) {
                    asort($modelLoads);
                    $empId = key($modelLoads);
                    $gap = $targetShare - $modelLoads[$empId];

                    $qtyToAssign = ($remaining <= ($gap + 3) || $modelLoads[$empId] == 0) ? $remaining : (int) max(1, $gap);

                    $this->assignWorkToEmployee($pos, $empId, $qtyToAssign);
                    $modelLoads[$empId] += $qtyToAssign;
                    $remaining -= $qtyToAssign;
                }
            }
        }
    }
}
