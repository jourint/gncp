<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use App\Filament\Pages\ModelBuilder\Actions\SelectModelAction;
use App\Filament\Pages\ModelBuilder\Actions\CreateModelAction;
use App\Models\ShoeModel;
use App\Models\Workflow;
use App\Models\Size;
use App\Models\Color;
use App\Models\ShoeSole;
use App\Models\Material;
use App\Models\ShoeTechCard;
use App\Models\TechCardMaterial;
use App\Models\ShoeType;
use Illuminate\Support\Collection;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ModelBuilder extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;
    protected string $view = 'filament.pages.model-builder';
    protected static ?string $title = 'АРМ - Конструктор моделей';
    protected static ?int $navigationSort = 7;

    // ui
    public $tcImage;
    public ?int $activeModelId = null;
    public array $state = [];
    public array $sizeNames = [];
    public array $workflowNames = [];

    public ?int $selectedTechCardId = null;
    public array $selectedTechCardData = [];
    public array $techCardState = [];

    // Строки поиска
    public string $colorSearch = '';
    public string $soleSearch = '';
    public string $mat1Search = '';
    public string $mat2Search = '';
    public string $compositionSearch = '';

    public function mount(): void
    {
        $this->sizeNames = Size::pluck('name', 'id')->toArray();
        $this->workflowNames = Workflow::where('is_active', true)->pluck('name', 'id')->toArray();
    }

    // --- ЛОГИКА МОДЕЛИ ---

    public function loadModel(int $id): void
    {
        $model = ShoeModel::find($id);
        if ($model) {
            $this->activeModelId = $model->id;
            $this->state = [
                'name' => $model->name,
                'shoe_type_id' => $model->shoe_type_id,
                'price_coeff_cutting' => $model->price_coeff_cutting ?? 1.0,
                'price_coeff_sewing' => $model->price_coeff_sewing ?? 1.0,
                'price_coeff_shoemaker' => $model->price_coeff_shoemaker ?? 1.0,
                'available_sizes' => collect($model->available_sizes ?? [])->map(fn($v) => (string)$v)->toArray(),
                'workflows' => collect($model->workflows ?? [])->map(fn($v) => (string)$v)->toArray(),
                'is_active' => (bool)$model->is_active,
            ];
            $this->selectedTechCardId = null;
            $this->selectedTechCardData = [];
        }
    }

    public function saveModel(): void
    {
        $model = ShoeModel::findOrFail($this->activeModelId);
        $model->update($this->state);
        Notification::make()->title('Данные модели обновлены')->success()->send();
    }

    public function toggleSize(int $id): void
    {
        $sizes = collect($this->state['available_sizes']);
        $this->state['available_sizes'] = $sizes->contains($id)
            ? $sizes->reject(fn($v) => $v == $id)->values()->toArray()
            : $sizes->push((string)$id)->values()->toArray();
    }

    public function toggleWorkflow(int $id): void
    {
        $flows = collect($this->state['workflows']);
        $this->state['workflows'] = $flows->contains($id)
            ? $flows->reject(fn($v) => $v == $id)->values()->toArray()
            : $flows->push((string)$id)->values()->toArray();
    }

    // --- ЛОГИКА ТЕХКАРТ (ГЕТТЕРЫ ПОИСКА) ---

    public function getTechCardsProperty(): Collection
    {
        return $this->activeModelId
            ? ShoeTechCard::where('shoe_model_id', $this->activeModelId)->get()
            : collect();
    }

    public function getFilteredColorsProperty(): Collection
    {
        $usedIds = ShoeTechCard::where('shoe_model_id', $this->activeModelId)->pluck('color_id');
        return Color::whereNotIn('id', $usedIds)
            ->when($this->colorSearch, fn($q) => $q->where('name', 'ilike', "%{$this->colorSearch}%"))
            ->limit(10)->get();
    }

    public function getFilteredSolesProperty(): Collection
    {
        return ShoeSole::where('is_active', true)
            ->when($this->soleSearch, fn($q) => $q->where('name', 'ilike', "%{$this->soleSearch}%"))
            ->limit(20)->get();
    }

    public function getFilteredMaterials1Property(): Collection
    {
        return $this->getMaterialSearch($this->mat1Search);
    }
    public function getFilteredMaterials2Property(): Collection
    {
        return $this->getMaterialSearch($this->mat2Search);
    }
    public function getFilteredCompositionMaterialsProperty(): Collection
    {
        return $this->getMaterialSearch($this->compositionSearch);
    }

    private function getMaterialSearch(string $query): Collection
    {
        if (strlen($query) < 2) return collect();
        return Material::where('is_active', true)
            ->where('name', 'ilike', "%{$query}%")
            ->limit(20)->get();
    }

    // --- ДЕЙСТВИЯ С ТЕХКАРТАМИ ---

    public function addTechCard(): void
    {
        $newTc = ShoeTechCard::create([
            'shoe_model_id' => $this->activeModelId,
            'color_id' => $this->techCardState['color_id'] ?? null,
            'shoe_sole_id' => $this->techCardState['shoe_sole_id'] ?? null,
            'material_id' => $this->techCardState['material_id'] ?? null,
            'material_two_id' => $this->techCardState['material_two_id'] ?? null,
            'is_active' => true,
        ]);

        $this->reset(['colorSearch', 'soleSearch', 'mat1Search', 'mat2Search', 'techCardState']);
        $this->selectTechCard($newTc->id);
        Notification::make()->title('Техкарта создана')->success()->send();
    }

    public function selectTechCard(int $id): void
    {
        $this->selectedTechCardId = $id;
        $tc = ShoeTechCard::with(['techCardMaterials.material', 'color', 'shoeSole', 'material', 'materialTwo'])->find($id);
        if ($tc) {
            $this->selectedTechCardData = $tc->toArray();
            // ЗАПОЛНЯЕМ ПОЛЯ ПОИСКА ТЕКУЩИМИ ЗНАЧЕНИЯМИ
            $this->colorSearch = $tc->color?->name ?? '';
            $this->soleSearch = $tc->shoeSole?->fullName ?? '';
            $this->mat1Search = $tc->material?->fullName ?? '';
            $this->mat2Search = $tc->materialTwo?->fullName ?? '';
            $this->compositionSearch = '';
        }
    }

    public function addMaterialToComposition($materialId): void
    {
        TechCardMaterial::create([
            'shoe_tech_card_id' => $this->selectedTechCardId,
            'material_id' => $materialId,
            'quantity' => 1.0,
        ]);
        $this->compositionSearch = '';
        $this->selectTechCard($this->selectedTechCardId);
    }

    public function saveTechCardComposition(): void
    {
        foreach ($this->selectedTechCardData['tech_card_materials'] as $item) {
            TechCardMaterial::where('id', $item['id'])->update(['quantity' => $item['quantity']]);
        }
        Notification::make()->title('Состав сохранен')->success()->send();
    }

    public function removeMaterial(int $id): void
    {
        TechCardMaterial::destroy($id);
        $this->selectTechCard($this->selectedTechCardId);
    }

    public function replicateCurrentTechCard(): void
    {
        if (!$this->selectedTechCardId) return;

        // 1. Загружаем оригинал со всеми связями, которые нужны для имени
        $record = ShoeTechCard::with(['shoeModel.shoeType', 'material', 'color'])->find($this->selectedTechCardId);

        // 2. Ищем следующий цвет
        $existingColorIds = ShoeTechCard::where('shoe_model_id', $record->shoe_model_id)
            ->pluck('color_id')->toArray();

        $nextColor = Color::find(
            Color::where('id', '>', $record->color_id)
                ->whereNotIn('id', $existingColorIds)
                ->orderBy('id', 'asc')
                ->value('id')
        );

        if (!$nextColor) {
            \Filament\Notifications\Notification::make()->title('Нет свободных цветов')->warning()->send();
            return;
        }

        try {
            $newCard = \Illuminate\Support\Facades\DB::transaction(function () use ($record, $nextColor) {
                $duplicatedCard = $record->replicate();

                // Устанавливаем ID
                $duplicatedCard->color_id = $nextColor->id;
                $duplicatedCard->shoe_model_id = $record->shoe_model_id;

                // ХИТРОСТЬ: Вручную устанавливаем связи в объект, чтобы fullName их увидел сразу
                $duplicatedCard->setRelation('shoeModel', $record->shoeModel);
                $duplicatedCard->setRelation('material', $record->material);
                $duplicatedCard->setRelation('color', $nextColor); // Подставляем новый цвет

                // Теперь save() вызовет booted -> saving -> fullName
                $duplicatedCard->save();

                // Копируем материалы состава
                foreach ($record->techCardMaterials as $material) {
                    $newMat = $material->replicate();
                    $newMat->shoe_tech_card_id = $duplicatedCard->id;
                    $newMat->save();
                }

                return $duplicatedCard;
            });

            // Обновляем UI
            $this->selectTechCard($newCard->id);

            \Filament\Notifications\Notification::make()
                ->title('Создана ТК: ' . $newCard->name)
                ->success()->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()->title('Ошибка')->body($e->getMessage())->danger()->send();
        }
    }

    /**
     * Универсальный метод для обновления одиночных полей техкарты
     */
    public function updateTechCardField(string $field, $value, string $searchVar = '', string $searchLabel = ''): void
    {
        if (!$this->selectedTechCardId) {
            return;
        }
        // Находим модель техкарты
        $techCard = ShoeTechCard::find($this->selectedTechCardId);

        if (!$techCard) {
            return;
        }
        try {
            $this->selectedTechCardData[$field] = $value;
            // Обновляем конкретное поле
            $techCard->update([
                $field => $value,
            ]);
            // Если переданы переменные для обновления строки поиска (для UI)
            if ($searchVar) {
                $this->{$searchVar} = $searchLabel;
            }
            // Если мы обновили статус активности, синхронизируем локальный массив данных
            if ($field === 'is_active') {
                $this->selectedTechCardData['is_active'] = (bool) $value;
            }
            // Перечитываем данные техкарты, чтобы обновить fullName и связи
            $this->selectTechCard($this->selectedTechCardId);
            $this->saveTechCardComposition();
            Notification::make()->title('Сохранено')->success()->send();
        } catch (\Illuminate\Database\QueryException $e) {
            // Обработка ошибки уникальности (Model + Color + Main Material)
            if ($e->getCode() == 23000) {
                Notification::make()->title('Ошибка уникальности')->body('Техкарта с таким цветом и основным материалом уже существует для этой модели.')->danger()->persistent()->send();

                // Откатываем выбор в поиске (визуально)
                $this->selectTechCard($this->selectedTechCardId);
            } else {
                throw $e;
            }
        }
    }

    // Наблюдатель за загрузкой фото
    public function updatedTcImage()
    {
        if (!$this->selectedTechCardId) return;

        $this->validate([
            'tcImage' => 'image|max:2048', // макс 2МБ
        ]);

        $path = $this->tcImage->store('shoe-tech-cards', 'public');

        $tc = ShoeTechCard::find($this->selectedTechCardId);
        $tc->update(['image_path' => $path]);

        $this->selectedTechCardData['image_path'] = $path;

        Notification::make()->title('Фото обновлено')->success()->send();
    }

    public function deleteTcImage(): void
    {
        if (!$this->selectedTechCardId) return;

        $tc = \App\Models\ShoeTechCard::find($this->selectedTechCardId);

        if ($tc && $tc->image_path) {
            // Удаляем физический файл с диска
            Storage::disk('public')->delete($tc->image_path);

            // Обновляем БД
            $tc->update(['image_path' => null]);

            // Синхронизируем локальное состояние
            $this->selectedTechCardData['image_path'] = null;

            \Filament\Notifications\Notification::make()
                ->title('Изображение удалено')
                ->success()
                ->send();
        }
    }

    // Добавь также метод удаления (кнопка Trash в шаблоне выше)
    public function deleteTechCard($id)
    {
        ShoeTechCard::destroy($id);
        $this->selectedTechCardId = null;
        $this->selectedTechCardData = [];

        Notification::make()
            ->title('Техкарта удалена')
            ->success()
            ->send();
    }

    // ----- ------

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')->label('Сохранить модель')->icon('heroicon-m-check-badge')->color('success')
                ->action(fn() => $this->saveModel())->visible(fn() => $this->activeModelId !== null),
            SelectModelAction::make(),
            CreateModelAction::make(),
        ];
    }
}
