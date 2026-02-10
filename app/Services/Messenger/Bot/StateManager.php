<?php

namespace App\Services\Messenger\Bot;

use App\Models\MessengerAccount;
use App\Models\MessengerBotState;

class StateManager
{
    /**
     * Установить или обновить состояние пользователя.
     * Контекст объединяется с уже существующим (array_merge).
     */
    public function setState(MessengerAccount $account, string $command, ?string $step = null, array $context = []): void
    {
        $state = $account->botState()->firstOrCreate(['messenger_account_id' => $account->id]);

        // Объединяем старый контекст с новым, чтобы не терять данные на промежуточных шагах
        $newContext = array_merge($state->context ?? [], $context);

        $state->update([
            'command_name' => $command,
            'step'         => $step,
            'context'      => $newContext,
        ]);
    }

    /**
     * Получить данные из контекста по ключу.
     */
    public function getContextParam(MessengerAccount $account, string $key, mixed $default = null): mixed
    {
        return $account->botState?->context[$key] ?? $default;
    }

    /**
     * Полная очистка состояния (после завершения команды или при отмене).
     */
    public function clearState(MessengerAccount $account): void
    {
        $account->botState()->delete();
    }

    public function setValidOptions(MessengerAccount $account, array $options): void
    {
        $this->setState($account, $account->botState->command_name, $account->botState->step, [
            '_valid_options' => $options
        ]);
    }
}
