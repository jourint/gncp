<?php

namespace App\Filament\Widgets;

use App\Models\OrderEmployee;
use App\Models\JobPosition;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SalariesChart extends ChartWidget
{
    protected ?string $heading = 'ФОТ по цехам (запущено в работу)';
    protected ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // 1. Получаем данные из БД, группируя по дате И по должности (цеху)
        // Нам нужно пробросить job_position_id из таблицы сотрудников
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

        // 2. Подготавливаем структуру для Chart.js
        $jobPositions = JobPosition::all(); // Наши Sushi-данные
        $labels = [];
        $datasets = [];

        // Генерируем подписи дней
        for ($i = 0; $i < 7; $i++) {
            $labels[] = $startOfWeek->copy()->addDays($i)->translatedFormat('D, d M');
        }

        // Цвета для разных цехов
        $colors = [
            1 => ['border' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.5)'], // Закройный
            2 => ['border' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.5)'], // Швейный
            3 => ['border' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.5)'], // Сапожный
        ];

        // 3. Формируем по датасету на каждый цех (кроме "Не выбрано")
        foreach ($jobPositions as $position) {
            if ($position->id === 0) continue;

            $dataForPosition = [];

            for ($i = 0; $i < 7; $i++) {
                $dateString = $startOfWeek->copy()->addDays($i)->format('Y-m-d');

                // Ищем сумму для этого цеха в этот день
                $sum = $rawData->where('date_key', $dateString)
                    ->where('job_position_id', $position->id)
                    ->first();

                $dataForPosition[] = $sum ? (float) $sum->total : 0.0;
            }

            $datasets[] = [
                'label' => $position->value,
                'data' => $dataForPosition,
                'backgroundColor' => $colors[$position->id]['bg'] ?? '#ccc',
                'borderColor' => $colors[$position->id]['border'] ?? '#999',
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
        return 'bar'; // Столбчатый график идеально подходит для сравнения цехов
    }
}
