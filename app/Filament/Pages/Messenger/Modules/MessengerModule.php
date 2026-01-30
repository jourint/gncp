<?php

namespace App\Filament\Pages\Messenger\Modules;

use Livewire\Component;
use App\Models\MessengerReport;
use App\Services\Messenger\MessengerService;
use Filament\Notifications\Notification;

abstract class MessengerModule extends Component
{
    public $previewContent = '';

    abstract public static function getTitle(): string;
    abstract public static function getIcon(): string;

    // Общий метод предпросмотра для всех модулей
    public function openPreview(int $reportId): void
    {
        $report = MessengerReport::find($reportId);
        $this->previewContent = $report?->content ?? 'Ошибка: контент не найден';
        $this->dispatch('open-modal', id: 'preview-modal');
    }

    // Общий метод отправки (базовая реализация)
    public function send(int $reportId, MessengerService $messenger): void
    {
        $report = MessengerReport::with('reportable.messengerAccounts')->find($reportId);

        if (!$report) return;

        $account = $report->reportable->messengerAccounts()
            ->where('is_active', true)
            ->first();

        if (!$account) {
            Notification::make()->title('Ошибка')->body("У получателя не привязан мессенджер")->danger()->send();
            return;
        }

        if ($messenger->sendMessage($account, $messenger->formatForTelegram($report->content))) {
            $report->update(['sent_at' => now()]);
            Notification::make()->title('Отправлено успешно')->success()->send();
        }
    }
}
