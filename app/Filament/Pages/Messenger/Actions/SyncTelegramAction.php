<?php

namespace App\Filament\Pages\Messenger\Actions;

use App\Enums\MessengerDriver;
use App\Services\Messenger\MessengerService;
use App\Services\Messenger\Drivers\TelegramDriver;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class SyncTelegramAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sync_telegram';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Обновить (Polling)')
            ->icon('heroicon-m-arrow-path')
            ->color('gray')
            ->action(function (MessengerService $service) {
                /** @var TelegramDriver $driver */
                $driver = $service->driver(MessengerDriver::Telegram);

                $cacheKey = "tg_last_update_" . config('services.telegram.bot_username');
                $lastUpdateId = Cache::get($cacheKey, 0);

                // Используем встроенный в драйвер метод
                $updates = $driver->getUpdates($lastUpdateId + 1);

                if (empty($updates)) {
                    Notification::make()->title('Новых обновлений нет')->info()->send();
                    return;
                }

                $processed = 0;
                foreach ($updates as $update) {
                    // handleIncoming сам решит: привязка это или просто текст
                    $service->handleIncoming(MessengerDriver::Telegram, $update);

                    $lastUpdateId = $update['update_id'];
                    $processed++;
                }

                Cache::put($cacheKey, $lastUpdateId, now()->addWeeks(1));

                Notification::make()
                    ->title('Синхронизация выполнена')
                    ->body("Обработано событий: {$processed}")
                    ->success()
                    ->send();
            });
    }
}
