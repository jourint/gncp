<?php

namespace App\Filament\Widgets;

use App\Models\OrderPosition;
use App\Models\ShoeType;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ShoeTypesChart extends ChartWidget
{
    protected ?string $heading = 'Распределение по типам обуви';
    protected ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // 1. Собираем данные: Дата -> Тип Обуви -> Количество
        // В методе getData() виджета ShoeTypesChart
        $rawData = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            // Добавляем звено Модели
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            // Привязываемся к Типу через Модель
            ->join('shoe_types', 'shoe_models.shoe_type_id', '=', 'shoe_types.id')
            ->select(
                DB::raw('orders.started_at::date as date_key'),
                'shoe_types.id as type_id',
                'shoe_types.name as type_name',
                DB::raw('SUM(order_positions.quantity) as total_qty')
            )
            ->whereBetween('orders.started_at', [$startOfWeek, $endOfWeek])
            ->groupBy('date_key', 'type_id', 'type_name')
            ->get();

        $labels = [];
        $datasets = [];

        // Генерируем дни недели
        for ($i = 0; $i < 7; $i++) {
            $labels[] = $startOfWeek->copy()->addDays($i)->translatedFormat('D, d M');
        }

        // 2. Группируем по типам (каждый тип - отдельный цвет на графике)
        $types = ShoeType::all();
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

        foreach ($types as $index => $type) {
            $typeData = [];

            for ($i = 0; $i < 7; $i++) {
                $dateString = $startOfWeek->copy()->addDays($i)->format('Y-m-d');

                $found = $rawData->where('date_key', $dateString)
                    ->where('type_id', $type->id)
                    ->first();

                $typeData[] = $found ? (float) $found->total_qty : 0.0;
            }

            $datasets[] = [
                'label' => $type->name,
                'data' => $typeData,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderRadius' => 4,
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

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'stacked' => true, // Складываем столбики друг на друга
                ],
                'x' => [
                    'stacked' => true, // Для красоты и наглядности общего объема
                ],
            ],
        ];
    }
}
