<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\OrderEmployee;
use App\Models\OrderPosition;
use App\Models\Workflow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Enums\JobPosition;

class PayrollService
{
    /**
     * Получает сводную статистику по сотрудникам за период.
     */
    public function getSummary(string $from, string $to, int|string|null $jobPositionId, string $search = ''): Collection
    {
        $position = $jobPositionId ? JobPosition::tryFrom($jobPositionId) : null;

        return Employee::query()
            ->when($position, fn($q) => $q->where('job_position_id', $position->value))
            ->when($search, fn($q) => $q->where('name', 'iLike', "%{$search}%"))
            // Считаем общее кол-во
            ->withSum(['orderEmployees as total_qty' => function ($q) use ($from, $to) {
                $q->whereHas('order', fn($o) => $o->whereBetween('started_at', [$from, $to]));
            }], 'quantity')
            // Считаем общую сумму (quantity * price_per_pair)
            // В Laravel для сложных вычислений в withSum можно использовать DB::raw
            ->withSum(['orderEmployees as total_sum' => function ($q) use ($from, $to) {
                $q->whereHas('order', fn($o) => $o->whereBetween('started_at', [$from, $to]))
                    ->select(DB::raw('COALESCE(SUM(quantity * price_per_pair), 0)'));
            }], 'id') // 'id' тут формальность, так как raw переопределит select
            // Считаем долг (где is_paid = false)
            ->withSum(['orderEmployees as debt_sum' => function ($q) use ($from, $to) {
                $q->where('is_paid', false)
                    ->whereHas('order', fn($o) => $o->whereBetween('started_at', [$from, $to]))
                    ->select(DB::raw('COALESCE(SUM(quantity * price_per_pair), 0)'));
            }], 'id')
            // Оставляем только тех, у кого была работа
            ->has('orderEmployees')
            ->get()
            ->map(function (Employee $emp) {
                return [
                    'id'       => $emp->id,
                    'name'     => $emp->name,
                    'position' => $emp->job_position_id?->getLabel() ?? '—',
                    'qty'      => (float)($emp->total_qty ?? 0),
                    'total'    => (float)($emp->total_sum ?? 0),
                    'paid'     => (float)(($emp->total_sum ?? 0) - ($emp->debt_sum ?? 0)), // Вычисляем оплаченное
                    'debt'     => (float)($emp->debt_sum ?? 0),
                ];
            });
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
                'order_employees.id as row_id',
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
                'row_id'     => $item->row_id,
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
