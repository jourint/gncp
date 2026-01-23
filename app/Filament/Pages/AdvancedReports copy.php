<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderPosition;
use App\Models\OrderEmployee;
use App\Models\ShoeModel;
use App\Models\ShoeTechCard;
use App\Models\ShoeInsole;
use App\Models\Customer;
use App\Models\Size;
use App\Models\Counter;
use App\Models\Puff;
use App\Models\Workflow;
use App\Models\Material;
use App\Models\TechCardMaterial;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use BackedEnum;

class AdvancedReports22 extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected string $view = 'filament.pages.advanced-reports';
    protected static ?string $title = 'АРМ - Отчеты по заказам';
    protected static ?int $navigationSort = 4;

    public ?string $selected_date = null;
    public ?string $active_report = null;

    public function mount(): void
    {
        $this->selected_date = now()->format('Y-m-d');
    }

    public function showReport(string $type): void
    {
        $this->active_report = $type;
    }

    public function getCuttingReportProperty(): Collection
    {
        if ($this->active_report !== 'cutting') return collect();

        $result = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->select(
                'shoe_models.name as model_name',
                'shoe_tech_cards.name as full_color_texture',
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->where('orders.started_at', $this->selected_date)
            ->groupBy([
                'shoe_models.name',
                'shoe_tech_cards.name',
                'order_positions.size_id'
            ])
            ->orderBy('shoe_models.name')
            ->orderBy('shoe_tech_cards.name')
            ->orderBy('order_positions.size_id')
            ->get();

        $grouped = $result->groupBy('model_name');

        $final = collect();
        $overallTotal = 0;

        foreach ($grouped as $modelName => $itemsByModel) {
            $byTechCard = $itemsByModel->groupBy('full_color_texture');

            $modelTotal = 0;

            foreach ($byTechCard as $techCardName => $items) {
                $techCardTotal = $items->sum('total_quantity');
                $modelTotal += $techCardTotal;
                $overallTotal += $techCardTotal;

                $final->push([
                    'type' => 'tech_card_header',
                    'model_name' => $modelName,
                    'tech_card_name' => $techCardName,
                    'total_quantity' => $techCardTotal,
                    'sizes' => $items->sortBy('size_id')->values()
                ]);
            }

            $final->push([
                'type' => 'model_total',
                'model_name' => $modelName,
                'total_quantity' => $modelTotal
            ]);
        }

        $final->push([
            'type' => 'overall_total',
            'total_quantity' => $overallTotal
        ]);

        return $final;
    }

    public function getSewingReportProperty(): Collection
    {
        if ($this->active_report !== 'sewing') return collect();

        $result = OrderEmployee::query()
            ->join('employees', 'order_employees.employee_id', '=', 'employees.id')
            ->join('order_positions', 'order_employees.order_position_id', '=', 'order_positions.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->select(
                'employees.name as employee_name',
                'shoe_tech_cards.name as tech_card_name',
                'order_positions.size_id',
                DB::raw('SUM(order_employees.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->whereHas('employee', fn($q) => $q->where('job_position_id', 2)) // 2 — швейный
            ->groupBy(['employees.name', 'shoe_tech_cards.name', 'order_positions.size_id'])
            ->orderBy('employees.name')
            ->orderBy('shoe_tech_cards.name')
            ->orderBy('order_positions.size_id')
            ->get();

        $grouped = $result->groupBy('employee_name');

        $final = collect();
        $overallTotal = 0;

        foreach ($grouped as $employeeName => $itemsByEmployee) {
            $byTechCard = $itemsByEmployee->groupBy('tech_card_name');

            $employeeTotal = 0;

            foreach ($byTechCard as $techCardName => $items) {
                $techCardTotal = $items->sum('total_quantity');
                $employeeTotal += $techCardTotal;
                $overallTotal += $techCardTotal;

                $final->push([
                    'type' => 'tech_card_header',
                    'employee_name' => $employeeName,
                    'tech_card_name' => $techCardName,
                    'total_quantity' => $techCardTotal,
                    'sizes' => $items->sortBy('size_id')->values()
                ]);
            }

            $final->push([
                'type' => 'employee_total',
                'employee_name' => $employeeName,
                'total_quantity' => $employeeTotal
            ]);
        }

        $final->push([
            'type' => 'overall_total',
            'total_quantity' => $overallTotal
        ]);

        return $final;
    }

    public function getShoemakerReportProperty(): Collection
    {
        if ($this->active_report !== 'shoemaker') return collect();

        $result = OrderEmployee::query()
            ->join('employees', 'order_employees.employee_id', '=', 'employees.id')
            ->join('order_positions', 'order_employees.order_position_id', '=', 'order_positions.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->select(
                'employees.name as employee_name',
                'shoe_tech_cards.name as tech_card_name',
                'order_positions.size_id',
                DB::raw('SUM(order_employees.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->whereHas('employee', fn($q) => $q->where('job_position_id', 3)) // 3 — сапожный
            ->groupBy(['employees.name', 'shoe_tech_cards.name', 'order_positions.size_id'])
            ->orderBy('employees.name')
            ->orderBy('shoe_tech_cards.name')
            ->orderBy('order_positions.size_id')
            ->get();

        $grouped = $result->groupBy('employee_name');

        $final = collect();
        $overallTotal = 0;

        foreach ($grouped as $employeeName => $itemsByEmployee) {
            $byTechCard = $itemsByEmployee->groupBy('tech_card_name');

            $employeeTotal = 0;

            foreach ($byTechCard as $techCardName => $items) {
                $techCardTotal = $items->sum('total_quantity');
                $employeeTotal += $techCardTotal;
                $overallTotal += $techCardTotal;

                $final->push([
                    'type' => 'tech_card_header',
                    'employee_name' => $employeeName,
                    'tech_card_name' => $techCardName,
                    'total_quantity' => $techCardTotal,
                    'sizes' => $items->sortBy('size_id')->values()
                ]);
            }

            $final->push([
                'type' => 'employee_total',
                'employee_name' => $employeeName,
                'total_quantity' => $employeeTotal
            ]);
        }

        $final->push([
            'type' => 'overall_total',
            'total_quantity' => $overallTotal
        ]);

        return $final;
    }

    public function getMiscellaneousReportProperty()
    {
        if ($this->active_report !== 'miscellaneous') return [
            'stelki' => collect(),
            'puffCounter' => collect(),
            'workflows' => collect(),
        ];

        // Стельки
        $stelki = OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_insoles', 'shoe_tech_cards.shoe_insole_id', '=', 'shoe_insoles.id')
            ->select(
                'shoe_insoles.name',
                'shoe_insoles.is_black',
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->groupBy(['shoe_insoles.name', 'shoe_insoles.is_black', 'order_positions.size_id'])
            ->orderBy('shoe_insoles.name')
            ->orderBy('shoe_insoles.is_black')
            ->orderBy('order_positions.size_id')
            ->get();

        // Подноски/Задники
        $puffCounter = OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->select(
                'shoe_models.puff_id',
                'shoe_models.counter_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->where(function ($q) {
                $q->whereNotNull('shoe_models.puff_id')
                    ->orWhereNotNull('shoe_models.counter_id');
            })
            ->groupBy(['shoe_models.puff_id', 'shoe_models.counter_id'])
            ->get();

        // Доп. работы
        $workflowsRaw = OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->whereNotNull('shoe_models.workflows')
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->get(['shoe_models.name as model_name', 'shoe_models.workflows', 'order_positions.quantity']);

        // Группируем и фильтруем
        $workflows = collect();
        foreach ($workflowsRaw as $item) {
            $wfArray = is_string($item->workflows) ? json_decode($item->workflows, true) : $item->workflows;
            if (is_array($wfArray) && count($wfArray) > 0) {
                foreach ($wfArray as $wfId) {
                    $workflows->push([
                        'model_name' => $item->model_name,
                        'workflow_id' => $wfId,
                        'quantity' => $item->quantity
                    ]);
                }
            }
        }

        // Группируем по workflow_id
        $groupedWorkflows = $workflows->groupBy('workflow_id')
            ->map(function ($items) {
                $first = $items->first();
                $wfName = \App\Models\Workflow::find($first['workflow_id'])?->name ?? 'Неизвестно';
                $totalQuantity = $items->sum('quantity');
                return [
                    'name' => $wfName,
                    'total_quantity' => $totalQuantity
                ];
            });

        return [
            'stelki' => $stelki,
            'puffCounter' => $puffCounter,
            'workflows' => $groupedWorkflows,
        ];
    }

    public function getExpeditionReportProperty(): Collection
    {
        if ($this->active_report !== 'expedition') return collect();

        $result = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->select(
                'customers.name as customer_name',
                'shoe_models.name as model_name',
                'shoe_tech_cards.name as full_color_texture', // ← Используем это поле
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->where('orders.started_at', $this->selected_date)
            ->groupBy([
                'customers.name',
                'shoe_models.name',
                'shoe_tech_cards.name', // ← Важно!
                'order_positions.size_id'
            ])
            ->orderBy('customers.name')
            ->orderBy('shoe_models.name')
            ->orderBy('shoe_tech_cards.name')
            ->orderBy('order_positions.size_id')
            ->get();

        $grouped = $result->groupBy('customer_name');

        $final = collect();
        $overallTotal = 0;

        foreach ($grouped as $customerName => $itemsByCustomer) {
            $byModel = $itemsByCustomer->groupBy(['model_name', 'full_color_texture']); // ← Группируем по модели и цвету

            $customerTotal = 0;

            foreach ($byModel as $modelGroup) {
                foreach ($modelGroup as $techCardName => $items) {
                    $techCardTotal = $items->sum('total_quantity');
                    $customerTotal += $techCardTotal;
                    $overallTotal += $techCardTotal;

                    $final->push([
                        'type' => 'model_header',
                        'customer_name' => $customerName,
                        'model_name' => $items->first()->model_name,
                        'tech_card_name' => $techCardName, // ← Полное название: "Модель / Цвет"
                        'total_quantity' => $techCardTotal,
                        'sizes' => $items->sortBy('size_id')->values()
                    ]);
                }
            }

            $final->push([
                'type' => 'customer_total',
                'customer_name' => $customerName,
                'total_quantity' => $customerTotal
            ]);
        }

        $final->push([
            'type' => 'overall_total',
            'total_quantity' => $overallTotal
        ]);

        return $final;
    }

    public function getStockRequirementsReportProperty()
    {
        if ($this->active_report !== 'stock_requirements') {
            return [
                'materials_for_cuting' => collect(),
                'materials_for_insoles' => collect(),
            ];
        }

        $targetDate = $this->selected_date;

        // 1. Получаем все позиции заказов на дату
        $orderPositions = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->select(
                'shoe_tech_cards.id as tech_card_id',
                'shoe_tech_cards.shoe_insole_id',
                'order_positions.quantity as pairs_needed'
            )
            ->where('orders.started_at', $targetDate)
            ->get();

        // 2. Собираем материалы
        $materialsForCuting = collect();
        $materialsForInsoles = collect();

        foreach ($orderPositions as $pos) {
            // Материалы из техкарты (для кроя)
            $techCardMaterials = TechCardMaterial::where('shoe_tech_card_id', $pos->tech_card_id)
                ->with(['material.color', 'material.materialType.unit'])
                ->get();

            foreach ($techCardMaterials as $tcm) {
                $needed = $pos->pairs_needed * $tcm->quantity;

                $materialsForCuting->push([
                    'material_name' => $tcm->material->full_name,
                    'unit_name' => $tcm->material->materialType?->unit?->name ?? 'ед.',
                    'quantity_needed' => $needed,
                ]);
            }

            // Материалы из стелек
            $shoeInsole = ShoeInsole::find($pos->shoe_insole_id);

            if ($shoeInsole && $shoeInsole->tech_card) {
                $insoleMaterials = is_string($shoeInsole->tech_card) ? json_decode($shoeInsole->tech_card, true) : $shoeInsole->tech_card;

                if (is_array($insoleMaterials)) {
                    foreach ($insoleMaterials as $insoleMat) {
                        $material = Material::with('color', 'materialType.unit')->find($insoleMat['material_id']);

                        if ($material) {
                            $needed = $pos->pairs_needed * $insoleMat['count'];

                            $materialsForInsoles->push([
                                'material_name' => $material->full_name,
                                'unit_name' => $material->materialType?->unit?->name ?? 'ед.',
                                'quantity_needed' => $needed,
                            ]);
                        }
                    }
                }
            }
        }

        // 3. Группируем
        $groupedCuting = $materialsForCuting->groupBy('material_name')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'material_name' => $first['material_name'],
                    'unit_name' => $first['unit_name'],
                    'total_needed' => $items->sum('quantity_needed'),
                ];
            })
            ->sortBy('material_name');

        $groupedInsoles = $materialsForInsoles->groupBy('material_name')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'material_name' => $first['material_name'],
                    'unit_name' => $first['unit_name'],
                    'total_needed' => $items->sum('quantity_needed'),
                ];
            })
            ->sortBy('material_name');

        return [
            'materials_for_cuting' => $groupedCuting,
            'materials_for_insoles' => $groupedInsoles,
        ];
    }
}
