<?php

namespace App\Services\Messenger\Bot;

use App\Models\MessengerAccount;
use App\Services\Messenger\DTO\IncomingMessage;

interface BotCommandInterface
{
    public function canHandle(IncomingMessage $message, MessengerAccount $account): bool;
    public function handle(IncomingMessage $message, MessengerAccount $account): void;
    public function isAuthorized(MessengerAccount $account): bool;
}
