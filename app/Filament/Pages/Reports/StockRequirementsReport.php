<?php

namespace App\Filament\Pages\Reports;

use App\Models\OrderPosition;
use App\Models\Material;
use App\Enums\Unit;
use Illuminate\Support\Collection;

class StockRequirementsReport extends BaseReport
{
    public function execute(string $date): array
    {
        $orderPositions = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->where('orders.started_at', $date)
            ->select('order_positions.*')
            ->with([
                'order.customer',
                'shoeTechCard.techCardMaterials.material.color',
                'shoeTechCard.techCardMaterials.material.materialType',
                'shoeTechCard.shoeSole.color'
            ])
            ->get();

        $materialsForCutting = collect();
        $solesNeeded = collect();

        foreach ($orderPositions as $pos) {
            $qty = $pos->quantity;
            $customerName = $pos->order?->customer?->name ?? 'Запас';

            // 1. Агрегация материалов для кроя
            $pos->shoeTechCard?->techCardMaterials?->each(function ($tcm) use ($qty, $customerName, &$materialsForCutting) {
                $this->aggregate($materialsForCutting, $tcm->material, $qty * $tcm->quantity, $customerName);
            });

            // 2. Агрегация подошв (всегда в штуках)
            if ($sole = $pos->shoeTechCard?->shoeSole) {
                $this->aggregateSoles($solesNeeded, $sole, $pos);
            }
        }

        return [
            'materials_for_cutting' => $materialsForCutting->sortBy('material_name')->values()->toArray(),
            'soles_needed' => $solesNeeded->sortBy('sole_name')->values()->toArray(),
        ];
    }

    private function aggregate(Collection &$col, ?Material $mat, float $val, string $customerName): void
    {
        if (!$mat) return;

        $id = $mat->id;
        $unit = $mat->materialType?->unit_id; // Это уже объект Unit Enum

        $item = $col->get($id, [
            'material_id'   => $id,
            'material_name' => $mat->name . ($mat->color ? " ({$mat->color->name})" : ""),
            // Используем метод getLabel() напрямую из Enum
            'unit_name'     => $unit instanceof Unit ? $unit->getLabel() : 'ед.',
            'total_needed'  => 0,
            'details'       => []
        ]);

        $item['total_needed'] += $val;
        $item['details'][$customerName] = ($item['details'][$customerName] ?? 0) + $val;

        $col->put($id, $item);
    }

    private function aggregateSoles(Collection &$soles, $sole, $pos): void
    {
        $id = $sole->id;
        $qty = $pos->quantity;
        $customerName = $pos->order?->customer?->name ?? 'Запас';
        $sizeId = $pos->size_id ?? 0;

        $item = $soles->get($id, [
            'sole_id'      => $id,
            'sole_name'    => $sole->fullName,
            'sizes'        => [],
            'total_needed' => 0,
            'details'      => []
        ]);

        $item['sizes'][$sizeId] = ($item['sizes'][$sizeId] ?? 0) + $qty;
        $item['total_needed'] += $qty;
        $item['details'][$customerName] = ($item['details'][$customerName] ?? 0) + $qty;

        $soles->put($id, $item);
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
