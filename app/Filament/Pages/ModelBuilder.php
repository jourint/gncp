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
use App\Models\Material;
use App\Models\TechCardMaterial;
use App\Models\ShoeTechCard;
use App\Models\Color;
use App\Models\ShoeSole;
use Illuminate\Support\Collection;
use Livewire\WithFileUploads;

use App\Filament\Pages\ModelBuilder\Traits\HasModelLogic;
use App\Filament\Pages\ModelBuilder\Traits\HasSearchLogic;
use App\Filament\Pages\ModelBuilder\Traits\HasTechCardLogic;


class ModelBuilder extends Page
{
    use WithFileUploads;
    use HasModelLogic, HasSearchLogic, HasTechCardLogic;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;
    protected string $view = 'filament.pages.model-builder';
    protected static ?string $title = 'АРМ - Конструктор моделей';
    protected static ?int $navigationSort = 7;

    // UI state
    public $tcImage;
    public ?int $activeModelId = null;
    public array $state = [];
    public array $sizeNames = [];
    public array $workflowNames = [];

    public ?int $selectedTechCardId = null;
    public array $selectedTechCardData = [];
    public array $techCardState = [];

    // Строки поиска (должны быть тут для работы wire:model)
    public string $colorSearch = '';
    public string $soleSearch = '';
    public string $mat1Search = '';
    public string $mat2Search = '';
    public string $compositionSearch = '';

    public string $newTcColorSearch = '';
    public string $newTcSoleSearch = '';
    public string $newTcMat1Search = '';
    public string $newTcMat2Search = '';

    public function mount(): void
    {
        $this->sizeNames = Size::pluck('name', 'id')->toArray();
        $this->workflowNames = Workflow::where('is_active', true)->pluck('name', 'id')->toArray();
    }

    // --- БАЗОВАЯ ЛОГИКА МОДЕЛИ ---

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
                'shoe_insole_id' => $model->shoe_insole_id,
                'counter_id' => $model->counter_id,
                'puff_id' => $model->puff_id,
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

    public function prefetchSelects(): void
    {
        // Пустой метод, просто чтобы спровоцировать обновление геттеров
        // filteredColors и filteredSoles вызовутся автоматически
    }

    // 
    public function getFilteredMaterialsProperty(): Collection
    {
        // Определяем, по какому полю идет поиск в данный момент
        $query = match (true) {
            $this->showMat1Dropdown => $this->mat1Search,
            $this->showMat2Dropdown => $this->mat2Search,
            $this->showCompositionDropdown => $this->compositionSearch,
            // Если ни одно из окон редактирования не открыто, значит мы в панели создания
            default => ($this->newTcMat1Search ?: $this->newTcMat2Search),
        };

        return Material::where('is_active', true)
            ->when($query, fn($q) => $q->where('name', 'ilike', "%{$query}%"))
            ->limit(20)
            ->get();
    }

    public function getTechCardsProperty(): Collection
    {
        return $this->activeModelId ? ShoeTechCard::where('shoe_model_id', $this->activeModelId)->orderBy('id', 'asc')->get() : collect();
    }

    public function getFilteredColorsProperty(): Collection
    {
        $query = $this->showColorDropdown ? $this->colorSearch : $this->newTcColorSearch;
        $usedIds = ShoeTechCard::where('shoe_model_id', $this->activeModelId)->pluck('color_id');
        return Color::whereNotIn('id', $usedIds)
            ->when($query, fn($q) => $q->where('name', 'ilike', "%{$query}%"))
            ->limit(10)->get();
    }

    public function getFilteredSolesProperty(): Collection
    {
        $query = $this->showSoleDropdown ? $this->soleSearch : $this->newTcSoleSearch;
        return ShoeSole::where('is_active', true)
            ->when($query, fn($q) => $q->where('name', 'ilike', "%{$query}%"))
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
        return Material::where('is_active', true)->where('name', 'ilike', "%{$query}%")->limit(20)->get();
    }

    // --- СОСТАВ ---

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
        if (!isset($this->selectedTechCardData['tech_card_materials'])) return;
        foreach ($this->selectedTechCardData['tech_card_materials'] as $item) {
            TechCardMaterial::where('id', $item['id'])->update(['quantity' => $item['quantity']]);
        }
    }

    public function removeMaterial(int $id): void
    {
        TechCardMaterial::destroy($id);
        $this->selectTechCard($this->selectedTechCardId);
    }

    // Наблюдатель за фото
    public function updatedTcImage()
    {
        if (!$this->selectedTechCardId) return;
        $this->validate(['tcImage' => 'image|max:2048']);
        $path = $this->tcImage->store('tech-cards', 'public');
        ShoeTechCard::where('id', $this->selectedTechCardId)->update(['image_path' => $path]);
        $this->selectedTechCardData['image_path'] = $path;
        Notification::make()->title('Фото обновлено')->success()->send();
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
        $wf = collect($this->state['workflows']);

        $this->state['workflows'] = $wf->contains($id)
            ? $wf->reject(fn($v) => $v == $id)->values()->all()
            : $wf->push((string)$id)->all();
    }

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
