<?php

namespace App\Services\Messenger\Bot\Commands\Orders;

use App\Services\Messenger\Bot\Commands\AbstractCommand;
use App\Models\{Order, ShoeType, ShoeModel, ShoeTechCard, MaterialLining, MessengerAccount};
use App\Services\Messenger\DTO\{IncomingMessage, ReplyKeyboard};
use Illuminate\Support\Facades\DB;
use App\Services\Messenger\MessengerService;
use App\Services\Messenger\Bot\StateManager;
use Illuminate\Support\Facades\Log;

class OrdersCreateCommand extends AbstractCommand
{
    protected ?string $permissionName = 'orders_create';
    protected array $synonyms = ['➕ Создать заказ'];

    public function __construct(
        protected StateManager $stateManager,
        protected MessengerService $messengerService
    ) {}
    public function getTrigger(): string
    {
        return '/order_new';
    }
    public function getDescription(): string
    {
        return 'Оформить заказ';
    }

    public function handle(IncomingMessage $message, MessengerAccount $account): void
    {
        Log::info("BOT DEBUG: Step: " . ($account->botState?->step ?? 'NULL') . " | Payload: " . $message->payload);

        $state = $account->botState;
        $payload = trim($message->payload);

        if (!$state || $state->command_name !== 'orders_create') {
            $this->startOrderProcess($account);
            return;
        }

        // Обработка глобальных кнопок управления
        if ($payload === '✅ Сохранить заказ и выйти') {
            $this->finalSave($account);
            return;
        }
        if ($payload === '➕ Добавить еще позицию') {
            $this->resetToTypeSelection($account);
            return;
        }

        // Лаконичный роутинг: Шаг X обрабатывает ввод и спрашивает про Шаг Y
        switch ($state->step) {
            case 'wait_date':
                $this->processDate($message, $account);
                break;
            case 'wait_type':
                $this->processType($message, $account);
                break;
            case 'wait_model':
                $this->processModel($message, $account);
                break;
            case 'wait_tech_card':
                $this->processTechCard($message, $account);
                break;
            case 'wait_lining':
                $this->processLining($message, $account);
                break;
            case 'wait_sizes':
                $this->processSizes($message, $account);
                break;
            case 'wait_quantity':
                $this->processQuantity($message, $account);
                break;
            default:
                $this->messengerService->sendMessage($account, "Используйте меню.");
        }
    }

    // --- Блок обработки шагов ---

    protected function startOrderProcess($account): void
    {
        $this->stateManager->setState($account, 'orders_create', 'wait_date');
        $this->askWithKeyboard($account, "📅 Введите дату начала (ГГГГ-ММ-ДД):", [now()->format('Y-m-d')]);
    }

    protected function processDate($message, $account): void
    {
        $this->stateManager->setState($account, 'orders_create', 'wait_type', ['date' => $message->payload]);
        $this->askWithKeyboard($account, "👠 Выберите тип обуви:", ShoeType::where('is_active', true)->pluck('name')->toArray());
    }

    protected function processType($message, $account): void
    {
        if (!$type = ShoeType::where('name', $message->payload)->first()) return;
        $this->stateManager->setState($account, 'orders_create', 'wait_model', ['type_id' => $type->id]);
        $this->askWithKeyboard($account, "👟 Выберите модель:", ShoeModel::where('shoe_type_id', $type->id)->pluck('name')->toArray());
    }

    protected function processModel($message, $account): void
    {
        if (!$model = ShoeModel::where('name', $message->payload)->first()) return;
        $this->stateManager->setState($account, 'orders_create', 'wait_tech_card', ['model_id' => $model->id, 'available_sizes' => $model->available_sizes]);
        $this->askWithKeyboard($account, "🛠 Выберите тех-карту:", ShoeTechCard::where('shoe_model_id', $model->id)->pluck('name')->toArray());
    }

    protected function processTechCard($message, $account): void
    {
        if (!$card = ShoeTechCard::where('name', $message->payload)->first()) return;
        $this->stateManager->setState($account, 'orders_create', 'wait_lining', ['tech_card_id' => $card->id]);
        $linings = MaterialLining::where('is_active', true)->get()->map(fn($l) => "{$l->name} (ID:{$l->id})")->toArray();
        $this->askWithKeyboard($account, "🧵 Выберите подкладку:", $linings);
    }

    protected function processLining($message, $account): void
    {
        preg_match('/\(ID:(\d+)\)/', $message->payload, $matches);
        $this->stateManager->setState($account, 'orders_create', 'wait_sizes', ['lining_id' => $matches[1] ?? null]);
        $sizes = $this->stateManager->getContextParam($account, 'available_sizes') ?? [];
        $this->messengerService->sendMessage($account, "📏 Введите размеры через запятую (доступны: " . implode(',', $sizes) . "):");
    }

    protected function processSizes($message, $account): void
    {
        $sizes = array_map('trim', explode(',', $message->payload));
        $this->stateManager->setState($account, 'orders_create', 'wait_quantity', ['selected_sizes' => $sizes]);
        $this->messengerService->sendMessage($account, "🔢 Введите количество пар для каждого указанного размера:");
    }

    protected function processQuantity($message, $account): void
    {
        $context = $account->botState->context;
        $newPos = [];
        foreach ($context['selected_sizes'] as $size) {
            $newPos[] = [
                'shoe_tech_card_id' => $context['tech_card_id'],
                'material_lining_id' => $context['lining_id'],
                'size_id' => $size,
                'quantity' => (int)$message->payload,
            ];
        }
        $all = array_merge($context['positions'] ?? [], $newPos);
        $this->stateManager->setState($account, 'orders_create', 'wait_next_action', ['positions' => $all]);
        $this->askWithKeyboard($account, "📍 Позиции добавлены. Всего: " . count($all) . ". Продолжим?", ['➕ Добавить еще позицию', '✅ Сохранить заказ и выйти']);
    }

    // --- Вспомогательные методы ---

    protected function resetToTypeSelection($account): void
    {
        $this->stateManager->setState($account, 'orders_create', 'wait_type');
        $this->askWithKeyboard($account, "👠 Выберите тип обуви для новой позиции:", ShoeType::where('is_active', true)->pluck('name')->toArray());
    }

    protected function finalSave($account): void
    {
        $context = $account->botState->context;
        if (empty($context['positions'])) return;

        DB::transaction(function () use ($account, $context) {
            $order = Order::create(['customer_id' => $account->messengerable_id, 'started_at' => $context['date'] ?? now()]);
            foreach ($context['positions'] as $pos) {
                $order->positions()->create($pos);
            }
        });

        $this->stateManager->clearState($account);
        $this->messengerService->sendMessage($account, "🎉 Заказ сохранен!");
    }

    protected function askWithKeyboard($account, $text, $options): void
    {
        $kb = new ReplyKeyboard();
        foreach (array_chunk($options, 2) as $row) {
            $kb->addRow($row);
        }
        $kb->addRow(['❌ Отмена']);
        $this->stateManager->setValidOptions($account, array_merge($options, ['❌ Отмена', '✅ Сохранить заказ и выйти', '➕ Добавить еще позицию']));
        $this->messengerService->sendMessage($account, $text, ['keyboard' => $kb]);
    }
}
