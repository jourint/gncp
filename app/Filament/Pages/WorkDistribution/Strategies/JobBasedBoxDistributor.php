<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class JobBasedBoxDistributor extends BaseDistributor
{
    public function getLabel(): string
    {
        return "Ящики по типу цеха";
    }
    public function getIcon(): string
    {
        return "heroicon-m-building-office-2";
    }
    public function getConfirmText(): string
    {
        return "Распределить работу с учетом специфики выбранного цеха (спаривание размеров или валовый объем)?";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        // ВАЖНО: jobId должен передаваться или браться из контекста. 
        // Если он в сессии или параметре страницы - адаптируй вызов.
        $jobId = (int) request('selected_job_id');

        $groupedPending = $pending->groupBy(function ($pos) use ($jobId) {
            $sizeNum = (int) $pos->size->name;
            if ($jobId === 1) { // Закройный
                $pair = floor(($sizeNum - 36) / 2);
                return "m_{$pos->shoe_tech_card_id}_p_{$pair}";
            }
            if ($jobId === 3) { // Сапожный
                return "m_{$pos->shoe_tech_card_id}_bulk";
            }
            return "m_{$pos->shoe_tech_card_id}_s_{$pos->size_id}";
        });

        $loads = $this->getLoads($employees, $selectedDate);
        $targetPerEmp = $pending->sum('remaining') / $employees->count();

        foreach ($groupedPending as $boxPositions) {
            $boxQty = $boxPositions->sum('remaining');
            while ($boxQty > 0) {
                asort($loads);
                $empId = key($loads);
                $gap = $targetPerEmp - $loads[$empId];

                $take = ($boxQty <= ($gap * 1.2) || $jobId === 3 || $gap <= 0) ? $boxQty : (int) max(1, $gap);

                $tempTake = $take;
                foreach ($boxPositions as $pos) {
                    $canGive = min($pos->remaining, $tempTake);
                    if ($canGive <= 0) continue;
                    $this->assignWorkToEmployee($pos, $empId, (int)$canGive);
                    $pos->remaining -= $canGive;
                    $tempTake -= $canGive;
                }
                $loads[$empId] += $take;
                $boxQty -= $take;
            }
        }
    }
}
