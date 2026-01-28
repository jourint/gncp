<?php

namespace App\Filament\Widgets;

use App\Models\OrderEmployee;
use App\Enums\JobPosition;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalariesChart extends ChartWidget
{
    protected ?string $heading = 'ФОТ по цехам (запущено в работу)';
    protected ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // 1. Получаем данные из БД
        $rawData = OrderEmployee::query()
            ->join('orders', 'order_employees.order_id', '=', 'orders.id')
            ->join('employees', 'order_employees.employee_id', '=', 'employees.id')
            ->select(
                DB::raw('orders.started_at::date as date_key'),
                'employees.job_position_id',
                DB::raw('SUM(order_employees.quantity * order_employees.price_per_pair) as total')
            )
            ->whereBetween('orders.started_at', [$startOfWeek, $endOfWeek])
            ->groupBy('date_key', 'employees.job_position_id')
            ->get();

        $labels = [];
        $datasets = [];

        // Генерируем подписи дней (Пн, Вт...)
        for ($i = 0; $i < 7; $i++) {
            $labels[] = $startOfWeek->copy()->addDays($i)->translatedFormat('D, d M');
        }

        foreach (JobPosition::cases() as $position) {
            // Пропускаем "Не выбрано"
            if ($position === JobPosition::None) continue;

            $dataForPosition = [];
            $colorHex = $position->getChartColor();

            for ($i = 0; $i < 7; $i++) {
                $dateString = $startOfWeek->copy()->addDays($i)->format('Y-m-d');

                // Ищем сумму. Важно: сравниваем с $position->value
                $sum = $rawData->where('date_key', $dateString)
                    ->where('job_position_id', $position->value)
                    ->first();

                $dataForPosition[] = $sum ? (float) $sum->total : 0.0;
            }

            $datasets[] = [
                'label' => $position->getLabel(),
                'data' => $dataForPosition,
                'backgroundColor' => $colorHex . '80', // Добавляем 80 для прозрачности (hex alpha)
                'borderColor' => $colorHex,
                'borderWidth' => 1,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
