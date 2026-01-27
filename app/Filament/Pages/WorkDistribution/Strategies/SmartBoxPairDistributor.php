<?php

namespace App\Filament\Pages\WorkDistribution\Strategies;

use App\Filament\Pages\WorkDistribution\BaseDistributor;

class SmartBoxPairDistributor extends BaseDistributor
{
    // Cамый продвинутый алгоритм со встроенным Box Builder.
    public function getLabel(): string
    {
        return "Умные ящики (36-37)";
    }
    public function getIcon(): string
    {
        return "heroicon-m-cube-transparent";
    }
    public function getConfirmText(): string
    {
        return "Сгруппировать смежные размеры в физические ящики и распределить их максимально эффективно? (36-37, 38-39)";
    }

    public function distribute($pending, $employees, $selectedDate): void
    {
        $loads = $this->getLoads($employees, $selectedDate);
        $targetPerEmp = ($pending->sum('remaining') + array_sum($loads)) / $employees->count();

        foreach ($pending->groupBy('shoe_tech_card_id') as $modelPositions) {
            $boxes = $this->buildBoxes($modelPositions);

            foreach ($boxes as $boxContent) {
                $boxQty = collect($boxContent)->sum('remaining');
                asort($loads);
                $empId = key($loads);
                $gap = $targetPerEmp - $loads[$empId];

                if ($boxQty <= ($gap + 4) || $loads[$empId] == 0) {
                    foreach ($boxContent as $pos) {
                        $this->assignWorkToEmployee($pos, $empId, (int)$pos->remaining);
                    }
                    $loads[$empId] += $boxQty;
                } else {
                    foreach ($boxContent as $pos) {
                        $pRemaining = (int)$pos->remaining;
                        while ($pRemaining > 0) {
                            asort($loads);
                            $eid = key($loads);
                            $currentGap = max(1, $targetPerEmp - $loads[$eid]);
                            $take = min($pRemaining, $currentGap);

                            $this->assignWorkToEmployee($pos, $eid, (int)$take);
                            $loads[$eid] += $take;
                            $pRemaining -= $take;
                        }
                    }
                }
            }
        }
    }

    private function buildBoxes($positions)
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
