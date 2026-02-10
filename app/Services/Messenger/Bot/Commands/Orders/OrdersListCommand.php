<?php

namespace App\Services\Messenger\Bot\Commands\Orders;

use App\Services\Messenger\Bot\Commands\AbstractCommand;
use App\Services\Messenger\DTO\ReplyKeyboard;
use App\Models\MessengerAccount;
use App\Services\Messenger\DTO\IncomingMessage;
use App\Services\Messenger\MessengerService;

class OrdersListCommand extends AbstractCommand
{
    // Строго как в БД: messenger_permissions.name
    protected ?string $permissionName = 'orders_list';
    protected array $synonyms = ['📦 Мои заказы'];

    public function getTrigger(): string
    {
        return '/orders_list';
    }
    public function getDescription(): string
    {
        return 'Мои заказы';
    }

    public function handle(IncomingMessage $message, MessengerAccount $account): void
    {
        if ($account->messengerable->getMorphClass() !== 'customers') {
            app(MessengerService::class)->sendMessage($account, "⛔ Ошибка. Информация о заказах доступна только для клиентов.");
            return;
        }

        // Логика вывода списка...
        $keyboard = (new ReplyKeyboard())
            ->addRow(['📦 Мои заказы', '💰 Зарплата'])
            ->addRow(['❓ Помощь']);

        app(MessengerService::class)->sendMessage($account, "Вот ваши заказы...", [
            'keyboard' => $keyboard
        ]);
    }
}
