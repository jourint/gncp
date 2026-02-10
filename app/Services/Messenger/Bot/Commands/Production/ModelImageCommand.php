<?php

namespace App\Services\Messenger\Bot\Commands\Production;

use App\Models\ShoeModel;
use App\Models\ShoeTechCard;
use App\Services\Messenger\Bot\Commands\AbstractCommand;
use App\Models\MessengerAccount;
use App\Services\Messenger\DTO\IncomingMessage;
use App\Services\Messenger\MessengerService;
use Illuminate\Support\Str;

class ModelImageCommand extends AbstractCommand
{
    /**
     * Право доступа, которое проверяется в родительском canHandle.
     */
    protected ?string $permissionName = 'image_view';

    /**
     * Текстовый триггер команды.
     */
    public function getTrigger(): string
    {
        return '/image';
    }

    /**
     * Описание для справки.
     */
    public function getDescription(): string
    {
        return 'Поиск фото: /image [модель] [цвет]';
    }

    /**
     * Переопределяем canHandle, чтобы разрешить ввод с аргументами (текст после /image).
     * Если не переопределить, родительский метод потребует строгого соответствия payload === '/image'.
     */
    public function canHandle(IncomingMessage $message, MessengerAccount $account): bool
    {
        // Если сообщение начинается с триггера — забираем управление
        if (Str::startsWith($message->payload, $this->getTrigger())) {
            return true;
        }

        // В остальных случаях (синонимы, FSM) полагаемся на стандартную логику
        return parent::canHandle($message, $account);
    }

    /**
     * Основная логика обработки.
     */
    public function handle(IncomingMessage $message, MessengerAccount $account): void
    {
        $payload = trim($message->payload);

        // Отрезаем "/image" и получаем хвост: "Сима бежевый"
        $queryText = trim(Str::after($payload, $this->getTrigger()));

        if (empty($queryText)) {
            app(MessengerService::class)->sendMessage($account, "ℹ️ Напишите название модели. Пример: `/image Сима` или `/image Сима бежевый`.");
            return;
        }

        // Разбиваем строку на 2 части: Модель и (опционально) Цвет
        $args = preg_split('/\s+/', $queryText, 2);
        $modelSearch = $args[0];
        $colorSearch = $args[1] ?? null;

        // 1. Сначала находим ID модели по её названию
        $shoeModel = ShoeModel::where('name', 'ilike', "%{$modelSearch}%")
            ->where('is_active', true)
            ->first();

        if (!$shoeModel) {
            app(MessengerService::class)->sendMessage($account, "😔 Модель \"{$modelSearch}\" не найдена.");
            return;
        }

        // 2. Формируем базовый запрос к тех-картам этой модели
        $query = ShoeTechCard::where('shoe_model_id', $shoeModel->id)
            ->whereNotNull('image_path')
            ->where('is_active', true);

        // 3. Логика фильтрации:
        // Если цвет указан — ищем в названии тех-карты и берем 1 фото (limit 1)
        // Если цвет не указан — берем все доступные фото этой модели
        if ($colorSearch) {
            $query->where('name', 'ilike', "%{$colorSearch}%")->limit(1);
        }

        $cards = $query->get();

        if ($cards->isEmpty()) {
            $errorMsg = $colorSearch
                ? "😔 Для модели <b>{$shoeModel->name}</b> не найдена тех-карта с цветом \"{$colorSearch}\"."
                : "😔 У модели {$shoeModel->name} пока нет загруженных фото.";

            app(MessengerService::class)->sendMessage($account, $errorMsg);
            return;
        }

        $messenger = app(MessengerService::class);

        // 4. Отправляем фото
        foreach ($cards as $card) {
            // Вызываем наш новый универсальный метод в MessengerService
            $messenger->sendPhoto($account, $card->image_path, "👟 {$card->name}");
        }
    }
}
