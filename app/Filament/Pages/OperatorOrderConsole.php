<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\ShoeModel;
use App\Models\ShoeTechCard;
use App\Models\Size;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use BackedEnum;
use App\Models\Order;
use App\Models\OrderPosition;
use App\Enums\OrderStatus;

class OperatorOrderConsole extends Page
{
    // Твой эталонный код инициализации страницы
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;
    protected string $view = 'filament.pages.operator-order-console';
    protected static ?string $title = 'АРМ - Создание заказа';

    // Свойства компонента
    public ?int $customer_id = null;
    public ?string $delivery_date = null;
    public ?int $selected_model_id = null;
    public array $rows = [];
    public array $sizeNames = [];

    public function mount(): void
    {
        $this->delivery_date = now()->addDays()->format('Y-m-d');
        // Загружаем имена размеров один раз, чтобы не дергать базу в цикле Blade
        $this->sizeNames = Size::pluck('name', 'id')->toArray();
    }

    /**
     * Вычисляемое свойство для списка техкарт
     */
    public function getAvailableTechCardsProperty(): Collection
    {
        if (!$this->selected_model_id) return collect();

        return ShoeTechCard::query()
            ->where('shoe_model_id', $this->selected_model_id)
            ->orderBy('name')
            ->get();
    }

    /**
     * Добавление строки техкарты
     */
    public function addTechCardToOrder(int $techCardId): void
    {
        if (collect($this->rows)->contains('tech_card_id', $techCardId)) {
            Notification::make()->title('Уже в списке')->warning()->send();
            return;
        }

        $techCard = ShoeTechCard::with('shoeModel')->find($techCardId);
        if (!$techCard || !$techCard->shoeModel) return;

        // Предполагаем, что доступные размеры — это массив ID
        $availableSizeIds = $techCard->shoeModel->available_sizes ?? [];

        $grid = [];
        foreach ($availableSizeIds as $sizeId) {
            $grid[$sizeId] = 0;
        }

        $this->rows[] = [
            'tech_card_id' => $techCard->id,
            'tech_card_name' => $techCard->name,
            'shoe_model_id' => $techCard->shoe_model_id,
            'grid' => $grid,
        ];
    }

    /**
     * Хук для фикса пробелов и букв в полях ввода
     */
    public function updatedRows($value, $name): void
    {
        if (str_contains($name, '.grid.')) {
            $parts = explode('.', $name);
            $rowIdx = $parts[0];
            $sizeId = $parts[2];

            $val = $this->rows[$rowIdx]['grid'][$sizeId];

            // Принудительно чистим данные, чтобы array_sum в Blade не падал
            $this->rows[$rowIdx]['grid'][$sizeId] = (is_numeric($val) && (int)$val >= 0)
                ? (int)$val
                : 0;
        }
    }

    /**
     * Копирование в следующую ТК
     */
    public function nextTechCard(int $index): void
    {
        $currentRow = $this->rows[$index];

        $nextTc = ShoeTechCard::query()
            ->where('shoe_model_id', $currentRow['shoe_model_id'])
            ->where('id', '>', $currentRow['tech_card_id'])
            ->orderBy('id', 'asc')
            ->first();

        if ($nextTc) {
            $this->addTechCardToOrder($nextTc->id);
            $lastIdx = count($this->rows) - 1;
            // Если техкарта успешно добавлена (не дубль), копируем сетку
            if ($this->rows[$lastIdx]['tech_card_id'] === $nextTc->id) {
                $this->rows[$lastIdx]['grid'] = $currentRow['grid'];
            }
        } else {
            Notification::make()->title('Это последняя техкарта')->info()->send();
        }
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function saveOrder(): void
    {
        // 1. Валидация
        if (!$this->customer_id) {
            Notification::make()->title('Ошибка')->body('Выберите заказчика')->danger()->send();
            return;
        }

        // Оставляем только строки, где общая сумма пар > 0
        $rowsToSave = collect($this->rows)->filter(function ($row) {
            return collect($row['grid'])->sum() > 0;
        });

        if ($rowsToSave->isEmpty()) {
            Notification::make()->title('Пустой заказ')->body('Введите количество хотя бы для одного размера')->warning()->send();
            return;
        }

        try {
            DB::transaction(function () use ($rowsToSave) {
                // 2. Создаем основной заказ (таблица orders)
                // Используем твой статус по умолчанию или OrderStatus::Pending->value
                $order = Order::create([
                    'customer_id' => $this->customer_id,
                    'started_at'  => $this->delivery_date ?? now()->addDay(),
                    'status'      => OrderStatus::Pending->value, // Или просто 'pending'
                    'comment'     => null, // Можно добавить свойство $comment в класс и привязать к textarea
                ]);

                // 3. Заполняем позиции (таблица order_positions)
                foreach ($rowsToSave as $row) {
                    foreach ($row['grid'] as $sizeId => $quantity) {
                        if ($quantity > 0) {
                            OrderPosition::create([
                                'order_id'           => $order->id,
                                'shoe_tech_card_id'  => $row['tech_card_id'],
                                'size_id'            => $sizeId,
                                'quantity'           => (int)$quantity,
                            ]);
                        }
                    }
                }
            });

            // 4. Успех и очистка
            Notification::make()
                ->title('Заказ №' . DB::getPdo()->lastInsertId() . ' создан')
                ->success()
                ->send();

            // Сброс формы для нового ввода
            $this->reset(['rows', 'selected_model_id', 'customer_id']);
            $this->delivery_date = now()->addDay()->format('Y-m-d');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при сохранении')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
