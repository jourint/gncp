<?php

namespace App\Services\Messenger\Bot\Commands;

use App\Services\Messenger\Bot\BotCommandInterface;
use App\Models\MessengerAccount;
use App\Services\Messenger\DTO\IncomingMessage;

abstract class AbstractCommand implements BotCommandInterface
{
    /**
     * Соответствует полю 'name' в messenger_permissions (orders_list, staff_salary).
     * Для системных команд (help, cancel) оставляем null.
     */
    protected ?string $permissionName = null;

    abstract public function getTrigger(): string;
    abstract public function getDescription(): string;

    /**
     * Проверка прав через ваш трейт HasMessengerAccess
     */
    public function isAuthorized(MessengerAccount $account): bool
    {
        if ($this->permissionName === null) {
            return true;
        }

        return $account->messengerable->hasMessengerPermission($this->permissionName);
    }

    /**
     * Общая логика: команда срабатывает на триггер ИЛИ на активное состояние в БД
     */
    public function canHandle(IncomingMessage $message, MessengerAccount $account): bool
    {
        // 1. Прямой вызов команды (например, пользователь нажал /orders_list)
        if ($message->payload === $this->getTrigger()) {
            return true;
        }

        // 2. Если мы внутри процесса этой команды (состояние в БД совпадает с именем права)
        $currentState = $account->botState?->command_name;
        if ($this->permissionName && $currentState === $this->permissionName) {
            return true;
        }

        return false;
    }
}
