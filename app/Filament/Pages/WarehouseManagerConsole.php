<?php

namespace App\Filament\Pages;

use App\Models\{Material, MaterialType, ShoeSole, ShoeSoleItem, MaterialMovement};
use App\Enums\MovementType;
use App\Filament\Pages\Reports\StockRequirementsReport;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Support\Collection;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use App\Services\WarehouseService;

class WarehouseManagerConsole extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;
    protected string $view = 'filament.pages.warehouse-manager-console';
    protected static ?string $title = 'АРМ - Складской учет';
    protected static ?int $navigationSort = 10;

    public ?string $selected_date = null;
    public string $movementType = MovementType::Income->value;
    public string $entityType = 'materials';

    public array $items = [];
    public array $expandedNodes = [];
    public bool $isMovementModalOpen = false;


    public function mount(): void
    {
        $this->selected_date = now()->format('Y-m-d');
    }

    /**
     * Расчет дефицита на основе заказов
     */
    public function getStockAnalysisProperty()
    {
        return app(WarehouseService::class)->getStockAnalysis($this->selected_date);
    }



    /**
     * Генерирует дерево материалов для модального окна
     */
    public function getMaterialTreeProperty(): array
    {
        return MaterialType::where('is_active', true)
            ->with(['materials.color'])
            ->get()
            ->map(function ($type) {
                $nodeKey = "material_type_{$type->id}"; // Уникальный ключ
                return [
                    'id' => $type->id,
                    'nodeKey' => $nodeKey,
                    'name' => $type->name,
                    'expanded' => in_array($nodeKey, $this->expandedNodes),
                    'children' => $type->materials->map(fn($mat) => [
                        'id' => $mat->id,
                        'name' => $mat->fullName,
                        'stock' => $mat->stock_quantity,
                    ])->toArray(),
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
            ->get()
            ->map(function ($sole) {
                $nodeKey = "sole_{$sole->id}"; // Уникальный ключ
                return [
                    'id' => $sole->id,
                    'nodeKey' => $nodeKey,
                    'name' => $sole->fullName,
                    'expanded' => in_array($nodeKey, $this->expandedNodes),
                    'children' => $sole->shoeSoleItems->map(fn($item) => [
                        'id' => $item->id,
                        'name' => $sole->fullName . ' (' . $item->size->name . ')',
                        'stock' => $item->stock_quantity,
                    ])->toArray(),
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

                MaterialMovement::create([
                    'movable_type' => $item['type'] === 'materials' ? Material::class : ShoeSoleItem::class,
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
}
