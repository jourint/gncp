<?php

namespace App\Filament\Pages\Reports;

use App\Models\OrderPosition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ExpeditionReport extends BaseReport
{
    public function execute(string $date): Collection
    {
        $raw = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->leftJoin('material_linings', 'order_positions.material_lining_id', '=', 'material_linings.id')
            ->select(
                'customers.name as customer_name',
                'shoe_models.name as model_name',
                'shoe_tech_cards.name as tech_card_name',
                'material_linings.id as lining_id',
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as qty_sum')
            )
            ->where('orders.started_at', $date)
            ->groupBy(['customers.name', 'shoe_models.name', 'shoe_tech_cards.name', 'material_linings.id', 'order_positions.size_id'])
            ->get();

        return $this->format($raw);
    }

    private function format(Collection $data): Collection
    {
        $liningNames = $this->getLiningNames($data); // Используем метод из BaseReport
        $final = collect();
        $overallTotal = 0;

        foreach ($data->groupBy('customer_name') as $customerName => $customerItems) {
            $customerTotal = $customerItems->sum('qty_sum');

            $final->push(['type' => 'customer_header', 'customer_name' => $customerName]);

            foreach ($customerItems->groupBy(fn($i) => $i->model_name . $i->tech_card_name . $i->lining_id) as $group) {
                $first = $group->first();
                $activeSizes = $group->where('qty_sum', '>', 0)->sortBy('size_id');

                $final->push([
                    'type' => 'model_row',
                    'customer_name' => $customerName,
                    'full_model_name' => "{$first->model_name} / {$first->tech_card_name}" . ($first->lining_id ? " / {$liningNames[$first->lining_id]}" : ""),
                    'total_quantity' => $activeSizes->sum('qty_sum'),
                    'sizes' => $activeSizes
                ]);
            }

            $final->push(['type' => 'customer_footer', 'customer_name' => $customerName, 'total_quantity' => $customerTotal]);
            $overallTotal += $customerTotal;
        }

        $final->push(['type' => 'overall_total', 'total_quantity' => $overallTotal]);
        return $final;
    }

    public function toExcel(string $date): Collection
    {
        return $this->execute($date)->where('type', 'model_row')->map(fn($item) => array_merge([
            'Клиент' => $item['customer_name'],
            'Модель' => $item['full_model_name'],
            'Всего' => (int)$item['total_quantity'],
        ], $item['sizes']->mapWithKeys(fn($s) => ["Размер {$s->size_id}" => (int)$s->qty_sum])->toArray()));
    }
}
