<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\OrderEmployee;
use App\Models\OrderPosition;
use App\Models\Workflow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Получает сводную статистику по сотрудникам за период.
     */
    public function getSummary(string $from, string $to, ?int $jobPositionId, string $search = ''): Collection
    {
        return Employee::query()
            ->with(['jobPosition'])
            ->when($jobPositionId, fn($q) => $q->where('job_position_id', $jobPositionId))
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->whereHas(
                'orderEmployees',
                fn($q) =>
                $q->join('orders', 'order_employees.order_id', '=', 'orders.id')
                    ->whereBetween('orders.started_at', [$from, $to])
            )
            ->get()
            ->map(fn(Employee $emp) => $this->calculateStats($emp, $from, $to));
    }

    private function calculateStats(Employee $emp, string $from, string $to): array
    {
        $stats = DB::table('order_employees')
            ->join('orders', 'order_employees.order_id', '=', 'orders.id')
            ->where('employee_id', $emp->id)
            ->whereBetween('orders.started_at', [$from, $to])
            ->selectRaw("
                SUM(quantity) as total_qty,
                SUM(quantity * price_per_pair) as total_sum,
                SUM(CASE WHEN is_paid = true THEN quantity * price_per_pair ELSE 0 END) as paid_sum,
                SUM(CASE WHEN is_paid = false THEN quantity * price_per_pair ELSE 0 END) as debt_sum
            ")->first();

        return [
            'id'       => $emp->id,
            'name'     => $emp->name,
            'position' => $emp->jobPosition?->value ?? '—',
            'qty'      => (float)($stats->total_qty ?? 0),
            'total'    => (float)($stats->total_sum ?? 0),
            'paid'     => (float)($stats->paid_sum ?? 0),
            'debt'     => (float)($stats->debt_sum ?? 0),
        ];
    }


    public function getEmployeeDetails(int $employeeId, string $from, string $to): array
    {
        $rawData = OrderEmployee::query()
            ->join('orders', 'order_employees.order_id', '=', 'orders.id')
            ->join('order_positions', 'order_employees.order_position_id', '=', 'order_positions.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->where('order_employees.employee_id', $employeeId)
            ->whereBetween('orders.started_at', [$from, $to])
            ->select([
                'orders.started_at as work_date',
                'shoe_tech_cards.name as card_name',
                'order_employees.quantity',
                'order_employees.price_per_pair',
                'order_employees.is_paid'
            ])
            ->orderBy('orders.started_at', 'desc')
            ->get();

        $grouped = [];
        foreach ($rawData as $item) {
            $grouped[$item->work_date][$item->card_name] = [
                'model_name' => $item->card_name,
                'qty'        => ($grouped[$item->work_date][$item->card_name]['qty'] ?? 0) + $item->quantity,
                'price'      => (float)$item->price_per_pair,
                'is_paid'    => ($grouped[$item->work_date][$item->card_name]['is_paid'] ?? true) && $item->is_paid,
            ];
        }
        return $grouped;
    }

    /**
     * Группировка дополнительных работ
     */
    public function getExtraWorksSummary(string $from, string $to): array
    {
        $positions = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->whereBetween('orders.started_at', [$from, $to])
            ->select(['orders.started_at as date', 'shoe_models.workflows', 'order_positions.quantity'])
            ->orderBy('orders.started_at', 'desc')
            ->get();

        $wfPrices = Workflow::pluck('price', 'id');
        $wfNames = Workflow::pluck('name', 'id');

        $grouped = [];
        foreach ($positions as $pos) {
            $workflows = is_array($pos->workflows) ? $pos->workflows : json_decode($pos->workflows, true);
            if (empty($workflows)) continue;

            foreach ($workflows as $id) {
                $name = $wfNames[$id] ?? "Услуга #{$id}";
                $grouped[$pos->date][$name] = [
                    'work_name' => $name,
                    'qty'   => ($grouped[$pos->date][$name]['qty'] ?? 0) + $pos->quantity,
                    'price' => (float)($wfPrices[$id] ?? 0),
                ];
            }
        }
        krsort($grouped);
        return $grouped;
    }
}
