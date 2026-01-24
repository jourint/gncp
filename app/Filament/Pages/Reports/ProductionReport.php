<?php

namespace App\Filament\Pages\Reports;

use App\Models\OrderPosition;
use App\Models\OrderEmployee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ProductionReport extends BaseReport
{
    public function __construct(protected ?int $jobPositionId = null) {}

    public function execute(string $date): Collection
    {
        $isEmployeeReport = !is_null($this->jobPositionId);

        $query = $isEmployeeReport
            ? OrderEmployee::query()->whereHas('employee', fn($q) => $q->where('job_position_id', $this->jobPositionId))
            : OrderPosition::query();

        $raw = $query
            ->join('order_positions as op', $isEmployeeReport ? 'order_employees.order_position_id' : 'order_positions.id', '=', 'op.id')
            ->join('orders', 'op.order_id', '=', 'orders.id')
            ->join('shoe_tech_cards', 'op.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->leftJoin('material_linings', 'op.material_lining_id', '=', 'material_linings.id')
            ->select(
                $isEmployeeReport ? 'employees.name as group_name' : 'shoe_models.name as group_name',
                'shoe_tech_cards.name as tech_card_name',
                'material_linings.id as lining_id',
                'op.size_id',
                DB::raw('SUM(' . ($isEmployeeReport ? 'order_employees.quantity' : 'op.quantity') . ') as total_quantity')
            )
            ->when($isEmployeeReport, fn($q) => $q->join('employees', 'order_employees.employee_id', '=', 'employees.id'))
            ->where('orders.started_at', $date)
            ->groupBy(['group_name', 'tech_card_name', 'lining_id', 'op.size_id'])
            ->get();

        return $this->format($raw);
    }

    private function format(Collection $data): Collection
    {
        $liningNames = $this->getLiningNames($data);
        $final = collect();
        $overallTotal = 0;

        foreach ($data->groupBy('group_name') as $groupName => $items) {
            $groupTotal = 0;
            foreach ($items->groupBy(['tech_card_name', 'lining_id']) as $techName => $linings) {
                foreach ($linings as $liningId => $rows) {
                    $sum = $rows->sum('total_quantity');
                    $groupTotal += $sum;
                    $final->push([
                        'type' => 'tech_card_header',
                        'title' => $groupName,
                        'tech_card_name' => $techName . ($liningId ? " / " . ($liningNames[$liningId] ?? '') : ""),
                        'total_quantity' => $sum,
                        'sizes' => $rows->sortBy('size_id')
                    ]);
                }
            }
            $final->push(['type' => 'footer', 'title' => $groupName, 'total_quantity' => $groupTotal]);
            $overallTotal += $groupTotal;
        }
        $final->push(['type' => 'overall_total', 'total_quantity' => $overallTotal]);
        return $final;
    }

    public function toExcel(string $date): Collection
    {
        return $this->execute($date)->where('type', 'tech_card_header')->flatMap(
            fn($item) =>
            $item['sizes']->map(fn($s) => [
                'Группа' => $item['title'],
                'Техкарта' => $item['tech_card_name'],
                'Размер' => $s->size_id,
                'Кол-во' => $s->total_quantity,
            ])
        );
    }
}
