<?php

namespace App\Filament\Pages\Messenger\Modules;

use App\Models\MessengerReport;
use App\Models\Employee;
use App\Services\Messenger\ReportGeneratorService;
use Filament\Notifications\Notification;

class ReportWarehouse extends MessengerModule
{
    public $reportDate;
    public $targetEmployeeId;

    public static function getTitle(): string
    {
        return 'Отчеты: Материалы';
    }
    public static function getIcon(): string
    {
        return 'heroicon-o-beaker';
    }

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    public function generateReports(ReportGeneratorService $generator): void
    {
        if (!$this->targetEmployeeId) {
            Notification::make()->title('Выберите кладовщика')->danger()->send();
            return;
        }

        $content = $generator->generateWarehouseMaterialsReport($this->reportDate);

        MessengerReport::updateOrCreate(
            [
                'reportable_id' => $this->targetEmployeeId,
                'reportable_type' => 'employee',
                'production_date' => $this->reportDate,
                'report_type' => 'warehouse',
            ],
            ['content' => $content]
        );

        Notification::make()->title('Сводка материалов готова')->success()->send();
    }

    public function render()
    {
        $reports = MessengerReport::query()
            ->where('report_type', 'warehouse')
            ->whereDate('production_date', $this->reportDate)
            ->with('reportable')
            ->latest()
            ->paginate(5);

        return view('filament.pages.messenger.report-warehouse', ['reports' => $reports]);
    }
}
