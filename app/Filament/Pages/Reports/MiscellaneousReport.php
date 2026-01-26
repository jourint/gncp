<?php

namespace App\Filament\Pages\Reports;

use App\Models\OrderPosition;
use App\Models\Workflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\MaterialLining;
use App\Models\Puff;
use App\Models\Counter;


class MiscellaneousReport extends BaseReport
{
    public function execute(string $date): array
    {
        // Собираем все ID, которые участвуют в отчете
        $dataRaw = OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->whereHas('order', fn($q) => $q->where('started_at', $date))
            ->select('material_lining_id', 'shoe_models.puff_id', 'shoe_models.counter_id')
            ->get();

        // 1. Готовим словари имен ОДНИМ запросом на каждый справочник
        $liningNames = MaterialLining::with('color')
            ->whereIn('id', $dataRaw->pluck('material_lining_id')->filter()->unique())
            ->get()->mapWithKeys(fn($l) => [$l->id => $l->fullName]);

        $puffNames = Puff::whereIn('id', $dataRaw->pluck('puff_id')->filter()->unique())
            ->pluck('name', 'id');

        $counterNames = Counter::whereIn('id', $dataRaw->pluck('counter_id')->filter()->unique())
            ->pluck('name', 'id');

        return [
            'stelki'       => $this->getStelki($date),
            'eggs'         => $this->getEggs($date),
            'puffCounter'  => $this->getPuffCounter($date),
            'workflows'    => $this->getWorkflows($date),
            // Передаем словари в Blade
            'liningNames'  => $liningNames,
            'puffNames'    => $puffNames,
            'counterNames' => $counterNames,
        ];
    }

    private function getStelki(string $date): Collection
    {
        return OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->join('shoe_insoles', 'shoe_models.shoe_insole_id', '=', 'shoe_insoles.id')
            ->select(
                'shoe_insoles.name',
                'shoe_insoles.type',
                'shoe_insoles.is_soft_texon',
                'order_positions.material_lining_id as lining_id',
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $date))
            ->groupBy(['shoe_insoles.name', 'shoe_insoles.type', 'shoe_insoles.is_soft_texon', 'lining_id', 'order_positions.size_id'])
            ->get();
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
            ->select(
                'shoe_models.puff_id',
                'shoe_models.counter_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $date))
            ->groupBy(['shoe_models.puff_id', 'shoe_models.counter_id'])
            // ОПТИМИЗАЦИЯ: Жадная загрузка для Blade
            ->with(['shoeTechCard.shoeModel.puff', 'shoeTechCard.shoeModel.counter'])
            ->get();
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
        $allWfIds = collect();

        foreach ($raw as $item) {
            $wfs = is_string($item->workflows) ? json_decode($item->workflows, true) : $item->workflows;
            if (!$wfs) continue;
            foreach ($wfs as $wfId) {
                $flat->push(['id' => $wfId, 'qty' => $item->quantity]);
                $allWfIds->push($wfId);
            }
        }

        // ОПТИМИЗАЦИЯ: Грузим все названия Workflow ОДНИМ запросом
        $wfNames = Workflow::whereIn('id', $allWfIds->unique())->pluck('name', 'id');

        return $flat->groupBy('id')->map(fn($items, $id) => [
            'name' => $wfNames[$id] ?? '?',
            'total_quantity' => $items->sum('qty')
        ]);
    }

    public function toExcel(string $date): Collection
    {
        $data = $this->execute($date);
        $rows = collect();

        foreach ($data['stelki'] as $s) {
            // ИСПРАВЛЕНО: Берем имя подкладки из словаря liningNames
            $liningName = $s->lining_id ? ($data['liningNames'][$s->lining_id] ?? 'Неизвестно') : 'Без подкладки';

            $rows->push([
                'Тип' => 'Стелька',
                'Наименование' => $s->name,
                'Подкладка' => $liningName,
                'Размер' => $s->size_id,
                'Количество' => $s->total_quantity
            ]);
        }

        foreach ($data['eggs'] as $e) {
            $rows->push([
                'Тип' => 'Яички',
                'Наименование' => $e->color_name,
                'Количество' => $e->total_quantity
            ]);
        }

        return $rows;
    }
}
