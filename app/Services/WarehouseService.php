<?php

namespace App\Services;

use App\Models\Material;
use App\Filament\Pages\Reports\StockRequirementsReport;
use Illuminate\Support\Collection;

class WarehouseService
{
    public function getStockAnalysis(string $date, array $reportData = null): Collection
    {
        if ($reportData === null) {
            $report = new StockRequirementsReport();
            $reportData = $report->execute($date);
        }

        // Используем только материалы для кроя (стельки больше не в этом отчёте)
        $materials = $reportData['materials_for_cutting'] ?? [];

        if (empty($materials)) {
            return collect();
        }

        // Оптимизация: один запрос к БД вместо LIKE в цикле
        $materialsCollection = Material::whereIn('id', collect($materials)->pluck('material_id'))
            ->get()
            ->keyBy('id');

        return collect($materials)->map(function ($req) use ($materialsCollection) {
            $material = $materialsCollection->get($req['material_id']);
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
