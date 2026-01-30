<?php

namespace App\Services\Messenger\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

abstract class AbstractMessengerDriver implements MessengerDriverInterface
{
    protected ?string $lastError = null;

    /**
     * Базовый URL API мессенджера.
     */
    abstract protected function getBaseUrl(): string;

    /**
     * Логика извлечения сообщения об ошибке из ответа конкретного API.
     */
    abstract protected function extractErrorMessage(array $response): ?string;

    /**
     * Выполнение запроса к API.
     * По умолчанию используется POST, но может быть расширено.
     */
    public function api(string $method, array $data = [], string $httpMethod = 'post'): array
    {
        try {
            /** @var Response $response */
            $response = Http::timeout(10) // Защита от зависания
                ->connectTimeout(5)
                ->{$httpMethod}("{$this->getBaseUrl()}/{$method}", $data);

            $json = $response->json();

            if ($response->failed()) {
                // Если API вернуло ошибку (4xx, 5xx), парсим её
                $this->lastError = is_array($json)
                    ? $this->extractErrorMessage($json)
                    : "HTTP Error: {$response->status()}";

                return [];
            }

            return is_array($json) ? $json : [];
        } catch (\Throwable $e) {
            $this->lastError = "Connection Error: {$e->getMessage()}";
            return [];
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
