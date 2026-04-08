<?php

namespace App\Filament\Pages\ModelBuilder\Traits;

use App\Models\Color;
use App\Models\ShoeTechCard;
use App\Models\TechCardMaterial;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

trait HasTechCardLogic
{
    public bool $showColorDropdown = false;
    public bool $showSoleDropdown = false;
    public bool $showMat1Dropdown = false;
    public bool $showMat2Dropdown = false;
    public bool $showCompositionDropdown = false;

    /**
     * Создание новой техкарты через верхнюю панель
     */
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

        // Очищаем именно переменные поиска для ПАНЕЛИ СОЗДАНИЯ
        $this->reset([
            'newTcColorSearch',
            'newTcSoleSearch',
            'newTcMat1Search',
            'newTcMat2Search',
            'techCardState'
        ]);

        $this->selectTechCard($newTc->id);
        Notification::make()->title('Техкарта создана')->success()->send();
    }

    /**
     * Выбор техкарты из списка слева для редактирования
     */
    public function selectTechCard(int $id): void
    {
        $tc = ShoeTechCard::with(['color', 'shoeSole', 'material', 'materialTwo', 'techCardMaterials.material'])->find($id);

        if ($tc) {
            $this->selectedTechCardId = $tc->id;
            $this->selectedTechCardData = $tc->toArray();

            // Заполняем инпуты РЕДАКТИРОВАНИЯ текущими значениями из базы
            // Используем fullName для материалов и подошвы, если они есть
            $this->colorSearch = $tc->color?->name ?? '';
            $this->soleSearch = $tc->shoeSole?->name ?? '';
            $this->mat1Search = $tc->material?->fullName ?? $tc->material?->name ?? '';
            $this->mat2Search = $tc->materialTwo?->fullName ?? $tc->materialTwo?->name ?? '';

            $this->resetDropdowns();
        }
    }

    public function resetDropdowns(): void
    {
        $this->showColorDropdown = false;
        $this->showSoleDropdown = false;
        $this->showMat1Dropdown = false;
        $this->showMat2Dropdown = false;
        $this->showCompositionDropdown = false;
    }

    /**
     * Обновление поля существующей техкарты
     * Параметры $searchVar и $searchLabel теперь явно nullable для PHP 8.4+
     */
    public function updateTechCardField(string $field, $value, ?string $searchVar = '', ?string $searchLabel = ''): void
    {
        if (!$this->selectedTechCardId) return;

        $techCard = ShoeTechCard::find($this->selectedTechCardId);
        if (!$techCard) return;

        try {
            DB::transaction(function () use ($techCard, $field, $value, $searchVar, $searchLabel) {
                $techCard->update([$field => $value]);

                if ($searchVar) {
                    $this->{$searchVar} = $searchLabel;
                }

                if ($field === 'is_active') {
                    $this->selectedTechCardData['is_active'] = (bool) $value;
                }

                $techCard->refresh();
                $this->selectedTechCardData['name'] = $techCard->name;
                $this->selectedTechCardData[$field] = $value;
            });

            // Если обновили ключевой параметр, состав может потребовать пересчета или обновления отображения
            $this->saveTechCardComposition();

            // Закрываем списки (через переменную в PHP для entangle)
            $this->resetDropdowns();

            // Дополнительный сигнал для JS если нужно
            $this->dispatch('close-search-lists');

            Notification::make()->title('Сохранено')->success()->send();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                Notification::make()->title('Ошибка уникальности (такой цвет уже есть)')->danger()->send();
                $this->selectTechCard($this->selectedTechCardId);
            } else {
                throw $e;
            }
        }
    }

    public function replicateCurrentTechCard(): void
    {
        if (!$this->selectedTechCardId) return;

        $record = ShoeTechCard::with(['shoeModel.shoeType', 'material', 'color', 'techCardMaterials'])->find($this->selectedTechCardId);
        $existingColorIds = ShoeTechCard::where('shoe_model_id', $record->shoe_model_id)->pluck('color_id')->toArray();

        // Поиск следующего доступного цвета
        $nextColor = Color::where('id', '>', $record->color_id)
            ->whereNotIn('id', $existingColorIds)
            ->orderBy('id', 'asc')->first();

        if (!$nextColor) {
            Notification::make()->title('Нет свободных цветов для репликации')->warning()->send();
            return;
        }

        try {
            $newCard = DB::transaction(function () use ($record, $nextColor) {
                $duplicatedCard = $record->replicate();
                $duplicatedCard->color_id = $nextColor->id;
                // Сбрасываем фото при репликации в новый цвет (опционально)
                $duplicatedCard->image_path = null;
                $duplicatedCard->save();

                foreach ($record->techCardMaterials as $material) {
                    $newMat = $material->replicate();
                    $newMat->shoe_tech_card_id = $duplicatedCard->id;
                    $newMat->save();
                }
                return $duplicatedCard;
            });

            $this->selectTechCard($newCard->id);
            Notification::make()->title('Репликация успешна: ' . $newCard->name)->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Ошибка репликации')->body($e->getMessage())->danger()->send();
        }
    }

    public function deleteTcImage(): void
    {
        if (!$this->selectedTechCardId) return;
        $tc = ShoeTechCard::find($this->selectedTechCardId);

        if ($tc && $tc->image_path) {
            Storage::disk('public')->delete($tc->image_path);
            $tc->update(['image_path' => null]);
            $this->selectedTechCardData['image_path'] = null;
            Notification::make()->title('Изображение удалено')->success()->send();
        }
    }

    public function deleteTechCard($id): void
    {
        ShoeTechCard::destroy($id);

        if ($this->selectedTechCardId == $id) {
            $this->selectedTechCardId = null;
            $this->selectedTechCardData = [];
            $this->reset(['colorSearch', 'soleSearch', 'mat1Search', 'mat2Search']);
        }

        Notification::make()->title('Техкарта удалена')->success()->send();
    }
}
