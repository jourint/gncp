<?php

namespace App\Filament\Pages\Messenger\Modules;

use App\Models\Customer;
use App\Models\MessengerReport;
use App\Models\OrderPosition;
use App\Services\Messenger\MessengerService;
use App\Services\Messenger\ReportGeneratorService;
use Filament\Notifications\Notification;
use Livewire\WithPagination;

class ReportExpedition extends MessengerModule
{
    use WithPagination;

    public $targetEmployeeId; // ID сотрудника склада

    public static function getTitle(): string
    {
        return 'Отчеты: Экспедиция';
    }
    public static function getIcon(): string
    {
        return 'heroicon-o-truck';
    }

    public $reportDate;
    public $search = '';
    public $previewContent = '';

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    public function generateReports(ReportGeneratorService $generator): void
    {
        if (!$this->targetEmployeeId) {
            Notification::make()->title('Выберите получателя')->danger()->send();
            return;
        }

        // Собираем ОДИН общий отчет по всем заказчикам для склада
        $content = $generator->generateFullExpeditionReport($this->reportDate);

        MessengerReport::updateOrCreate(
            [
                'reportable_id'   => $this->targetEmployeeId,
                'reportable_type' => 'employee', // Всегда ссылаемся на реальную модель
                'production_date' => $this->reportDate,
                'report_type'     => 'expedition', // Вот здесь разделяем логику!
            ],
            ['content' => $content]
        );

        Notification::make()->title('Общий наряд сформирован')->success()->send();
    }

    public function sendFiltered(MessengerService $messenger): void
    {
        $reports = MessengerReport::where('report_type', 'expedition')
            ->whereDate('production_date', $this->reportDate)
            ->whereNull('sent_at')
            ->get();

        foreach ($reports as $report) {
            $this->send($report->id, $messenger);
        }
    }

    public function render()
    {
        $baseQuery = MessengerReport::query()
            ->where('reportable_type', 'employee')
            ->where('report_type', 'expedition')
            ->whereDate('production_date', $this->reportDate);

        // Считаем общее количество пар во всех сформированных отчетах за день
        // Для этого парсим "Всего по клиенту: (\d+)" или считаем заново из базы (точнее)
        $grandTotal = \App\Models\OrderPosition::whereHas('order', function ($q) {
            $q->whereDate('started_at', $this->reportDate);
        })->sum('quantity');

        return view('filament.pages.messenger.report-expedition', [
            'reports' => $baseQuery->with('reportable')->latest()->paginate(10),
            'grandTotal' => $grandTotal
        ]);
    }
}
