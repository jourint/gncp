<?php

namespace App\Filament\Pages;

use App\Services\Reports\ReportService;
use App\Traits\CanExportCsv;

use App\Models\JobPosition;
use App\Filament\Pages\Reports\ReportContract;
use App\Filament\Pages\Reports\ProductionReport;
use App\Filament\Pages\Reports\MiscellaneousReport;
use App\Filament\Pages\Reports\ExpeditionReport;
use App\Filament\Pages\Reports\StockRequirementsReport;
use App\Filament\Pages\Reports\SalaryReport;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Barryvdh\DomPDF\Facade\Pdf;
use BackedEnum;

class AdvancedReports extends Page
{
    use CanExportCsv;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;
    protected string $view = 'filament.pages.advanced-reports';
    protected static ?string $title = 'АРМ - Отчеты по заказам';
    protected static ?int $navigationSort = 3;

    public ?string $selected_date = null;
    public ?string $active_report = null;

    public function mount(): void
    {
        $this->selected_date = now()->addDay()->format('Y-m-d');
    }

    /**
     * Computed Property для данных отчета
     */
    public function getReportDataProperty()
    {
        if (!$this->active_report) return collect();

        return app(ReportService::class)->getData($this->active_report, $this->selected_date);
    }

    public function showReport(string $type): void
    {
        $this->active_report = $type;
    }

    public function exportToPdf()
    {
        if (!$this->active_report) return null;

        $pdf = Pdf::loadView('filament.pages.reports.pdf-export', [
            'active_report' => $this->active_report,
            'selected_date' => $this->selected_date,
            'data'          => $this->report_data
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "report-{$this->active_report}-{$this->selected_date}.pdf"
        );
    }

    public function exportToExcel(string $type)
    {
        $module = app(ReportService::class)->getModule($type);
        if (!$module) return null;

        $data = $module->toExcel($this->selected_date);

        return $this->streamCsv($data, "report-{$type}-{$this->selected_date}.csv");
    }
}
