<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\MessengerDriver;
use App\Services\Messenger\MessengerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MessengerWebhookController extends Controller
{
    /**
     * Единая точка входа для вебхуков всех мессенджеров.
     */
    public function handle(string $driver, string $token, Request $request, MessengerService $service): JsonResponse
    {
        // 1. Проверка существования драйвера
        $driverEnum = MessengerDriver::tryFrom($driver);

        if (!$driverEnum) {
            return response()->json(['error' => 'Unsupported driver'], 404);
        }

        // 2. Безопасность: сверяем токен из URL с конфигом
        $expectedToken = config("services.{$driverEnum->value}.webhook_token");

        if (empty($expectedToken) || $token !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 3. Отправляем "сырые" данные в сервис
        // Сервис сам вызовет parseRequest драйвера и создаст IncomingMessage DTO
        try {
            $service->handleIncoming($driverEnum, $request->all());
        } catch (\Throwable $e) {
            // Логируем системную ошибку, если парсинг или БД упали
            Log::error("Webhook processing error [{$driver}]: " . $e->getMessage());

            // Мессенджеру лучше вернуть 200 (ok), чтобы он не заваливал нас повторами
            return response()->json(['ok' => false, 'error' => 'Internal processing error']);
        }

        return response()->json(['ok' => true]);
    }
}
