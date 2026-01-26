<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\OrderEmployee;
use App\Services\Payroll\PayrollService;
use App\Exports\PayrollCsvExporter;
use App\Traits\CanExportCsv;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class PayrollAccounting extends Page
{
    use CanExportCsv;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $title = 'АРМ - Бухгалтерия';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.payroll-accounting';

    // Состояние фильтров
    public $date_from;
    public $date_to;
    public $selected_job_position = null;
    public $search_employee = '';

    // Состояние модалок
    public $viewing_employee = null;
    public $employee_details = [];
    public $extra_works_details = [];

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->endOfMonth()->format('Y-m-d');
    }

    /**
     * Основной расчет данных (Computed Property)
     */
    public function getPayrollDataProperty()
    {
        return app(PayrollService::class)->getSummary(
            $this->date_from,
            $this->date_to,
            $this->selected_job_position,
            $this->search_employee
        );
    }

    /**
     * Кнопки в верхней части страницы (Header Actions)
     */
    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('print')
                    ->label('Печать ведомости')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->action(fn() => $this->dispatch('trigger-print', id: 'report-content-area')),


                Action::make('pay_all_filtered')
                    ->label('Оплатить всех (фильтр)')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn() => $this->payAllFiltered()),

                Action::make('export_csv')
                    ->label('Выгрузить CSV (Шахматка)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(fn() => $this->exportToCsv()),

                Action::make('open_extra_works')
                    ->label('Доп. работы')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('gray')
                    ->action(fn() => $this->openExtraWorks()),
            ])
                ->label('Действия')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('primary')
                ->button(),
        ];
    }

    /**
     * Логика экспорта
     */
    public function exportToExcel()
    {
        $exporter = app(PayrollCsvExporter::class);
        $data = $exporter->export($this->payroll_data, $this->date_from, $this->date_to);

        return $this->streamCsv($data, "payroll_{$this->date_from}_{$this->date_to}.csv");
    }

    /**
     * Детализация по конкретному сотруднику
     */
    public function openDetails(int $employeeId): void
    {
        $this->viewing_employee = Employee::find($employeeId);
        $this->employee_details = app(PayrollService::class)->getEmployeeDetails(
            $employeeId,
            $this->date_from,
            $this->date_to
        );

        $this->dispatch('open-modal', id: 'employee-details-modal');
    }

    /**
     * Детализация доп. работ
     */
    public function openExtraWorks(): void
    {
        $this->extra_works_details = app(PayrollService::class)->getExtraWorksSummary(
            $this->date_from,
            $this->date_to
        );

        $this->dispatch('open-modal', id: 'extra-works-modal');
    }

    /**
     * Массовая оплата (для всех по фильтру)
     */
    public function payAllFiltered(): void
    {
        $employeeIds = $this->payroll_data->where('debt', '>', 0)->pluck('id');

        if ($employeeIds->isEmpty()) {
            Notification::make()->title('Нет задолженностей для оплаты')->warning()->send();
            return;
        }

        $affected = OrderEmployee::query()
            ->join('orders', 'order_employees.order_id', '=', 'orders.id')
            ->whereIn('employee_id', $employeeIds)
            ->where('is_paid', false)
            ->whereBetween('orders.started_at', [$this->date_from, $this->date_to])
            ->update(['is_paid' => true]);

        Notification::make()
            ->title('Оплата произведена')
            ->body("Обновлено позиций: {$affected}")
            ->success()
            ->send();
    }

    /**
     * Оплата конкретного сотрудника
     */
    public function payAllForEmployee(int $employeeId): void
    {
        $affected = OrderEmployee::query()
            ->join('orders', 'order_employees.order_id', '=', 'orders.id')
            ->where('employee_id', $employeeId)
            ->where('is_paid', false)
            ->whereBetween('orders.started_at', [$this->date_from, $this->date_to])
            ->update(['is_paid' => true]);

        Notification::make()->title("Выплачено: {$affected} позиций")->success()->send();
    }
}
