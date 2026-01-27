<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class ModelShareDistributor extends BaseDistributor
{
    public function getLabel(): string
    {
        return "По долям моделей (каждому)";
    }

    public function getIcon(): string
    {
        return "heroicon-m-user-group";
    }

    public function getConfirmText(): string
    {
        return "Разделить каждую модель между всеми мастерами пропорционально? Это обеспечит разнообразие работы для всех.";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        $empCount = $employees->count();
        $loads = $this->getLoads($employees, $selectedDate);
        $groupedByModel = $pending->groupBy('shoe_tech_card_id');

        foreach ($groupedByModel as $positions) {
            $modelTotal = $positions->sum('remaining');
            $shareOfModel = $modelTotal / $empCount;
            $modelLoads = array_fill_keys($employees->pluck('id')->toArray(), 0);

            foreach ($positions as $pos) {
                $remaining = (int)$pos->remaining;

                while ($remaining > 0) {
                    asort($modelLoads);
                    $targetEmpId = key($modelLoads);
                    $canTake = max(0, $shareOfModel - $modelLoads[$targetEmpId]);

                    if ($remaining <= ($canTake * 1.2) || $canTake < 1) {
                        $qtyToAssign = $remaining;
                    } else {
                        $qtyToAssign = (int)floor($canTake);
                    }

                    if ($qtyToAssign <= 0) $qtyToAssign = $remaining;

                    $this->assignWorkToEmployee($pos, $targetEmpId, $qtyToAssign);
                    $modelLoads[$targetEmpId] += $qtyToAssign;
                    $loads[$targetEmpId] += $qtyToAssign;
                    $remaining -= $qtyToAssign;
                }
            }
        }
    }
}
