<?php

namespace App\Filament\Pages;

use App\Models\ShoeTechCard;
use App\Models\Size;
use App\Models\MaterialLining;
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
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;
    protected string $view = 'filament.pages.operator-order-console';
    protected static ?string $title = 'АРМ - Оператор заказов';
    protected static ?int $navigationSort = 1;

    public ?int $customer_id = null;
    public ?string $delivery_date = null;
    public ?int $selected_model_id = null;
    public array $rows = [];
    public array $sizeNames = [];
    public array $liningNames = [];

    public function mount(): void
    {
        $this->delivery_date = now()->addDays()->format('Y-m-d');
        $this->sizeNames = Size::pluck('name', 'id')->toArray();
        $this->liningNames = MaterialLining::with('color')->get()->mapWithKeys(function ($lining) {
            return [$lining->id => $lining->fullName];
        })->toArray();
    }

    public function getAvailableTechCardsProperty(): Collection
    {
        if (!$this->selected_model_id) return collect();

        return ShoeTechCard::query()
            ->where('shoe_model_id', $this->selected_model_id)
            ->orderBy('name')
            ->get();
    }

    public function getAvailableLiningsProperty(): Collection
    {
        return MaterialLining::with('color')->orderBy('name')->get();
    }

    public function addTechCardToOrder(int $techCardId): void
    {
        $techCard = ShoeTechCard::with('shoeModel')->find($techCardId);

        if (!$techCard || !$techCard->shoeModel) return;

        $availableSizeIds = $techCard->shoeModel->available_sizes ?? [];
        $grid = [];
        foreach ($availableSizeIds as $sizeId) {
            $grid[$sizeId] = 0;
        }

        $key = $techCardId . '_null';

        $this->rows[] = [
            'key' => $key,
            'tech_card_id' => $techCard->id,
            'lining_id' => null,
            'tech_card_name' => $techCard->name,
            'lining_name' => null,
            'shoe_model_id' => $techCard->shoe_model_id,
            'grid' => $grid,
        ];
    }

    public function updateLiningForRow(int $index, $liningId): void
    {
        if ($liningId === '' || $liningId === null || $liningId === 0) {
            // Не обновляем, если пусто
            return;
        }

        $liningId = (int)$liningId;

        $lining = MaterialLining::find($liningId);
        if (!$lining) return;

        $techCardId = $this->rows[$index]['tech_card_id'];
        $newKey = $techCardId . '_' . $liningId;

        // Проверяем на дубль
        if (collect($this->rows)->contains('key', $newKey)) {
            Notification::make()->title('Уже в списке')->warning()->send();
            // Возвращаем предыдущее значение
            $prevLiningId = $this->rows[$index]['lining_id'];
            $this->rows[$index]['key'] = $techCardId . '_' . ($prevLiningId ?? 'null');
            return;
        }

        $this->rows[$index]['key'] = $newKey;
        $this->rows[$index]['lining_id'] = $liningId;
        $this->rows[$index]['lining_name'] = $lining->fullName;
    }

    public function updatedRows($value, $name): void
    {
        if (str_contains($name, '.grid.')) {
            $parts = explode('.', $name);
            $rowIdx = $parts[0];
            $sizeId = $parts[2];

            $val = $this->rows[$rowIdx]['grid'][$sizeId];

            $this->rows[$rowIdx]['grid'][$sizeId] = (is_numeric($val) && (int)$val >= 0)
                ? (int)$val
                : 0;
        }
    }

    public function nextTechCard(int $index): void
    {
        $currentRow = $this->rows[$index];

        $nextTc = ShoeTechCard::query()
            ->where('shoe_model_id', $currentRow['shoe_model_id'])
            ->where('id', '>', $currentRow['tech_card_id'])
            ->orderBy('id', 'asc')
            ->first();

        if ($nextTc) {
            $liningId = $currentRow['lining_id'];
            $newKey = $nextTc->id . '_' . ($liningId ?? 'null');

            if (collect($this->rows)->contains('key', $newKey)) {
                Notification::make()->title('Уже в списке')->warning()->send();
                return;
            }

            $this->rows[] = [
                'key' => $newKey,
                'tech_card_id' => $nextTc->id,
                'lining_id' => $liningId,
                'tech_card_name' => $nextTc->name,
                'lining_name' => $currentRow['lining_name'],
                'shoe_model_id' => $nextTc->shoe_model_id,
                'grid' => $currentRow['grid'],
            ];
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
        if (!$this->customer_id) {
            Notification::make()->title('Ошибка')->body('Выберите заказчика')->danger()->send();
            return;
        }

        $rowsToSave = collect($this->rows)->filter(function ($row) {
            return collect($row['grid'])->sum() > 0;
        })->filter(function ($row) {
            return $row['lining_id'] !== null && $row['lining_id'] !== 0; // ✅ Проверяем на 0 тоже
        });

        if ($rowsToSave->isEmpty()) {
            Notification::make()->title('Пустой заказ')->body('Введите количество хотя бы для одного размера')->warning()->send();
            return;
        }

        try {
            DB::transaction(function () use ($rowsToSave) {
                $order = Order::create([
                    'customer_id' => $this->customer_id,
                    'started_at'  => $this->delivery_date ?? now()->addDay(),
                    'status'      => OrderStatus::Pending->value,
                    'comment'     => null,
                ]);

                foreach ($rowsToSave as $row) {
                    foreach ($row['grid'] as $sizeId => $quantity) {
                        if ($quantity > 0) {
                            OrderPosition::create([
                                'order_id'           => $order->id,
                                'shoe_tech_card_id'  => $row['tech_card_id'],
                                'material_lining_id' => $row['lining_id'], // ✅ Теперь всегда число
                                'size_id'            => $sizeId,
                                'quantity'           => (int)$quantity,
                            ]);
                        }
                    }
                }
            });

            Notification::make()
                ->title('Заказ создан')
                ->success()
                ->send();

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
