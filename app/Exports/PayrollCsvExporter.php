<?php

namespace App\Exports;

use Illuminate\Support\Collection;

class PayrollCsvExporter
{
    /**
     * Подготавливает массив данных для CSV с разверткой по моделям.
     */
    public function export(Collection $payrollData, string $from, string $to): array
    {
        // Получаем все уникальные техкарты за период
        $allModels = \App\Models\OrderEmployee::query()
            ->join('orders', 'order_employees.order_id', '=', 'orders.id')
            ->join('order_positions', 'order_employees.order_position_id', '=', 'order_positions.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->whereBetween('orders.started_at', [$from, $to])
            ->distinct()
            ->pluck('shoe_tech_cards.name')
            ->sort()
            ->toArray();

        $rows = [];
        foreach ($payrollData as $emp) {
            $line = [
                'Сотрудник' => $emp['name'],
                'Должность' => $emp['position'],
                'Итого'     => $emp['total'],
                'Выплачено' => $emp['paid'],
                'Долг'      => $emp['debt'],
            ];

            // Подгружаем детализацию для "шахматки"
            $details = \App\Models\OrderEmployee::query()
                ->join('orders', 'order_employees.order_id', '=', 'orders.id')
                ->join('order_positions', 'order_employees.order_position_id', '=', 'order_positions.id')
                ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
                ->where('employee_id', $emp['id'])
                ->whereBetween('orders.started_at', [$from, $to])
                ->select(['shoe_tech_cards.name', 'order_employees.quantity', 'order_employees.price_per_pair'])
                ->get();

            foreach ($allModels as $modelName) {
                $modelSum = $details->where('name', $modelName)
                    ->sum(fn($i) => $i->quantity * $i->price_per_pair);
                $line[$modelName] = $modelSum ?: 0;
            }

            $rows[] = $line;
        }

        return $rows;
    }
}
