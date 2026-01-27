<?php

namespace App\Filament\Pages\WorkDistribution;

use App\Models\OrderEmployee;

abstract class BaseDistributor implements DistributionStrategy
{
    protected function assignWorkToEmployee($pos, $employeeId, $qty): void
    {
        if ($qty <= 0) return;

        $assignment = OrderEmployee::firstOrNew([
            'order_id' => $pos->order_id,
            'order_position_id' => $pos->id,
            'employee_id' => $employeeId,
        ]);

        $assignment->quantity = ($assignment->exists ? $assignment->quantity : 0) + $qty;
        $assignment->price_per_pair = $pos->price;
        $assignment->is_paid = false;
        $assignment->save();
    }

    protected function getLoads($employees, $date): array
    {
        $loads = [];
        foreach ($employees as $emp) {
            $loads[$emp->id] = (int) OrderEmployee::where('employee_id', $emp->id)
                ->whereHas('order', fn($q) => $q->where('started_at', $date))
                ->sum('quantity');
        }
        return $loads;
    }
}
