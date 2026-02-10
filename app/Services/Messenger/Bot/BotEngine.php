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
        Commands\Production\ModelImageCommand::class,

        // Test
        //    Commands\Orders\OrdersListCommand::class,
        //    Commands\Orders\OrdersCreateCommand::class,
    ];

    public function __construct(protected MessengerService $messengerService) {}

    public function process(MessengerAccount $account, IncomingMessage $message): void
    {
        foreach ($this->commands as $commandClass) {
            $command = app($commandClass);

            if ($command->canHandle($message, $account)) {
                if (!$command->isAuthorized($account)) {
                    $this->messengerService->sendMessage($account, "⛔ Доступ запрещен.");
                    return;
                }

                // --- АВТОМАТИЧЕСКАЯ ВАЛИДАЦИЯ ---
                if (!$command->validate($message, $account)) {
                    $this->messengerService->sendMessage($account, "⚠️ Пожалуйста, выберите один из вариантов на кнопках или введите /cancel для отмены.");
                    return;
                }

                $command->handle($message, $account);
                return;
            }
        }
    }

    public function getCommands(): array
    {
        return $this->commands;
    }
}
