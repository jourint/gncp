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
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use BackedEnum;

class WorkPrinting extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;
    protected string $view = 'filament.pages.work-printing';
    protected static ?string $title = 'АРМ - Продвинутые отчеты 1';
    protected static ?int $navigationSort = 3;

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

        // Группируем по модели, затем по техкарте
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

        $puffCounter = OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->select(
                'shoe_models.puff_id',
                'shoe_models.counter_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->groupBy(['shoe_models.puff_id', 'shoe_models.counter_id'])
            ->get();

        $workflows = OrderPosition::query()
            ->join('shoe_tech_cards', 'order_positions.shoe_tech_card_id', '=', 'shoe_tech_cards.id')
            ->join('shoe_models', 'shoe_tech_cards.shoe_model_id', '=', 'shoe_models.id')
            ->whereNotNull('shoe_models.workflows')
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->get(['shoe_models.name as model_name', 'shoe_models.workflows']);

        return [
            'stelki' => $stelki,
            'puffCounter' => $puffCounter,
            'workflows' => $workflows,
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
                'order_positions.size_id',
                DB::raw('SUM(order_positions.quantity) as total_quantity')
            )
            ->where('orders.started_at', $this->selected_date)
            ->groupBy([
                'customers.name',
                'shoe_models.name',
                'order_positions.size_id'
            ])
            ->orderBy('customers.name')
            ->orderBy('shoe_models.name')
            ->orderBy('order_positions.size_id')
            ->get();

        $grouped = $result->groupBy('customer_name');

        $final = collect();
        $overallTotal = 0;

        foreach ($grouped as $customerName => $itemsByCustomer) {
            $byModel = $itemsByCustomer->groupBy('model_name');

            $customerTotal = 0;

            foreach ($byModel as $modelName => $items) {
                $modelTotal = $items->sum('total_quantity');
                $customerTotal += $modelTotal;
                $overallTotal += $modelTotal;

                $final->push([
                    'type' => 'model_header',
                    'customer_name' => $customerName,
                    'model_name' => $modelName,
                    'total_quantity' => $modelTotal,
                    'sizes' => $items->sortBy('size_id')->values()
                ]);
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
}
