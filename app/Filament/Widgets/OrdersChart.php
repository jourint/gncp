<?php

namespace App\Filament\Widgets;

use App\Models\OrderPosition;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Объем производства (пар в неделю)';
    protected ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // 1. Получаем агрегированные данные из БД
        $dbData = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->select(
                DB::raw('orders.started_at::date as date_key'),
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->whereBetween('orders.started_at', [$startOfWeek, $endOfWeek])
            ->groupBy('date_key')
            ->get()
            ->pluck('total_quantity', 'date_key')
            ->map(fn($val) => (float) $val) // Гарантируем числовой тип
            ->toArray();

        $results = [];
        $labels = [];

        // 2. Формируем непрерывную шкалу на 7 дней
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateString = $date->format('Y-m-d');

            // Красивая метка дня: Пн, 19 янв
            $labels[] = $date->translatedFormat('D, d M');

            // Сопоставляем данные БД с календарем
            $results[] = array_key_exists($dateString, $dbData) ? $dbData[$dateString] : 0.0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Запланировано пар',
                    'data' => $results,
                    'borderColor' => '#8b5cf6', // Фиолетовый бренд-цвет
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)', // Легкая заливка под линией
                    'fill' => 'start',
                    'tension' => 0.4, // Плавные изгибы линии
                    'pointRadius' => 4, // Размер точек на графике
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
