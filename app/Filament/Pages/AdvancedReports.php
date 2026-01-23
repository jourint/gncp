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
use App\Models\MaterialLining;
use App\Models\TechCardMaterial;
use App\Enums\InsolesType;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use BackedEnum;

class AdvancedReports extends Page
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
            ->leftJoin('material_linings', 'order_positions.material_lining_id', '=', 'material_linings.id')
            ->select(
                'shoe_models.name as model_name',
                'shoe_tech_cards.name as full_color_texture',
                'material_linings.id as lining_id',
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->where('orders.started_at', $this->selected_date)
            ->groupBy([
                'shoe_models.name',
                'shoe_tech_cards.name',
                'material_linings.id',
                'order_positions.size_id'
            ])
            ->orderBy('shoe_models.name')
            ->orderBy('shoe_tech_cards.name')
            ->orderBy(DB::raw('COALESCE(material_linings.id, 0)'))
            ->orderBy('order_positions.size_id')
            ->get();

        $grouped = $result->groupBy(['model_name', 'full_color_texture', 'lining_id']);

        $final = collect();
        $overallTotal = 0;

        foreach ($grouped as $modelGroup) {
            $modelTotal = 0;

            foreach ($modelGroup as $techCardGroup) {
                foreach ($techCardGroup as $liningId => $items) {
                    $techCardTotal = $items->sum('total_quantity');
                    $modelTotal += $techCardTotal;
                    $overallTotal += $techCardTotal;

                    $techCardName = $items->first()->full_color_texture;
                    if ($liningId) {
                        $lining = MaterialLining::find($liningId);
                        if ($lining) {
                            $techCardName .= ' / ' . $lining->fullName;
                        }
                    }

                    $final->push([
                        'type' => 'tech_card_header',
                        'model_name' => $items->first()->model_name,
                        'tech_card_name' => $techCardName,
                        'total_quantity' => $techCardTotal,
                        'sizes' => $items->sortBy('size_id')->values()
                    ]);
                }
            }

            $final->push([
                'type' => 'model_total',
                'model_name' => $items->first()->model_name ?? $items->last()->model_name,
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
            ->leftJoin('material_linings', 'order_positions.material_lining_id', '=', 'material_linings.id')
            ->select(
                'employees.name as employee_name',
                'shoe_tech_cards.name as tech_card_name',
                'material_linings.id as lining_id',
                'order_positions.size_id',
                DB::raw('SUM(order_employees.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->whereHas('employee', fn($q) => $q->where('job_position_id', 2)) // 2 — швейный
            ->groupBy(['employees.name', 'shoe_tech_cards.name', 'material_linings.id', 'order_positions.size_id'])
            ->orderBy('employees.name')
            ->orderBy('shoe_tech_cards.name')
            ->orderBy(DB::raw('COALESCE(material_linings.id, 0)'))
            ->orderBy('order_positions.size_id')
            ->get();

        $grouped = $result->groupBy(['employee_name', 'tech_card_name', 'lining_id']);

        $final = collect();
        $overallTotal = 0;

        foreach ($grouped as $empGroup) {
            $employeeTotal = 0;

            foreach ($empGroup as $techCardGroup) {
                foreach ($techCardGroup as $liningId => $items) {
                    $techCardTotal = $items->sum('total_quantity');
                    $employeeTotal += $techCardTotal;
                    $overallTotal += $techCardTotal;

                    $techCardName = $items->first()->tech_card_name;
                    if ($liningId) {
                        $lining = MaterialLining::find($liningId);
                        if ($lining) {
                            $techCardName .= ' / ' . $lining->fullName;
                        }
                    }

                    $final->push([
                        'type' => 'tech_card_header',
                        'employee_name' => $items->first()->employee_name,
                        'tech_card_name' => $techCardName,
                        'total_quantity' => $techCardTotal,
                        'sizes' => $items->sortBy('size_id')->values()
                    ]);
                }
            }

            $final->push([
                'type' => 'employee_total',
                'employee_name' => $items->first()->employee_name ?? $items->last()->employee_name,
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
            ->leftJoin('material_linings', 'order_positions.material_lining_id', '=', 'material_linings.id')
            ->select(
                'employees.name as employee_name',
                'shoe_tech_cards.name as tech_card_name',
                'material_linings.id as lining_id',
                'order_positions.size_id',
                DB::raw('SUM(order_employees.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->whereHas('employee', fn($q) => $q->where('job_position_id', 3)) // 3 — сапожный
            ->groupBy(['employees.name', 'shoe_tech_cards.name', 'material_linings.id', 'order_positions.size_id'])
            ->orderBy('employees.name')
            ->orderBy('shoe_tech_cards.name')
            ->orderBy(DB::raw('COALESCE(material_linings.id, 0)'))
            ->orderBy('order_positions.size_id')
            ->get();

        $grouped = $result->groupBy(['employee_name', 'tech_card_name', 'lining_id']);

        $final = collect();
        $overallTotal = 0;

        foreach ($grouped as $empGroup) {
            $employeeTotal = 0;

            foreach ($empGroup as $techCardGroup) {
                foreach ($techCardGroup as $liningId => $items) {
                    $techCardTotal = $items->sum('total_quantity');
                    $employeeTotal += $techCardTotal;
                    $overallTotal += $techCardTotal;

                    $techCardName = $items->first()->tech_card_name;
                    if ($liningId) {
                        $lining = MaterialLining::find($liningId);
                        if ($lining) {
                            $techCardName .= ' / ' . $lining->fullName;
                        }
                    }

                    $final->push([
                        'type' => 'tech_card_header',
                        'employee_name' => $items->first()->employee_name,
                        'tech_card_name' => $techCardName,
                        'total_quantity' => $techCardTotal,
                        'sizes' => $items->sortBy('size_id')->values()
                    ]);
                }
            }

            $final->push([
                'type' => 'employee_total',
                'employee_name' => $items->first()->employee_name ?? $items->last()->employee_name,
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
            'eggs' => collect(),
            'puffCounter' => collect(),
            'workflows' => collect(),
        ];

        // Стельки: теперь учитываем тип (вкладная/обтяжная) и подкладку
        $stelki = OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->join('shoe_insoles', 'shoe_models.shoe_insole_id', '=', 'shoe_insoles.id')
            ->leftJoin('material_linings', 'order_positions.material_lining_id', '=', 'material_linings.id')
            ->select(
                'shoe_insoles.name',
                'shoe_insoles.type',
                'shoe_insoles.is_soft_texon',
                'material_linings.id as lining_id',
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->groupBy([
                'shoe_insoles.name',
                'shoe_insoles.type',
                'shoe_insoles.is_soft_texon',
                'material_linings.id',
                'order_positions.size_id'
            ])
            ->orderBy('shoe_insoles.name')
            ->orderBy('shoe_insoles.type')
            ->orderBy(DB::raw('COALESCE(material_linings.id, 0)'))
            ->orderBy('order_positions.size_id')
            ->get();

        // Яички: если has_egg, то количество = кол-во пар × цвет техкарты
        $eggs = OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->join('shoe_insoles', 'shoe_models.shoe_insole_id', '=', 'shoe_insoles.id')
            ->join('colors', 'shoe_tech_cards.color_id', '=', 'colors.id') // цвет техкарты
            ->select(
                'colors.name as color_name',
                'shoe_tech_cards.color_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->where('shoe_insoles.has_egg', true)
            ->groupBy(['shoe_tech_cards.color_id', 'colors.name'])
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
            'eggs' => $eggs,
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
            ->leftJoin('material_linings', 'order_positions.material_lining_id', '=', 'material_linings.id')
            ->select(
                'customers.name as customer_name',
                'shoe_models.name as model_name',
                'shoe_tech_cards.name as full_color_texture',
                'material_linings.id as lining_id',
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->where('orders.started_at', $this->selected_date)
            ->groupBy([
                'customers.name',
                'shoe_models.name',
                'shoe_tech_cards.name',
                'material_linings.id',
                'order_positions.size_id'
            ])
            ->orderBy('customers.name')
            ->orderBy('shoe_models.name')
            ->orderBy('shoe_tech_cards.name')
            ->orderBy(DB::raw('COALESCE(material_linings.id, 0)'))
            ->orderBy('order_positions.size_id')
            ->get();

        $grouped = $result->groupBy(['customer_name', 'model_name', 'full_color_texture', 'lining_id']);

        $final = collect();
        $overallTotal = 0;

        foreach ($grouped as $custGroup) {
            $customerTotal = 0;

            foreach ($custGroup as $modelGroup) {
                $modelTotal = 0;

                foreach ($modelGroup as $techGroup) {
                    foreach ($techGroup as $liningId => $items) {
                        $techCardTotal = $items->sum('total_quantity');
                        $modelTotal += $techCardTotal;
                        $customerTotal += $techCardTotal;
                        $overallTotal += $techCardTotal;

                        $techCardName = $items->first()->full_color_texture;
                        if ($liningId) {
                            $lining = MaterialLining::find($liningId);
                            if ($lining) {
                                $techCardName .= ' / ' . $lining->fullName;
                            }
                        }

                        $final->push([
                            'type' => 'model_header',
                            'customer_name' => $items->first()->customer_name,
                            'model_name' => $items->first()->model_name,
                            'tech_card_name' => $techCardName,
                            'total_quantity' => $techCardTotal,
                            'sizes' => $items->sortBy('size_id')->values()
                        ]);
                    }
                }

                $final->push([
                    'type' => 'customer_model_total',
                    'customer_name' => $items->first()->customer_name ?? $items->last()->customer_name,
                    'model_name' => $items->first()->model_name,
                    'total_quantity' => $modelTotal
                ]);
            }

            $final->push([
                'type' => 'customer_total',
                'customer_name' => $items->first()->customer_name ?? $items->last()->customer_name,
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

        $orderPositions = OrderPosition::query()
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->select(
                'shoe_tech_cards.id as tech_card_id',
                'shoe_models.shoe_insole_id',
                'order_positions.material_lining_id',
                'order_positions.quantity as pairs_needed'
            )
            ->where('orders.started_at', $targetDate)
            ->get();

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
