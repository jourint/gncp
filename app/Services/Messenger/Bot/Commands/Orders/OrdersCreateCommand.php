<?php

namespace App\Services\Messenger\Bot\Commands\Orders;

use App\Services\Messenger\Bot\Commands\AbstractCommand;
use App\Models\MessengerAccount;
use App\Services\Messenger\DTO\IncomingMessage;
use App\Services\Messenger\Bot\StateManager;
use App\Services\Messenger\MessengerService;

class OrdersCreateCommand extends AbstractCommand
{
    protected ?string $permissionName = 'orders_create';

    public function __construct(
        protected StateManager $stateManager,
        protected MessengerService $messengerService
    ) {}

    public function getTrigger(): string
    {
        return '/order_new';
    }
    public function getDescription(): string
    {
        return 'Создать новый заказ';
    }

    public function handle(IncomingMessage $message, MessengerAccount $account): void
    {
        $state = $account->botState;

        // 1. Инициализация (переход на первый шаг)
        if (!$state || $state->command_name !== 'orders_create') {
            $this->stateManager->setState($account, 'orders_create', 'wait_title');
            $this->messengerService->sendMessage($account, "📝 Введите название заказа:");
            return;
        }

        // 2. Обработка названия, переход к количеству
        if ($state->step === 'wait_title') {
            $this->stateManager->setState($account, 'orders_create', 'wait_quantity', [
                'title' => $message->payload // Сохранили название
            ]);
            $this->messengerService->sendMessage($account, "🔢 Сколько штук требуется?");
            return;
        }

        // 3. Завершение: берем название из контекста, а количество из сообщения
        if ($state->step === 'wait_quantity') {
            $title = $this->stateManager->getContextParam($account, 'title');
            $quantity = (int) $message->payload;

            // Логика БД: Order::create(['title' => $title, 'qty' => $quantity...]);

            $this->messengerService->sendMessage($account, "✅ Заказ «{$title}» ({$quantity} шт.) успешно создан!");

            // Сбрасываем всё
            $this->stateManager->clearState($account);
        }
    }
}
