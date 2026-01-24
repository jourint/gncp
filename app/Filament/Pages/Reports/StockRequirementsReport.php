<?php

namespace App\Filament\Pages\Reports;

use App\Models\OrderPosition;
use App\Models\Material;
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
                'shoeTechCard.techCardMaterials.material.materialType.unit',
                'shoeTechCard.shoeModel.shoeInsole'
            ])->get();

        $materialsForCutting = collect();
        $materialsForInsoles = collect();

        $insoleMatIds = $this->extractInsoleMatIds($orderPositions);
        $insoleMats = Material::with(['materialType', 'color'])->whereIn('id', $insoleMatIds)->get()->keyBy('id');

        foreach ($orderPositions as $pos) {
            $q = $pos->quantity;

            // Крой
            if ($pos->shoeTechCard?->techCardMaterials) {
                foreach ($pos->shoeTechCard->techCardMaterials as $tcm) {
                    $this->aggregate($materialsForCutting, $tcm->material, $q * $tcm->quantity);
                }
            }

            // Стельки
            $insole = $pos->shoeTechCard?->shoeModel?->shoeInsole;
            if ($insole && $insole->tech_card) {
                $components = is_string($insole->tech_card) ? json_decode($insole->tech_card, true) : $insole->tech_card;
                foreach ($components as $c) {
                    $mat = $insoleMats->get($c['material_id'] ?? null);
                    if ($mat) $this->aggregate($materialsForInsoles, $mat, $q * ($c['count'] ?? 0));
                }
            }
        }

        return [
            'materials_for_cutting' => $materialsForCutting->sortBy('material_name')->values(),
            'materials_for_insoles' => $materialsForInsoles->sortBy('material_name')->values(),
        ];
    }

    private function aggregate(Collection &$col, ?Material $mat, float $val): void
    {
        if (!$mat) return;
        $id = $mat->id;
        $item = $col->get($id, [
            'material_name' => $mat->name . ($mat->color ? " ({$mat->color->name})" : ""),
            'unit_name' => $mat->materialType?->unit->label ?? 'ед.',
            'total_needed' => 0
        ]);
        $item['total_needed'] += $val;
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
        return collect($data['materials_for_cutting'])->map(fn($i) => array_merge($i, ['Категория' => 'Крой']))
            ->concat(collect($data['materials_for_insoles'])->map(fn($i) => array_merge($i, ['Категория' => 'Стельки'])))
            ->map(fn($i) => [
                'Категория' => $i['Категория'],
                'Материал'  => $i['material_name'],
                'Кол-во'    => $i['total_needed'],
                'Ед. изм.'  => $i['unit_name']
            ]);
    }
}
