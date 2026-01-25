<?php

namespace App\Filament\Pages\Reports;

use App\Models\OrderPosition;
use App\Models\Material;
use Illuminate\Support\Collection;

class StockRequirementsReport implements ReportContract
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
                'shoeTechCard.shoeModel.shoeInsole'
            ])
            ->get();

        $materialsForCutting = collect();
        $materialsForInsoles = collect();

        // 2. Предварительная загрузка материалов для стелек (так как они в JSON)
        $insoleMatIds = $this->extractInsoleMatIds($orderPositions);
        $insoleMats = Material::with(['materialType.unit', 'color'])->whereIn('id', $insoleMatIds)->get()->keyBy('id');

        foreach ($orderPositions as $pos) {
            $q = $pos->quantity;
            $customerName = $pos->order?->customer?->name ?? 'Запас';

            // Агрегация материалов для кроя
            if ($pos->shoeTechCard?->techCardMaterials) {
                foreach ($pos->shoeTechCard->techCardMaterials as $tcm) {
                    $this->aggregate($materialsForCutting, $tcm->material, $q * $tcm->quantity, $customerName);
                }
            }

            // Агрегация материалов для стелек
            $insole = $pos->shoeTechCard?->shoeModel?->shoeInsole;
            if ($insole && $insole->tech_card) {
                $components = is_string($insole->tech_card) ? json_decode($insole->tech_card, true) : $insole->tech_card;
                foreach ($components as $c) {
                    $mat = $insoleMats->get($c['material_id'] ?? null);
                    if ($mat) {
                        $this->aggregate($materialsForInsoles, $mat, $q * ($c['count'] ?? 0), $customerName);
                    }
                }
            }
        }

        return [
            'materials_for_cutting' => $materialsForCutting->sortBy('material_name')->values()->toArray(),
            'materials_for_insoles' => $materialsForInsoles->sortBy('material_name')->values()->toArray(),
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

    private function extractInsoleMatIds(Collection $pos): array
    {
        return $pos->flatMap(function ($p) {
            $tc = $p->shoeTechCard?->shoeModel?->shoeInsole?->tech_card;
            $decoded = is_string($tc) ? json_decode($tc, true) : $tc;
            return collect($decoded)->pluck('material_id');
        })->filter()->unique()->toArray();
    }

    public function toExcel(string $date): Collection
    {
        $data = $this->execute($date);
        $rows = collect();

        $categories = [
            'Крой' => $data['materials_for_cutting'],
            'Стельки' => $data['materials_for_insoles']
        ];

        foreach ($categories as $catName => $items) {
            foreach ($items as $item) {
                $rows->push([
                    'Категория' => $catName,
                    'Материал'  => $item['material_name'],
                    'Кол-во'    => $item['total_needed'],
                    'Ед. изм.'  => $item['unit_name'],
                ]);
            }
        }

        return $rows;
    }
}
