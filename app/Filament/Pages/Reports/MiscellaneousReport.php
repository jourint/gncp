<?php

namespace App\Filament\Pages\Reports;

use App\Models\OrderPosition;
use App\Models\Workflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class MiscellaneousReport extends BaseReport
{
    public function execute(string $date): array
    {
        $liningNames = $this->getLiningNames(
            OrderPosition::whereHas('order', fn($q) => $q->where('started_at', $date))
                ->select('material_lining_id as lining_id')->get()
        );

        return [
            'stelki'      => $this->getStelki($date, $liningNames),
            'eggs'        => $this->getEggs($date),
            'puffCounter' => $this->getPuffCounter($date),
            'workflows'   => $this->getWorkflows($date),
        ];
    }

    private function getStelki(string $date, Collection $liningNames): Collection
    {
        return OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->join('shoe_insoles', 'shoe_models.shoe_insole_id', '=', 'shoe_insoles.id')
            ->select(
                'shoe_insoles.name',
                'shoe_insoles.type',
                'order_positions.material_lining_id as lining_id',
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $date))
            ->groupBy(['shoe_insoles.name', 'shoe_insoles.type', 'lining_id', 'order_positions.size_id'])
            ->get()
            ->map(function ($item) use ($liningNames) {
                $item->full_lining_name = $item->lining_id ? ($liningNames[$item->lining_id] ?? '') : '';
                return $item;
            });
    }

    private function getEggs(string $date): Collection
    {
        return OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->join('shoe_insoles', 'shoe_models.shoe_insole_id', '=', 'shoe_insoles.id')
            ->join('colors', 'shoe_tech_cards.color_id', '=', 'colors.id')
            ->select('colors.name as color_name', DB::raw('SUM(order_positions.quantity) as total_quantity'))
            ->whereHas('order', fn($q) => $q->where('started_at', $date))
            ->where('shoe_insoles.has_egg', true)
            ->groupBy(['colors.name'])->get();
    }

    private function getPuffCounter(string $date): Collection
    {
        return OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->select('shoe_models.puff_id', 'shoe_models.counter_id', DB::raw('SUM(order_positions.quantity) as total_quantity'))
            ->whereHas('order', fn($q) => $q->where('started_at', $date))
            ->groupBy(['shoe_models.puff_id', 'shoe_models.counter_id'])->get();
    }

    private function getWorkflows(string $date): Collection
    {
        $raw = OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->whereNotNull('shoe_models.workflows')
            ->whereHas('order', fn($q) => $q->where('started_at', $date))
            ->get(['shoe_models.workflows', 'order_positions.quantity']);

        $flat = collect();
        foreach ($raw as $item) {
            $wfs = is_string($item->workflows) ? json_decode($item->workflows, true) : $item->workflows;
            foreach ($wfs as $wfId) {
                $flat->push(['id' => $wfId, 'qty' => $item->quantity]);
            }
        }
        return $flat->groupBy('id')->map(fn($items, $id) => [
            'name' => Workflow::find($id)?->name ?? '?',
            'total_quantity' => $items->sum('qty')
        ]);
    }

    public function toExcel(string $date): Collection
    {
        $raw = $this->execute($date);
        $rows = collect();
        foreach ($raw['stelki'] as $s) {
            $rows->push(['Тип' => 'Стелька', 'Наименование' => $s->name, 'Подкладка' => $s->full_lining_name, 'Размер' => $s->size_id, 'Количество' => $s->total_quantity]);
        }
        foreach ($raw['eggs'] as $e) {
            $rows->push(['Тип' => 'Яички', 'Наименование' => $e->color_name, 'Количество' => $e->total_quantity]);
        }
        return $rows;
    }
}
