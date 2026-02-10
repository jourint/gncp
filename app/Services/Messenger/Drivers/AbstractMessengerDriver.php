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
            $request = Http::timeout(20)->connectTimeout(10);

            // Если в массиве $data есть ключ 'multipart', переключаемся на прикрепление файлов
            if (isset($data['multipart']) && is_array($data['multipart'])) {
                foreach ($data['multipart'] as $file) {
                    // $file должен содержать: name, contents, filename
                    $request->attach($file['name'], $file['contents'], $file['filename']);
                }
                // Удаляем служебный ключ, чтобы он не ушел в POST-полях
                unset($data['multipart']);
            }

            /** @var Response $response */
            $response = $request->{$httpMethod}("{$this->getBaseUrl()}/{$method}", $data);

            $json = $response->json();

            if ($response->failed()) {
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
