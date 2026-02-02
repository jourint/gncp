<?php

namespace App\Services\Messenger\Bot\Commands\System;

use App\Services\Messenger\Bot\Commands\AbstractCommand;
use App\Models\MessengerAccount;
use App\Services\Messenger\DTO\IncomingMessage;
use App\Services\Messenger\Bot\StateManager;
use App\Services\Messenger\MessengerService;

class CancelCommand extends AbstractCommand
{
    /**
     * Системная команда — доступна всем без проверки прав в БД
     */
    protected ?string $permissionName = null;

    public function __construct(
        protected StateManager $stateManager,
        protected MessengerService $messengerService
    ) {}

    public function getTrigger(): string
    {
        return '/cancel';
    }

    public function getDescription(): string
    {
        return 'Отменить текущую операцию';
    }

    /**
     * Переопределяем canHandle, чтобы отмена срабатывала всегда, 
     * даже если в БД висит другое активное состояние.
     */
    public function canHandle(IncomingMessage $message, MessengerAccount $account): bool
    {
        return in_array($message->payload, [$this->getTrigger(), 'Отмена', '❌ Отмена']);
    }

    public function handle(IncomingMessage $message, MessengerAccount $account): void
    {
        // 1. Удаляем состояние из messenger_bot_states
        $this->stateManager->clearState($account);

        // 2. Уведомляем пользователя
        $this->messengerService->sendMessage(
            $account,
            "❌ <b>Операция отменена.</b>\nВведите /help, чтобы увидеть список доступных функций.",
            ['parse_mode' => 'html']
        );
    }
}
