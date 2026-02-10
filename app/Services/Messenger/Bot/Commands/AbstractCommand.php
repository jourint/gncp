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

    protected array $synonyms = [];

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
        $payload = $message->payload;

        // 1. Проверка по триггеру или синонимам
        $isTriggered = ($payload === $this->getTrigger()) || in_array($payload, $this->synonyms);

        if ($isTriggered) {
            return true;
        }

        // 2. Проверка по активному состоянию (FSM)
        $currentState = $account->botState?->command_name;
        if ($this->permissionName && $currentState === $this->permissionName) {
            return true;
        }

        return false;
    }

    public function validate(IncomingMessage $message, MessengerAccount $account): bool
    {
        $validOptions = $account->botState?->context['_valid_options'] ?? null;

        // Если ограничений нет — валидация пройдена
        if (is_null($validOptions)) {
            return true;
        }

        // Добавляем системные команды в исключения (чтобы /cancel сработал всегда)
        if (in_array($message->payload, ['/cancel', '/help', 'Отмена'])) {
            return true;
        }

        return in_array($message->payload, $validOptions);
    }
}
