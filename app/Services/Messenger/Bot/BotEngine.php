<?php

namespace App\Services\Messenger\Bot;

use App\Models\MessengerAccount;
use App\Services\Messenger\DTO\IncomingMessage;
use App\Services\Messenger\MessengerService;

class BotEngine
{
    protected array $commands = [
        Commands\System\HelpCommand::class,
        Commands\System\CancelCommand::class,
        Commands\Orders\OrdersListCommand::class,
        Commands\Orders\OrdersCreateCommand::class,
        // Сюда добавляем все новые классы
    ];

    public function __construct(protected MessengerService $messengerService) {}

    public function process(MessengerAccount $account, IncomingMessage $message): void
    {
        foreach ($this->commands as $commandClass) {
            $command = app($commandClass);

            if ($command->canHandle($message, $account)) {
                // Финальный барьер безопасности
                if (!$command->isAuthorized($account)) {
                    $this->messengerService->sendMessage($account, "⛔ У вас нет прав на эту команду.");
                    return;
                }

                $command->handle($message, $account);
                return;
            }
        }

        $this->messengerService->sendMessage($account, "Неизвестная команда. Используйте /help");
    }

    public function getCommands(): array
    {
        return $this->commands;
    }
}
