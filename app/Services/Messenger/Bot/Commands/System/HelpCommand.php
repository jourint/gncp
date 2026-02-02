<?php

namespace App\Services\Messenger\Bot\Commands\System;

use App\Services\Messenger\Bot\Commands\AbstractCommand;
use App\Models\MessengerAccount;
use App\Services\Messenger\DTO\IncomingMessage;
use App\Services\Messenger\Bot\BotEngine;
use App\Services\Messenger\MessengerService;

class HelpCommand extends AbstractCommand
{
    protected ?string $permissionName = null;

    public function getTrigger(): string
    {
        return '/help';
    }
    public function getDescription(): string
    {
        return 'Список доступных команд';
    }

    public function handle(IncomingMessage $message, MessengerAccount $account): void
    {
        $engine = app(BotEngine::class);
        $text = "<b>🤖 Доступные вам функции:</b>\n\n";

        foreach ($engine->getCommands() as $commandClass) {
            $cmd = app($commandClass);

            // Показываем только если есть доступ и это не сама справка
            if ($cmd->isAuthorized($account) && !($cmd instanceof self)) {
                $text .= "• {$cmd->getTrigger()} — {$cmd->getDescription()}\n";
            }
        }

        app(MessengerService::class)->sendMessage($account, $text, ['parse_mode' => 'html']);
    }
}
