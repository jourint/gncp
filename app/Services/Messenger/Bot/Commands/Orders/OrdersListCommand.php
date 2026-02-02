<?php

namespace App\Services\Messenger\Bot\Commands\Orders;

use App\Services\Messenger\Bot\Commands\AbstractCommand;
use App\Models\MessengerAccount;
use App\Services\Messenger\DTO\IncomingMessage;
use App\Services\Messenger\MessengerService;

class OrdersListCommand extends AbstractCommand
{
    // Строго как в БД: messenger_permissions.name
    protected ?string $permissionName = 'orders_list';

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
        // Логика вывода списка...
        app(MessengerService::class)->sendMessage($account, "Ваши последние заказы: \n #123 - В работе");
    }
}
