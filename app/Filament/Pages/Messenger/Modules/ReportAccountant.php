<?php

namespace App\Filament\Pages\Messenger\Modules;

use App\Models\Employee;
use App\Models\MessengerReport;
use App\Services\Messenger\MessengerService;
use App\Services\Messenger\ReportGeneratorService;
use Filament\Notifications\Notification;
use Livewire\WithPagination;

class ReportAccountant extends MessengerModule
{
    use WithPagination;

    public static function getTitle(): string
    {
        return 'Отчеты: Бухгалтерия';
    }
    public static function getIcon(): string
    {
        return 'heroicon-o-banknotes';
    }

    public $reportDate;
    public $targetEmployeeId;

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    public function generateReports(ReportGeneratorService $generator): void
    {
        if (!$this->targetEmployeeId) {
            Notification::make()->title('Выберите бухгалтера')->danger()->send();
            return;
        }

        $content = $generator->generateAccountingReport($this->reportDate);

        MessengerReport::updateOrCreate(
            [
                'reportable_id' => $this->targetEmployeeId,
                'reportable_type' => 'employee',
                'production_date' => $this->reportDate,
                'report_type' => 'accounting',
            ],
            ['content' => $content]
        );

        Notification::make()->title('Финансовый отчет готов')->success()->send();
    }

    public function sendFiltered(MessengerService $messenger): void
    {
        $reports = MessengerReport::where('report_type', 'accounting')
            ->whereDate('production_date', $this->reportDate)
            ->whereNull('sent_at')
            ->get();

        foreach ($reports as $report) {
            $this->send($report->id, $messenger);
        }
    }

    public function render()
    {
        $reports = MessengerReport::query()
            ->where('report_type', 'accounting')
            ->whereDate('production_date', $this->reportDate)
            ->with('reportable')
            ->latest()
            ->paginate(5);

        return view('filament.pages.messenger.report-accountant', ['reports' => $reports]);
    }
}
