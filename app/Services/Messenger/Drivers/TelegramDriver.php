<?php

namespace App\Services\Messenger\Drivers;

use App\Services\Messenger\DTO\IncomingMessage;
use App\Services\Messenger\DTO\MessageType;

class TelegramDriver extends AbstractMessengerDriver
{
    protected function getBaseUrl(): string
    {
        return "https://api.telegram.org/bot" . config('services.telegram.token');
    }

    protected function extractErrorMessage(array $response): ?string
    {
        // Telegram всегда возвращает описание ошибки в поле 'description'
        return $response['description'] ?? null;
    }

    public function send(string $chatId, string $text, array $options = []): bool
    {
        $response = $this->api('sendMessage', array_merge([
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML', // Стандарт для красивых уведомлений
        ], $options));

        return !empty($response['ok']);
    }

    public function parseRequest(array $rawData): IncomingMessage
    {
        // Извлекаем данные либо из обычного сообщения, либо из коллбэка кнопки
        $msg = $rawData['message'] ?? $rawData['callback_query']['message'] ?? [];
        $from = $rawData['message']['from'] ?? $rawData['callback_query']['from'] ?? [];

        $payload = $rawData['callback_query']['data'] ?? $rawData['message']['text'] ?? null;

        $type = match (true) {
            isset($rawData['callback_query']) => MessageType::CALLBACK,
            str_starts_with($payload ?? '', '/') => MessageType::COMMAND,
            default => MessageType::TEXT,
        };

        return new IncomingMessage(
            senderId: (string)($from['id'] ?? ''),
            chatId: (string)($msg['chat']['id'] ?? $from['id'] ?? ''),
            type: $type,
            payload: $payload,
            senderIdentifier: $from['username'] ?? null,
            senderNickname: trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')),
            metadata: [
                'update_id' => $rawData['update_id'] ?? null,
                'is_bot'    => $from['is_bot'] ?? false
            ]
        );
    }

    /**
     * Метод для локального тестирования (Long Polling).
     */
    public function getUpdates(int $offset = 0): array
    {
        $response = $this->api('getUpdates', [
            'offset'  => $offset,
            'timeout' => 2,
        ]);

        return $response['result'] ?? [];
    }

    public function setupWebhook(string $url): bool
    {
        $response = $this->api('setWebhook', ['url' => $url]);
        return !empty($response['ok']);
    }

    public function removeWebhook(): bool
    {
        $response = $this->api('deleteWebhook');
        return !empty($response['ok']);
    }

    public function getInviteUrl(string $token): string
    {
        $botUsername = config('services.telegram.bot_username');
        return "https://t.me/{$botUsername}?start={$token}";
    }
}
