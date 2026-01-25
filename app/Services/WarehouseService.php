<?php

namespace App\Services;

use App\Models\Material;
use App\Filament\Pages\Reports\StockRequirementsReport;
use Illuminate\Support\Collection;

class WarehouseService
{
    public function getStockAnalysis(string $date): Collection
    {
        $report = new StockRequirementsReport();
        $reportData = $report->execute($date);

        // Объединяем крой и стельки в одну коллекцию
        $allRequirements = collect($reportData['materials_for_cutting'])
            ->concat($reportData['materials_for_insoles']);

        if ($allRequirements->isEmpty()) {
            return collect();
        }

        // Оптимизация: один запрос к БД вместо LIKE в цикле
        $materials = Material::whereIn('id', $allRequirements->pluck('material_id'))
            ->get()
            ->keyBy('id');

        return $allRequirements->map(function ($req) use ($materials) {
            $material = $materials->get($req['material_id']);
            $stock = (float)($material?->stock_quantity ?? 0);
            $needed = (float)$req['total_needed'];
            $diff = $stock - $needed;

            return [
                'material_id'   => $req['material_id'],
                'name'          => $req['material_name'],
                'needed'        => $needed,
                'stock'         => $stock,
                'diff'          => $diff,
                'unit'          => $req['unit_name'],
                'details'       => is_array($req['details']) ? $req['details'] : [], // Защита от "offset on float"
                'status'        => $diff < 0 ? 'critical' : ($diff < ($needed * 0.2) ? 'warning' : 'ok'),
            ];
        });
    }
}
