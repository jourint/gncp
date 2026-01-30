<?php

namespace App\Services\Messenger\Drivers;

use App\Services\Messenger\DTO\IncomingMessage;

interface MessengerDriverInterface
{
    /**
     * Отправка текстового сообщения.
     * @param array $options Дополнительные параметры (parse_mode, reply_markup и т.д.)
     */
    public function send(string $chatId, string $text, array $options = []): bool;

    /**
     * Генерация ссылки для приглашения по токену.
     */
    public function getInviteUrl(string $token): string;

    /**
     * Низкоуровневый метод для прямого взаимодействия с API мессенджера.
     */
    public function api(string $method, array $data = []): array;

    /**
     * Преобразование сырых данных от мессенджера в стандартизированный объект.
     */
    public function parseRequest(array $rawData): IncomingMessage;

    /**
     * Установка URL вебхука на стороне мессенджера.
     */
    public function setupWebhook(string $url): bool;

    /**
     * Удаление вебхука.
     */
    public function removeWebhook(): bool;

    /**
     * Получение последней ошибки, возникшей при запросе к API.
     */
    public function getLastError(): ?string;
}
