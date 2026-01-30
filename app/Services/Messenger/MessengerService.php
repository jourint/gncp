<?php

namespace App\Services\Messenger;

use App\Enums\MessengerDriver;
use App\Models\MessengerAccount;
use App\Models\MessengerLog;
use App\Models\MessengerInvite;
use App\Services\Messenger\DTO\IncomingMessage;
use App\Services\Messenger\Drivers\MessengerDriverInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessengerService
{
    protected array $drivers = [];

    /**
     * Динамическое получение драйвера через Enum.
     */
    public function driver(MessengerDriver $driver): MessengerDriverInterface
    {
        return $this->drivers[$driver->value] ??= app($driver->getDriverClass());
    }

    /**
     * Универсальный метод отправки сообщения конкретному аккаунту
     */
    public function sendMessage(MessengerAccount $account, string $text, array $options = []): bool
    {
        // Превращаем строку из базы в Enum, чтобы достать драйвер
        $driverEnum = $account->driver instanceof MessengerDriver
            ? $account->driver
            : MessengerDriver::tryFrom((string) $account->driver);

        if (!$driverEnum) {
            Log::error("Не удалось определить драйвер для аккаунта ID: {$account->id}");
            return false;
        }

        // 1. Создаем предварительную запись в логах
        $messengerLog = MessengerLog::create([
            'messenger_account_id' => $account->id,
            'title' => $options['title'] ?? null,
            'message' => $text,
            'status' => 'pending',
        ]);

        $driver = $this->driver($driverEnum);
        $success = $driver->send($account->chat_id, $text, $options);

        if (!$success) {
            $error = $driver->getLastError();
            // 3. Фиксируем ошибку в логах
            $messengerLog->update([
                'status' => 'failed',
                'error_message' => $error,
            ]);
            // Проверка на блокировку бота пользователем
            if ($error && (str_contains($error, 'forbidden') || str_contains($error, 'not found'))) {
                $account->update(['is_active' => false]);
            }

            Log::warning("Messenger send error [{$account->driver}]: {$error}");
        } else {
            // 2. Обновляем статус в логах на успешную отправку
            $messengerLog->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }

        return $success;
    }

    /**
     * Главная точка входа для всех мессенджеров.
     */
    public function handleIncoming(MessengerDriver $driverType, array $rawData): void
    {
        $driver = $this->driver($driverType);
        $message = $driver->parseRequest($rawData);

        // 1. Проверяем, не является ли сообщение попыткой авторизации
        if ($token = $message->getRegistrationToken()) {
            $this->processRegistration($driverType, $message, $token);
            return;
        }

        // 2. Если это обычное сообщение — обрабатываем по логике системы
        $this->processAuthenticatedMessage($driverType, $message);
    }

    protected function processRegistration(MessengerDriver $driverType, IncomingMessage $message, string $token): void
    {
        DB::transaction(function () use ($driverType, $message, $token) {
            $invite = MessengerInvite::where('token', $token)
                ->where('driver', $driverType->value)
                ->where('expires_at', '>', now())
                ->first();

            if (!$invite) {
                $this->driver($driverType)->send($message->chatId, "⚠️ Ссылка недействительна или срок её действия истек.");
                return;
            }

            MessengerAccount::updateOrCreate(
                ['driver' => $driverType->value, 'chat_id' => $message->chatId],
                [
                    'messengerable_id'   => $invite->invitable_id,
                    'messengerable_type' => $invite->invitable_type,
                    'identifier'         => $message->senderIdentifier,
                    'nickname'           => $message->senderNickname,
                    'is_active'          => true,
                ]
            );

            $invite->delete();

            $this->driver($driverType)->send(
                $message->chatId,
                "✅ <b>Успешно!</b>\nТеперь вы будете получать уведомления в этот чат."
            );
        });
    }

    protected function processAuthenticatedMessage(MessengerDriver $driverType, IncomingMessage $message): void
    {
        $account = MessengerAccount::where('driver', $driverType->value)
            ->where('chat_id', $message->chatId)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            // Опционально: можно отправить инструкцию по подключению
            return;
        }

        // Здесь реализуется логика ответов бота (например, обработка команд меню)
        if ($message->payload === '/help') {
            $this->driver($driverType)->send($message->chatId, "Список доступных команд: ...");
        }
    }
}
