<?php

namespace App\Filament\Pages\Reports;

use App\Models\OrderEmployee;
use App\Models\JobPosition;
use Illuminate\Support\Collection;

class SalaryReport extends BaseReport
{
    public function execute(string $date): Collection
    {
        $positions = JobPosition::pluck('value', 'id');

        return OrderEmployee::query()
            ->with(['employee', 'orderPosition.shoeTechCard.shoeModel'])
            ->whereHas('order', fn($q) => $q->where('started_at', $date))
            ->get()
            ->groupBy('employee.job_position_id')
            ->map(fn($emps, $jobId) => [
                'job_position_name' => $positions[$jobId] ?? 'Прочее',
                'employees' => $emps->groupBy('employee.name')->map(fn($works, $name) => [
                    'name' => $name,
                    'works' => $works->groupBy('orderPosition.shoeTechCard.shoeModel.name')->map(fn($items) => [
                        'model_name' => $items->first()->orderPosition->shoeTechCard->shoeModel->name,
                        'qty'   => $items->sum('quantity'),
                        'price' => $items->first()->price_per_pair,
                        'total' => $items->sum(fn($i) => $i->quantity * $i->price_per_pair)
                    ]),
                    'total_sum' => $works->sum(fn($i) => $i->quantity * $i->price_per_pair)
                ])
            ]);
    }

    public function toExcel(string $date): Collection
    {
        $data = $this->execute($date);
        $rows = collect();

        foreach ($data as $pos) {
            foreach ($pos['employees'] as $emp) {
                foreach ($emp['works'] as $work) {
                    $rows->push([
                        'Цех' => $pos['job_position_name'],
                        'Сотрудник' => $emp['name'],
                        'Модель' => $work['model_name'],
                        'Кол-во' => $work['qty'],
                        'Сумма' => $work['total'],
                    ]);
                }
            }
        }
        return $rows;
    }
}
