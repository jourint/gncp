<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class ModelPriorityDistributor extends BaseDistributor
{
    public function getLabel(): string
    {
        return "По моделям (минимум замен)";
    }
    public function getIcon(): string
    {
        return "heroicon-m-paint-brush";
    }
    public function getConfirmText(): string
    {
        return "Распределить так, чтобы каждый мастер получал одну модель целиком, пока не достигнет своей нормы?";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        $targetPerEmp = $pending->sum('remaining') / $employees->count();
        $loads = $this->getLoads($employees, $selectedDate);

        foreach ($pending->groupBy('shoe_tech_card_id') as $positions) {
            foreach ($positions->sortByDesc('remaining') as $pos) {
                $remaining = (int)$pos->remaining;
                while ($remaining > 0) {
                    asort($loads);
                    $empId = key($loads);
                    $gap = $targetPerEmp - $loads[$empId];

                    $qtyToAssign = ($remaining <= ($gap + 2) || $remaining < 6) ? $remaining : (($gap > 0) ? (int)$gap : $remaining);

                    $this->assignWorkToEmployee($pos, $empId, $qtyToAssign);
                    $loads[$empId] += $qtyToAssign;
                    $remaining -= $qtyToAssign;
                }
            }
        }
    }
}
