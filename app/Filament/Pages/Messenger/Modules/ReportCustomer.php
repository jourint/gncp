<?php

namespace App\Filament\Pages\Messenger\Modules;

use App\Models\Customer;
use App\Models\MessengerReport;
use App\Services\Messenger\MessengerService;
use App\Services\Messenger\ReportGeneratorService;
use App\Enums\MessengerDriver;
use Filament\Notifications\Notification;
use Livewire\WithPagination;

class ReportCustomer extends MessengerModule
{
    use WithPagination;

    // Следуем абстрактным методам твоего MessengerModule
    public static function getTitle(): string
    {
        return 'Отчеты: Заказы';
    }


    public static function getIcon(): string
    {
        return 'heroicon-o-building-storefront';
    }

    public $reportDate;
    public $search = '';
    public $previewContent = '';

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    /**
     * Сборка отчетов по базе заказов
     */
    public function generateReports(ReportGeneratorService $generator): void
    {
        $customers = Customer::whereHas('orders', function ($q) {
            $q->whereDate('started_at', $this->reportDate);
        })->get();

        $count = 0;
        foreach ($customers as $customer) {
            // Генерируем текст отчета через сервис
            $content = $generator->generateForCustomer($customer->id, $this->reportDate);

            MessengerReport::updateOrCreate(
                [
                    'reportable_id' => $customer->id,
                    'reportable_type' => 'customer',
                    'production_date' => $this->reportDate,
                    'report_type' => 'customer',
                ],
                ['content' => $content]
            );
            $count++;
        }

        Notification::make()
            ->title('Сборка завершена')
            ->body("Подготовлено отчетов: $count")
            ->success()
            ->send();
    }

    /**
     * Массовая отправка всех "черновиков" за дату
     */
    public function sendAllPending(MessengerService $messenger): void
    {
        $reports = MessengerReport::whereDate('production_date', $this->reportDate)
            ->where('reportable_type', 'customer')
            ->whereNull('sent_at')
            ->get();

        $sent = 0;
        foreach ($reports as $report) {
            $this->send($report->id, $messenger);
            $sent++;
        }

        Notification::make()
            ->title('Массовая отправка')
            ->body("Успешно доставлено: $sent")
            ->success()
            ->send();
    }

    public function render()
    {
        return view('filament.pages.messenger.report-customer', [
            'reports' => MessengerReport::query()
                ->where('reportable_type', 'customer')
                ->where('report_type', 'customer')
                ->whereDate('production_date', $this->reportDate)
                ->whereHas('reportable', fn($q) => $q->where('name', 'iLike', "%{$this->search}%"))
                ->with('reportable')
                ->latest()
                ->paginate(10)
        ]);
    }
}
