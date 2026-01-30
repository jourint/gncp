<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MessengerInvite;
use App\Models\MessengerAccount;
use App\Enums\MessengerDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessengerWebhookTest extends TestCase
{
    /**
     * RefreshDatabase гарантирует, что база данных в памяти (SQLite) 
     * будет мигрирована перед тестами и очищена после.
     */
    use RefreshDatabase;

    protected string $webhookToken = 'test_secret_token';

    protected function setUp(): void
    {
        parent::setUp();

        // Устанавливаем токен в конфигурацию, чтобы контроллер мог его проверить
        config(['services.telegram.webhook_token' => $this->webhookToken]);
    }

    /**
     * Тест: Успешная привязка Telegram аккаунта через команду /start {token}
     */
    public function test_telegram_start_command_links_account_successfully(): void
    {
        // 1. Создаем сотрудника (заполняем phone, чтобы избежать IntegrityConstraintViolation)
        $employee = Employee::create([
            'name' => 'Алексей Тестов',
            'phone' => '79990000000',
        ]);

        $inviteToken = Str::random(32);

        // 2. Создаем инвайт в базе данных
        MessengerInvite::create([
            'invitable_id' => $employee->id,
            'invitable_type' => 'employee', // убедитесь, что в MorphMap это 'employee'
            'driver' => MessengerDriver::Telegram->value,
            'token' => $inviteToken,
            'expires_at' => now()->addHour(),
        ]);

        // 3. Формируем "сырой" JSON от Telegram
        $payload = [
            'update_id' => 12345,
            'message' => [
                'chat' => ['id' => '555666'],
                'from' => [
                    'id' => '555666',
                    'username' => 'testuser',
                    'first_name' => 'Ivan',
                ],
                'text' => "/start {$inviteToken}",
            ],
        ];

        // 4. Отправляем POST запрос по вашему актуальному маршруту
        $url = "/api/messenger/telegram/{$this->webhookToken}";
        $response = $this->postJson($url, $payload);

        // 5. Проверяем HTTP статус и структуру ответа
        $response->assertStatus(200)
            ->assertJson(['ok' => true]);

        // 6. Проверяем, что в таблице мессенджеров появилась связь
        $this->assertDatabaseHas('messenger_accounts', [
            'messengerable_id' => $employee->id,
            'messengerable_type' => 'employee',
            'chat_id' => '555666',
            'driver' => 'telegram',
            'is_active' => true,
        ]);

        // 7. Проверяем, что инвайт удален после использования
        $this->assertDatabaseMissing('messenger_invites', [
            'token' => $inviteToken
        ]);
    }

    /**
     * Тест: Доступ запрещен при неверном токене вебхука
     */
    public function test_webhook_returns_401_for_invalid_token(): void
    {
        $response = $this->postJson("/api/messenger/telegram/wrong-token", [
            'update_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Тест: Ошибка 404 при попытке использовать неподдерживаемый драйвер
     */
    public function test_webhook_returns_404_for_invalid_driver(): void
    {
        $response = $this->postJson("/api/messenger/unknown-service/{$this->webhookToken}", []);

        $response->assertStatus(404);
    }
}
