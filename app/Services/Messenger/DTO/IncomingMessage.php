<?php

namespace App\Services\Messenger\DTO;

readonly class IncomingMessage
{
    public function __construct(
        public string $senderId,                    // ID конкретного пользователя
        public string $chatId,                      // ID чата для ответа (может быть группой)
        public MessageType $type,
        public ?string $payload = null,             // Текст сообщения или данные коллбэка
        public ?string $senderIdentifier = null,    // Username (например, @gemini_ai)
        public ?string $senderNickname = null,      // Отображаемое имя (First + Last Name)
        public array $metadata = []                 // Любые доп. данные от мессенджера (update_id и т.д.)
    ) {}

    /**
     * Пытается извлечь токен регистрации.
     * Возвращает строку с токеном, если это команда /start с параметром, иначе null.
     */
    public function getRegistrationToken(): ?string
    {
        if ($this->type !== MessageType::COMMAND || empty($this->payload)) {
            return null;
        }

        // Для Telegram формат: "/start {token}"
        if (str_starts_with($this->payload, '/start ')) {
            $token = trim(substr($this->payload, 7));

            // Проверка длины, чтобы не считать обычный "/start" за токен
            return strlen($token) === 32 ? $token : null;
        }

        return null;
    }

    public function isPrivate(): bool
    {
        return $this->chatId === $this->senderId;
    }
}
