<?php

namespace App\Filament\Pages;

use App\Enums\MovementType;
use App\Models\Material;
use App\Models\MaterialType;
use App\Models\ShoeSole;
use App\Models\ShoeSoleItem;
use App\Models\Size;
use App\Models\User;
use App\Models\MaterialMovement;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use BackedEnum;

class WarehouseManagerConsole extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;
    protected string $view = 'filament.pages.warehouse-manager-console';
    protected static ?string $title = 'АРМ - Складовщик';
    protected static ?int $navigationSort = 10;

    public string $movementType = MovementType::Income->value;
    public string $entityType = 'materials'; // 'materials' или 'soles'

    // Для дерева
    public array $materialTree = [];
    public array $soleTree = [];
    public array $expandedNodes = []; // ['node_id']

    // Для добавления позиций
    public array $items = []; // [ ['entity_id', 'quantity', 'type', 'name'], ... ]

    public function mount(): void
    {
        $this->buildTrees();
    }

    private function buildTrees(): void
    {
        // Материалы: тип → материалы (без цветов)
        $this->materialTree = MaterialType::where('is_active', true)
            ->with(['materials' => fn($q) => $q->where('is_active', true)->with('color')])
            ->get()
            ->map(fn($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'type' => 'material_type',
                'expanded' => in_array($type->id, $this->expandedNodes),
                'children' => $type->materials->map(fn($mat) => [
                    'id' => $mat->id,
                    'name' => $mat->fullName, // включает цвет, если есть
                    'type' => 'material',
                    'stock' => $mat->stock_quantity,
                ])->toArray(),
            ])->toArray();

        // Подошвы: подошва → размеры (название + цвет + размер)
        $this->soleTree = ShoeSole::where('is_active', true)
            ->with(['color', 'shoeSoleItems.size'])
            ->get()
            ->map(fn($sole) => [
                'id' => $sole->id,
                'name' => $sole->fullName, // уже включает цвет
                'type' => 'sole',
                'expanded' => in_array($sole->id, $this->expandedNodes),
                'children' => $sole->shoeSoleItems->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $sole->fullName . ' (' . $item->size->name . ')', // название + цвет + размер
                    'type' => 'sole_item',
                    'stock' => $item->stock_quantity,
                ])->toArray(),
            ])->toArray();
    }

    public function updatedEntityType(): void
    {
        $this->buildTrees();
    }

    public function toggleNode(int $nodeId): void
    {
        if (in_array($nodeId, $this->expandedNodes)) {
            $this->expandedNodes = array_diff($this->expandedNodes, [$nodeId]);
        } else {
            $this->expandedNodes[] = $nodeId;
        }

        $this->buildTrees(); // ✅ Обновляем дерево
    }

    public function addItemFromTree(int $entityId, string $name): void
    {
        // Проверяем, нет ли уже такой позиции
        $exists = collect($this->items)->some(fn($item) => $item['entity_id'] === $entityId && $item['type'] === $this->entityType);

        if ($exists) {
            Notification::make()->title('Ошибка')->body('Позиция уже добавлена')->warning()->send();
            return;
        }

        $this->items[] = [
            'entity_id' => $entityId,
            'quantity' => 0, // по умолчанию 0
            'type' => $this->entityType,
            'name' => $name,
        ];
    }

    public function updateItemQuantity(int $index, float $quantity): void
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['quantity'] = $quantity;
        }
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function processMovements(): void
    {
        if (empty($this->items)) {
            Notification::make()->title('Ошибка')->body('Нет позиций для проведения')->danger()->send();
            return;
        }

        // Фильтруем позиции с 0
        $validItems = collect($this->items)->filter(fn($item) => $item['quantity'] > 0);

        if ($validItems->isEmpty()) {
            Notification::make()->title('Ошибка')->body('Нет позиций с количеством > 0')->danger()->send();
            return;
        }

        try {
            DB::transaction(function () use ($validItems) {
                foreach ($validItems as $item) {
                    $movement = MaterialMovement::create([
                        'movable_type' => $item['type'] === 'materials' ? Material::class : ShoeSoleItem::class,
                        'movable_id' => $item['entity_id'],
                        'type' => $this->movementType,
                        'quantity' => $item['quantity'],
                        'user_id' => Auth::id(),
                        'description' => "Движение: {$this->movementType}",
                    ]);

                    if ($item['type'] === 'materials') {
                        $material = Material::findOrFail($item['entity_id']);
                        if (MovementType::from($this->movementType)->isNegative()) {
                            if ($material->stock_quantity < $item['quantity']) {
                                throw new \Exception("Недостаточно материала: {$material->fullName}");
                            }
                            $material->decrement('stock_quantity', $item['quantity']);
                        } else {
                            $material->increment('stock_quantity', $item['quantity']);
                        }
                    } elseif ($item['type'] === 'soles') {
                        $soleItem = ShoeSoleItem::findOrFail($item['entity_id']);
                        if (MovementType::from($this->movementType)->isNegative()) {
                            $soleItem->deductStock((int)$item['quantity']);
                        } else {
                            $soleItem->addStock((int)$item['quantity']);
                        }
                    }
                }
            });

            Notification::make()->title('Успешно')->body('Все движения проведены')->success()->send();
            $this->reset(['items']);
        } catch (\Exception $e) {
            Notification::make()->title('Ошибка')->body($e->getMessage())->danger()->send();
        }
    }
}
