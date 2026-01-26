<?php

namespace App\Filament\Pages\Reports;

use App\Models\OrderPosition;
use App\Models\Material;
use Faker\Provider\Base;
use Illuminate\Support\Collection;

class StockRequirementsReport extends BaseReport
{
    public function execute(string $date): array
    {
        // 1. Жадная загрузка (Eager Loading) для исключения N+1 запросов
        $orderPositions = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->where('orders.started_at', $date)
            ->select('order_positions.*')
            ->with([
                'order.customer', // Для детализации
                'shoeTechCard.techCardMaterials.material.color',
                'shoeTechCard.techCardMaterials.material.materialType.unit', // Подгружаем Unit (Sushi)
                'shoeTechCard.shoeSole.color'
            ])
            ->get();

        $materialsForCutting = collect();
        $solesNeeded = collect(); // Группировка: sole_id => [sizes => [size_id => qty]]

        foreach ($orderPositions as $pos) {
            $q = $pos->quantity;
            $customerName = $pos->order?->customer?->name ?? 'Запас';

            // Агрегация материалов для кроя
            if ($pos->shoeTechCard?->techCardMaterials) {
                foreach ($pos->shoeTechCard->techCardMaterials as $tcm) {
                    $this->aggregate($materialsForCutting, $tcm->material, $q * $tcm->quantity, $customerName);
                }
            }

            // Агрегация подошв по размерам
            $sole = $pos->shoeTechCard?->shoeSole;
            if ($sole) {
                $soleName = $sole->fullName;
                $id = $sole->id;
                $sizeId = $pos->size_id ?? 0;

                $item = $solesNeeded->get($id, [
                    'sole_id'        => $id,
                    'sole_name'      => $soleName,
                    'sizes'          => [], // Детализация по размерам: [size_id => qty]
                    'total_needed'   => 0,
                    'details'        => [] // Детализация по клиентам
                ]);

                // Добавляем размер и количество
                $item['sizes'][$sizeId] = ($item['sizes'][$sizeId] ?? 0) + $q;

                $item['total_needed'] += $q;
                $item['details'][$customerName] = ($item['details'][$customerName] ?? 0) + $q;

                $solesNeeded->put($id, $item);
            }
        }

        return [
            'materials_for_cutting' => $materialsForCutting->sortBy('material_name')->values()->toArray(),
            'soles_needed' => $solesNeeded->sortBy('sole_name')->values()->toArray(),
        ];
    }

    /**
     * Универсальный метод агрегации с поддержкой детализации
     */
    private function aggregate(Collection &$col, ?Material $mat, float $val, string $customerName): void
    {
        if (!$mat) return;

        $id = $mat->id;
        $item = $col->get($id, [
            'material_id'   => $id,
            'material_name' => $mat->name . ($mat->color ? " ({$mat->color->name})" : ""),
            'unit_name'     => $mat->materialType?->unit?->label ?? 'ед.',
            'total_needed'  => 0,
            'details'       => [] // Храним детализацию: ['Имя клиента' => кол-во]
        ]);

        $item['total_needed'] += $val;

        // Накапливаем детализацию по клиентам
        $item['details'][$customerName] = ($item['details'][$customerName] ?? 0) + $val;

        $col->put($id, $item);
    }

    public function toExcel(string $date): Collection
    {
        $data = $this->execute($date);
        $rows = collect();

        // Экспорт материалов для кроя
        foreach ($data['materials_for_cutting'] as $item) {
            $rows->push([
                'Категория' => 'Крой',
                'Наименование'  => $item['material_name'],
                'Размер'    => '',
                'Кол-во'    => $item['total_needed'],
                'Ед. изм.'  => $item['unit_name'],
            ]);
        }

        // Экспорт подошв с размерами
        foreach ($data['soles_needed'] as $item) {
            if (empty($item['sizes'])) {
                $rows->push([
                    'Категория' => 'Подошвы',
                    'Наименование'  => $item['sole_name'],
                    'Размер'    => 'Всего',
                    'Кол-во'    => $item['total_needed'],
                    'Ед. изм.'  => 'шт.',
                ]);
            } else {
                foreach ($item['sizes'] as $sizeId => $qty) {
                    $rows->push([
                        'Категория' => 'Подошвы',
                        'Наименование'  => $item['sole_name'],
                        'Размер'    => $sizeId,
                        'Кол-во'    => $qty,
                        'Ед. изм.'  => 'шт.',
                    ]);
                }
            }
        }

        return $rows;
    }
}
