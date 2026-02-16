<?php

namespace App\Filament\Pages;

use App\Models\{Order, Material, MaterialType, ShoeSole, ShoeSoleItem, MaterialMovement};
use App\Enums\MovementType;
use App\Filament\Pages\Reports\StockRequirementsReport;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use App\Services\WarehouseService;
use App\Enums\OrderStatus;

class WarehouseManagerConsole extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;
    protected string $view = 'filament.pages.warehouse-manager-console';
    protected static ?string $title = 'АРМ - Складской учет';
    protected static ?int $navigationSort = 10;

    public ?string $selected_date = null;
    public string $movementType = MovementType::Income->value;
    public string $entityType = 'materials';

    public array $items = [];
    public array $expandedNodes = [];
    public bool $isMovementModalOpen = false;
    private ?array $cachedStockReport = null;
    public string $searchStock = '';
    public ?int $editingMaterialId = null;
    public float $editQuantity = 0;
    public string $activeTab = 'materials';



    public function mount(): void
    {
        $this->selected_date = now()->format('Y-m-d');
    }

    public function getHasActiveOrdersProperty(): bool
    {
        return Order::query()
            ->whereDate('started_at', $this->selected_date)
            ->whereNotIn('status', [
                OrderStatus::Completed->value,
                OrderStatus::Cancelled->value
            ])
            ->exists();
    }

    /** 
     * Начало редактирования
     */
    public function cancelEditing(): void
    {
        $this->editingMaterialId = null;
    }

    // Группировка материалов: Тип -> Базовое имя -> Список цветов
    public function getInventoryGroupsProperty()
    {
        return Material::query()
            ->with(['materialType', 'color'])
            ->where('stock_quantity', '!=', 0)
            ->where('is_active', true)
            ->when($this->searchStock, fn($q) => $q->where('name', 'ilike', "%{$this->searchStock}%"))
            ->get()
            ->groupBy([
                'material_type_id',
                fn($item) => trim(str_ireplace($item->color?->name, '', $item->name))
            ]);
    }

    public function getSoleInventoryProperty()
    {
        return ShoeSoleItem::query()
            ->with(['shoeSole.color', 'size'])
            ->where('stock_quantity', '!=', 0)
            ->get()
            ->sortBy([
                ['shoeSole.name', 'asc'],
                ['size.name', 'asc'],
            ])
            ->groupBy('shoe_sole_id');
    }

    public function startEditing(int $id)
    {
        $this->editingMaterialId = $id;
        $this->editQuantity = 0;
    }

    public function applyQuickMovement(string $typeValue, string $alias): void
    {
        if ($this->editQuantity <= 0) return;

        $modelClass = match ($alias) {
            'material' => Material::class,
            'sole' => ShoeSoleItem::class,
            default => null,
        };

        if (!$modelClass) return;

        DB::transaction(function () use ($typeValue, $modelClass) {
            $record = $modelClass::findOrFail($this->editingMaterialId);

            $record->movements()->create([
                'type' => $typeValue, // Enum подхватит строку сам
                'quantity' => $this->editQuantity,
                'description' => "Корректировка через АРМ",
                // user_id заполнится сам через booted в модели
            ]);
        });

        $this->editingMaterialId = null;
        $this->editQuantity = 0;
        Notification::make()->success()->title('Запас обновлен')->send();
    }

    /**
     * Расчет дефицита на основе заказов
     */
    public function getStockAnalysisProperty()
    {
        $report = $this->getStockReport();
        return app(WarehouseService::class)->getStockAnalysis($this->selected_date, $report);
    }

    /**
     * Получить кэшированный отчёт
     */
    private function getStockReport(): array
    {
        if ($this->cachedStockReport === null) {
            $this->cachedStockReport = app(StockRequirementsReport::class)->execute($this->selected_date);
        }
        return $this->cachedStockReport;
    }

    /**
     * Расчет дефицита подошв на основе заказов (по размерам)
     */
    public function getSoleAnalysisProperty()
    {
        $report = $this->getStockReport();
        $solesNeeded = $report['soles_needed'] ?? [];

        // Предзагружаем все ShoeSoleItems одним запросом
        $soleIds = array_column($solesNeeded, 'sole_id');
        $allSoleItems = ShoeSoleItem::whereIn('shoe_sole_id', $soleIds)
            ->get()
            ->keyBy(fn($item) => $item->shoe_sole_id . '_' . $item->size_id);

        $result = collect();

        foreach ($solesNeeded as $sole) {
            $soleId = $sole['sole_id'];
            $soleName = $sole['sole_name'];
            $sizes = $sole['sizes'] ?? [];

            // Если нет размеров, выводим общее количество
            if (empty($sizes)) {
                $totalNeeded = $sole['total_needed'] ?? 0;
                $totalStock = ShoeSoleItem::where('shoe_sole_id', $soleId)->sum('stock_quantity');

                $result->push([
                    'sole_id' => $soleId,
                    'name' => $soleName,
                    'size' => 'Всего',
                    'needed' => $totalNeeded,
                    'stock' => $totalStock,
                    'diff' => $totalStock - $totalNeeded,
                ]);
            } else {
                // Выводим по каждому размеру
                foreach ($sizes as $sizeId => $needed) {
                    $key = $soleId . '_' . $sizeId;
                    $soleItem = $allSoleItems->get($key);
                    $stock = $soleItem?->stock_quantity ?? 0;

                    $result->push([
                        'sole_id' => $soleId,
                        'name' => $soleName,
                        'size' => $sizeId,
                        'needed' => $needed,
                        'stock' => $stock,
                        'diff' => $stock - $needed,
                    ]);
                }
            }
        }

        return $result->values()->toArray();
    }



    /**
     * Генерирует дерево материалов для модального окна
     */
    public function getMaterialTreeProperty(): array
    {
        return MaterialType::where('is_active', true)
            ->with(['materials.color'])
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                $nodeKey = "material_type_{$type->id}"; // Уникальный ключ
                return [
                    'id' => $type->id,
                    'nodeKey' => $nodeKey,
                    'name' => $type->name,
                    'expanded' => in_array($nodeKey, $this->expandedNodes),
                    'children' => $type->materials->sortBy('name')->map(fn($mat) => [
                        'id' => $mat->id,
                        'name' => $mat->fullName,
                        'stock' => $mat->stock_quantity,
                    ])->values()->toArray(),
                ];
            })->toArray();
    }

    /**
     * Генерирует дерево подошв
     */
    public function getSoleTreeProperty(): array
    {
        return ShoeSole::where('is_active', true)
            ->with(['color', 'shoeSoleItems.size'])
            ->orderBy('name')
            ->get()
            ->map(function ($sole) {
                $nodeKey = "sole_{$sole->id}"; // Уникальный ключ
                return [
                    'id' => $sole->id,
                    'nodeKey' => $nodeKey,
                    'name' => $sole->fullName,
                    'expanded' => in_array($nodeKey, $this->expandedNodes),
                    'children' => $sole->shoeSoleItems->sortBy(fn($item) => $item->size->name)->map(fn($item) => [
                        'id' => $item->id,
                        'name' => $sole->fullName . ' (' . $item->size->name . ')',
                        'stock' => $item->stock_quantity,
                    ])->values()->toArray(),
                ];
            })->toArray();
    }

    // 
    public function toggleNode(string $nodeKey): void
    {
        if (in_array($nodeKey, $this->expandedNodes)) {
            $this->expandedNodes = array_diff($this->expandedNodes, [$nodeKey]);
        } else {
            $this->expandedNodes[] = $nodeKey;
        }
    }

    public function addItemFromTree(int $entityId, string $name): void
    {
        $exists = collect($this->items)->some(
            fn($item) =>
            $item['entity_id'] === $entityId && $item['type'] === $this->entityType
        );

        if ($exists) {
            Notification::make()->title('Позиция уже в списке')->warning()->send();
            return;
        }

        $this->items[] = [
            'entity_id' => $entityId,
            'quantity' => 1,
            'type' => $this->entityType,
            'name' => $name,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function processMovements(): void
    {
        if (empty($this->items)) return;

        DB::transaction(function () {
            foreach ($this->items as $item) {
                if ($item['quantity'] <= 0) continue;
                $morphType = $item['type'] === 'materials' ? 'material' : 'sole';
                MaterialMovement::create([
                    'movable_type' => $morphType,
                    'movable_id' => $item['entity_id'],
                    'type' => $this->movementType,
                    'quantity' => $item['quantity'],
                    'description' => "Ручное проведение через АРМ",
                ]);
            }
        });

        $this->reset(['items', 'expandedNodes']);
        $this->dispatch('close-modal', id: 'movement-modal');
        Notification::make()->success()->title('Операции проведены')->send();
    }

    public function resetToZero(int $materialId, string $name): void
    {
        DB::transaction(function () use ($materialId, $name) {
            $material = Material::findOrFail($materialId);
            $currentStock = $material->stock_quantity;

            if ($currentStock < 0) {
                $material->movements()->create([
                    'type' => \App\Enums\MovementType::Income,
                    'quantity' => abs($currentStock),
                    'description' => "Обнуление отрицательного остатка: {$name}",
                ]);
            }
        });
        Notification::make()->success()->title('Остаток обнулен')->send();
    }

    public function fillToBalance(int $materialId, float $needed, string $name): void
    {
        DB::transaction(function () use ($materialId, $needed, $name) {
            $material = Material::findOrFail($materialId);
            $toAdd = $needed - $material->stock_quantity;

            if ($toAdd > 0) {
                $material->movements()->create([
                    'type' => \App\Enums\MovementType::Income,
                    'quantity' => $toAdd,
                    'description' => "Приход в баланс под план на {$this->selected_date}: {$name}",
                ]);
            }
        });
        Notification::make()->success()->title('Баланс пополнен')->send();
    }

    public function resetSoleToZero(int $soleId, string $name): void
    {
        DB::transaction(function () use ($soleId, $name) {
            $items = ShoeSoleItem::where('shoe_sole_id', $soleId)->get();

            foreach ($items as $item) {
                if ($item->stock_quantity < 0) {
                    $item->movements()->create([
                        'type' => \App\Enums\MovementType::Income,
                        'quantity' => abs($item->stock_quantity),
                        'description' => "Обнуление отрицательного остатка: {$name}",
                    ]);
                }
            }
        });
        Notification::make()->success()->title('Остаток обнулен')->send();
    }

    public function fillSoleToBalance(int $soleId, float $needed, string $name): void
    {
        DB::transaction(function () use ($soleId, $needed, $name) {
            $totalStock = ShoeSoleItem::where('shoe_sole_id', $soleId)->sum('stock_quantity');
            $toAdd = $needed - $totalStock;

            if ($toAdd > 0) {
                // Добавляем прирост к первому доступному размеру подошвы
                $firstItem = ShoeSoleItem::where('shoe_sole_id', $soleId)->first();
                if ($firstItem) {
                    $firstItem->movements()->create([
                        'type' => \App\Enums\MovementType::Income,
                        'quantity' => $toAdd,
                        'description' => "Приход в баланс под план на {$this->selected_date}: {$name}",
                    ]);
                }
            }
        });
        Notification::make()->success()->title('Баланс пополнен')->send();
    }

    /**
     * Массовое списание материалов и подошв на основе плана (выбранной даты)
     */
    public function applyBulkOrderWriteOff(): void
    {
        // 1. Находим заказы на выбранную дату, которые еще не выполнены
        $orders = Order::query()
            ->whereDate('started_at', $this->selected_date)
            ->where('status', '!=', OrderStatus::Completed->value)
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->get();

        if ($orders->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('Нет заказов для списания')
                ->body("На {$this->selected_date} все заказы уже выполнены или отсутствуют.")
                ->send();
            return;
        }

        $report = $this->getStockReport();
        $materialsNeeded = $report['materials_needed'] ?? [];
        $solesNeeded = $report['soles_needed'] ?? [];

        DB::transaction(function () use ($materialsNeeded, $solesNeeded, $orders) {
            // 2. Списываем материалы по отчету
            foreach ($materialsNeeded as $mat) {
                if (($mat['needed'] ?? 0) <= 0) continue;
                Material::find($mat['material_id'])?->movements()->create([
                    'type' => MovementType::Outcome,
                    'quantity' => $mat['needed'],
                    'description' => "Авто-списание по плану на {$this->selected_date}",
                ]);
            }

            // 3. Списываем подошвы по отчету
            foreach ($solesNeeded as $sole) {
                foreach ($sole['sizes'] ?? [] as $sizeId => $needed) {
                    if ($needed <= 0) continue;
                    ShoeSoleItem::where('shoe_sole_id', $sole['sole_id'])
                        ->where('size_id', $sizeId)
                        ->first()
                        ?->movements()->create([
                            'type' => MovementType::Outcome,
                            'quantity' => $needed,
                            'description' => "Авто-списание по плану на {$this->selected_date}",
                        ]);
                }
            }

            // 4. МЕНЯЕМ СТАТУС ЗАКАЗОВ НА ВЫПОЛНЕНО
            foreach ($orders as $order) {
                $order->update(['status' => OrderStatus::Completed->value]);
            }
        });

        // Очищаем кэш отчета, чтобы дефицит в таблицах исчез
        $this->cachedStockReport = null;

        Notification::make()
            ->success()
            ->title('Списание проведено')
            ->body("Материалы списаны, " . $orders->count() . " зак. переведены в статус 'Выполнено'")
            ->send();
    }
}
