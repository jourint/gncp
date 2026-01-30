<?php

namespace App\Filament\Pages\Messenger\Modules;

use App\Models\Employee;
use App\Models\MessengerReport;
use App\Services\Messenger\MessengerService;
use App\Services\Messenger\ReportGeneratorService;
use App\Enums\MessengerDriver;
use App\Enums\JobPosition;
use Filament\Notifications\Notification;
use Livewire\WithPagination;

class ReportEmployee extends MessengerModule
{
    use WithPagination;

    public static function getTitle(): string
    {
        return 'Отчеты: Разнарядки';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-scissors';
    }

    public $reportDate;
    public $search = '';
    public $selectedPosition = null; // Фильтр по цеху
    public $previewContent = '';

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    public function generateReports(ReportGeneratorService $generator): void
    {
        // Ищем сотрудников, у которых была работа на эту дату
        $employeeIds = \App\Models\OrderEmployee::query()
            ->whereDate('created_at', $this->reportDate)
            ->distinct()
            ->pluck('employee_id');

        $count = 0;
        foreach ($employeeIds as $id) {
            $content = $generator->generateForEmployee($id, $this->reportDate);

            MessengerReport::updateOrCreate(
                [
                    'reportable_id' => $id,
                    'reportable_type' => 'employee',
                    'production_date' => $this->reportDate,
                    'report_type' => 'employee',
                ],
                ['content' => $content]
            );
            $count++;
        }

        Notification::make()->title('Отчеты сформированы')->body("Для сотрудников: $count")->success()->send();
    }

    /**
     * Массовая отправка по текущим фильтрам (дата, поиск, цех)
     */
    public function sendFiltered(MessengerService $messenger): void
    {
        // Строим тот же запрос, что и в render(), но только для неотправленных
        $query = MessengerReport::query()
            ->where('reportable_type', 'employee')
            ->whereDate('production_date', $this->reportDate)
            ->whereNull('sent_at');

        if ($this->search) {
            $query->whereHasMorph('reportable', [\App\Models\Employee::class], function ($q) {
                $q->where('name', 'iLike', "%{$this->search}%");
            });
        }

        if ($this->selectedPosition) {
            $query->whereHasMorph('reportable', [\App\Models\Employee::class], function ($q) {
                $q->where('job_position_id', $this->selectedPosition);
            });
        }

        $reports = $query->get();
        $sent = 0;

        foreach ($reports as $report) {
            // Используем уже существующий метод send()
            $this->send($report->id, $messenger);
            $sent++;
        }

        Notification::make()
            ->title('Массовая отправка завершена')
            ->body("Успешно отправлено: $sent")
            ->success()
            ->send();
    }

    public function render()
    {
        $query = MessengerReport::query()
            ->where('reportable_type', 'employee')
            ->where('report_type', 'employee')
            ->whereDate('production_date', $this->reportDate)
            ->with('reportable');

        // Поиск по имени через морфную связь
        if ($this->search) {
            $query->whereHasMorph('reportable', [\App\Models\Employee::class], function ($q) {
                $q->where('name', 'iLike', "%{$this->search}%");
            });
        }

        // Фильтр по цеху (job_position_id)
        if ($this->selectedPosition) {
            $query->whereHasMorph('reportable', [\App\Models\Employee::class], function ($q) {
                $q->where('job_position_id', $this->selectedPosition);
            });
        }

        return view('filament.pages.messenger.report-employee', [
            'reports' => $query->latest()->paginate(10),
            'positions' => JobPosition::cases(),
        ]);
    }
}
