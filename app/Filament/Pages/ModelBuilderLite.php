<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

use App\Models\{Color, ShoeSole, Material, Workflow, Size, ShoeModel, ShoeType, ShoeInsole, Counter, Puff, ShoeTechCard, TechCardMaterial};
use Illuminate\Support\Collection;
use Livewire\WithFileUploads;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Filament\Pages\ModelBuilder\Actions\SelectModelAction;
use App\Filament\Pages\ModelBuilder\Actions\CreateModelAction;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Enums\MaxWidth;


class ModelBuilderLite extends Page
{
    use WithFileUploads;

    protected string $view = 'filament.pages.model-builder-lite';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;
    protected static ?string $title = 'АРМ - Конс-тр моделей Lite';
    protected static ?int $navigationSort = 7;

    // Кнопки в шапке
    protected function getHeaderActions(): array
    {
        return [
            // Кнопка сохранения основной модели
            \Filament\Actions\Action::make('save')
                ->label('Сохранить модель')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->keyBindings(['command+s', 'ctrl+s'])
                ->action(fn() => $this->saveModel())
                ->visible(fn() => $this->activeModelId !== null),

            // НОВАЯ КНОПКА: Создание техкарты
            \Filament\Actions\Action::make('addTechCard')
                ->label('Новая техкарта')
                ->icon('heroicon-m-plus-circle')
                ->color('info')
                ->visible(fn() => $this->activeModelId !== null)
                ->modalWidth('7xl')
                ->modalHeading('Конфигурация новой техкарты')
                ->schema([
                    // Верхний блок: Основные параметры и Фото
                    Grid::make(2)
                        ->schema([
                            Section::make('Базовые параметры')
                                ->columnSpan(1)
                                ->schema([
                                    Select::make('color_id')
                                        ->label('Цвет изделия')
                                        ->options(Color::pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->preload(),

                                    Select::make('shoe_sole_id')
                                        ->label('Подошва')
                                        ->options(ShoeSole::all()->pluck('fullName', 'id'))
                                        ->searchable()
                                        ->preload(),
                                ]),

                            Section::make('Визуализация')
                                ->description('Загрузите фото образца')
                                ->columnSpan(1)
                                ->schema([
                                    FileUpload::make('image_path')
                                        ->label(false) // Скрываем лейбл для чистоты
                                        ->image()
                                        ->directory('tech-cards')
                                        ->imageEditor()
                                        ->imageEditorAspectRatios(['1:1', '4:3'])
                                        ->extraAttributes(['class' => 'mx-auto']),
                                ]),
                        ]),

                    // Нижний блок: Материалы
                    Section::make('Состав материалов')
                        ->compact()
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('material_id')
                                        ->label('Основной материал (верх)')
                                        ->options(Material::all()->pluck('fullName', 'id'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('material_two_id')
                                        ->label('Дополнительный материал')
                                        ->options(Material::all()->pluck('fullName', 'id'))
                                        ->searchable()
                                        ->preload(),
                                ]),
                        ]),
                ])
                ->action(function (array $data) {
                    $tc = ShoeTechCard::create([
                        'shoe_model_id' => $this->activeModelId,
                        'color_id' => $data['color_id'],
                        'shoe_sole_id' => $data['shoe_sole_id'] ?? null,
                        'material_id' => $data['material_id'] ?? null,
                        'material_two_id' => $data['material_two_id'] ?? null,
                        'image_path' => $data['image_path'] ?? null,
                        'name' => $this->state['name'] . ' (' . Color::find($data['color_id'])->name . ')',
                        'is_active' => true,
                    ]);

                    $this->selectTechCard($tc->id);

                    Notification::make()
                        ->title('Техкарта успешно создана')
                        ->success()
                        ->send();
                }),

            SelectModelAction::make(),
            CreateModelAction::make(),
        ];
    }

    // 
    public ?int $activeModelId = null;
    public array $state = [];
    public array $catalog = [];
    public array $sizeNames = [];
    public array $workflowNames = [];

    // Состояния для техкарт
    public ?int $selectedTechCardId = null;
    public array $selectedTechCardData = [];
    public array $techCardState = [];
    public $tcImage;

    public function mount(): void
    {
        // 1. Загружаем основной справочник цветов (для селектов и поиска)
        $this->catalog['colors'] = Color::orderBy('name')->pluck('name', 'id')->toArray();

        // 2. Подошвы (цвета подтянутся автоматически благодаря $with в модели)
        $this->catalog['soles'] = ShoeSole::where('is_active', true)
            ->get()
            ->mapWithKeys(fn($s) => [
                $s->id => [
                    'name' => $s->name,
                    'fullName' => $s->fullName, // Геттер внутри использует уже загруженный color
                    'color' => $s->color->name ?? null
                ]
            ])->toArray();

        // 3. Материалы (аналогично — color уже в памяти)
        $this->catalog['materials'] = Material::where('is_active', true)
            ->get()
            ->mapWithKeys(fn($m) => [
                $m->id => [
                    'name' => $m->name,
                    'fullName' => $m->fullName,
                    'color' => $m->color->name ?? null
                ]
            ])->toArray();

        // 4. Остальные справочники
        $this->catalog['types'] = ShoeType::pluck('name', 'id')->toArray();
        $this->catalog['insoles'] = ShoeInsole::get()->mapWithKeys(fn($i) => [$i->id => $i->fullName ?? $i->name])->toArray();
        $this->catalog['counters'] = Counter::pluck('name', 'id')->toArray();
        $this->catalog['puffs'] = Puff::pluck('name', 'id')->toArray();

        // 5. Статические данные для кнопок
        $this->sizeNames = Size::pluck('name', 'id')->toArray();
        $this->workflowNames = Workflow::where('is_active', true)->pluck('name', 'id')->toArray();
    }

    public function loadModel(int $id): void
    {
        $model = ShoeModel::with(['techCards.shoeSole', 'techCards.color', 'techCards.material', 'techCards.materialTwo'])->find($id);
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
        // Проверяем, что модель выбрана
        if (!$this->activeModelId) {
            return;
        }

        // Собираем данные из state
        $data = [
            'name'                  => $this->state['name'],
            'shoe_type_id'          => $this->state['shoe_type_id'] ?: null,
            'shoe_insole_id'        => $this->state['shoe_insole_id'] ?: null,
            'counter_id'            => $this->state['counter_id'] ?: null, // Задник
            'puff_id'               => $this->state['puff_id'] ?: null,    // Подносок
            'price_coeff_cutting'   => $this->state['price_coeff_cutting'] ?? 1.0,
            'price_coeff_sewing'    => $this->state['price_coeff_sewing'] ?? 1.0,
            'price_coeff_shoemaker' => $this->state['price_coeff_shoemaker'] ?? 1.0,
            'available_sizes'       => $this->state['available_sizes'] ?? [],
            'workflows'             => $this->state['workflows'] ?? [],
            'is_active'             => (bool)($this->state['is_active'] ?? true),
        ];
        // Обновляем модель
        ShoeModel::where('id', $this->activeModelId)->update($data);

        Notification::make()
            ->title('Сохранено')
            ->body('Все параметры обновлены.')
            ->success()
            ->send();
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
            ? $wf->reject(fn($v) => $v == $id)->values()->toArray()
            : $wf->push((string)$id)->values()->toArray();
    }

    public function getTechCardsProperty(): Collection
    {
        if (!$this->activeModelId) return collect();

        return ShoeTechCard::where('shoe_model_id', $this->activeModelId)
            ->with(['shoeSole.color', 'color', 'material.color', 'materialTwo.color']) // Грузим всё сразу!
            ->orderBy('id', 'asc')
            ->get();
    }

    public function selectTechCard(int $id): void
    {
        $tc = ShoeTechCard::with(['techCardMaterials.material'])->find($id);
        if ($tc) {
            $this->selectedTechCardId = $tc->id;
            $this->selectedTechCardData = $tc->toArray();
        }
    }


    // Удаление из состава (только из массива перед сохранением)
    public function removeMaterial($index): void
    {
        unset($this->selectedTechCardData['tech_card_materials'][$index]);
        // Пересбрасываем ключи массива для корректной работы Livewire
        $this->selectedTechCardData['tech_card_materials'] = array_values($this->selectedTechCardData['tech_card_materials']);
    }

    public function updatedTcImage()
    {
        if (!$this->selectedTechCardId) return;
        $this->validate(['tcImage' => 'image|max:2048']);
        $path = $this->tcImage->store('tech-cards', 'public');
        ShoeTechCard::where('id', $this->selectedTechCardId)->update(['image_path' => $path]);
        $this->selectedTechCardData['image_path'] = $path;
        Notification::make()->title('Фото обновлено')->success()->send();
    }

    // ... 

    // Добавление материала (теперь принимает количество по умолчанию)
    public function addMaterialToComposition($materialId, $defaultQuantity = 1.00): void
    {
        if (!$materialId) return;

        // Ищем материал в уже загруженном каталоге
        $materialData = $this->catalog['materials'][$materialId] ?? null;

        if (!$materialData) {
            // Если в каталоге нет, пробуем найти в БД (на всякий случай)
            $material = Material::find($materialId);
            if (!$material) return;
            $fullName = $material->fullName;
        } else {
            $fullName = $materialData['fullName'];
        }

        // Проверяем на дубликаты
        $exists = collect($this->selectedTechCardData['tech_card_materials'] ?? [])
            ->contains('material_id', $materialId);

        if ($exists) {
            Notification::make()->title('Материал уже в составе')->warning()->send();
            return;
        }

        // Добавляем в массив
        $this->selectedTechCardData['tech_card_materials'][] = [
            'material_id' => $materialId,
            'quantity' => $defaultQuantity,
            'material' => [
                'fullName' => $fullName
            ]
        ];
    }

    // ИСПРАВЛЕННЫЙ МЕТОД: Сохранение (удалена ошибка loadTechCards)
    public function saveTechCardComposition(): void
    {
        if (!$this->selectedTechCardId) return;

        try {
            DB::transaction(function () {
                $tc = ShoeTechCard::findOrFail($this->selectedTechCardId);
                $tc->update([
                    'color_id'        => $this->selectedTechCardData['color_id'],
                    'shoe_sole_id'    => $this->selectedTechCardData['shoe_sole_id'] ?: null,
                    'material_id'     => $this->selectedTechCardData['material_id'] ?: null,
                    'material_two_id' => $this->selectedTechCardData['material_two_id'] ?: null,
                    'is_active'       => $this->selectedTechCardData['is_active'],
                ]);

                $tc->techCardMaterials()->delete();
                foreach ($this->selectedTechCardData['tech_card_materials'] as $item) {
                    $tc->techCardMaterials()->create([
                        'material_id' => $item['material_id'],
                        'quantity'    => $item['quantity'] ?? 1.0,
                    ]);
                }

                $colorName = Color::find($tc->color_id)?->name ?? '???';
                $tc->update(['name' => $this->state['name'] . ' (' . $colorName . ')']);
            });

            Notification::make()->title('Сохранено')->success()->send();

            // НЕ вызываем loadModel, чтобы не сбрасывать фокус/скролл
            // Но обновляем список для левой панели
            $this->dispatch('refreshTechCards');
        } catch (\Exception $e) {
            Notification::make()->title('Ошибка')->danger()->send();
        }
    }

    public function replicateCurrentTechCard(): void
    {
        if (!$this->activeModelId || !$this->selectedTechCardId) return;

        try {
            DB::transaction(function () {
                $original = ShoeTechCard::with('techCardMaterials')->findOrFail($this->selectedTechCardId);

                // 1. Находим все существующие комбинации для этой модели, 
                // чтобы не наткнуться на уникальный ключ (color_id + material_id)
                $existingCombinations = ShoeTechCard::where('shoe_model_id', $this->activeModelId)
                    ->where('material_id', $original->material_id)
                    ->pluck('color_id')
                    ->toArray();

                // 2. Ищем первый цвет из каталога, который в связке с этим материалом еще не занят
                $nextColorId = null;
                foreach ($this->catalog['colors'] as $id => $name) {
                    if (!in_array($id, $existingCombinations)) {
                        $nextColorId = $id;
                        break;
                    }
                }

                if (!$nextColorId) {
                    Notification::make()
                        ->title('Нет доступных вариантов')
                        ->body('Для данного материала уже созданы техкарты во всех возможных цветах.')
                        ->warning()
                        ->send();
                    return;
                }

                // 3. Создаем реплику
                $replica = $original->replicate();
                $replica->color_id = $nextColorId;
                $replica->name = $this->state['name'] . ' (' . $this->catalog['colors'][$nextColorId] . ')';

                // Очищаем только фото (так как подошва и материалы в БД NOT NULL)
                $replica->image_path = null;
                $replica->is_active = false;

                $replica->save();

                // 4. Копируем состав (расходники)
                foreach ($original->techCardMaterials as $material) {
                    $newMat = $material->replicate();
                    $newMat->shoe_tech_card_id = $replica->id;
                    $newMat->save();
                }

                // Переключаемся на новую карту
                $this->selectTechCard($replica->id);
            });

            Notification::make()->title('Техкарта создана')->success()->send();
            $this->loadModel($this->activeModelId);
        } catch (\Exception $e) {
            Notification::make()->title('Ошибка базы данных')->danger()
                ->body('Не удалось создать уникальную комбинацию. Попробуйте сменить материал вручную.')
                ->send();
        }
    }

    // Удаление техкарты целиком
    public function deleteTechCard(int $id): void
    {
        ShoeTechCard::destroy($id);
        $this->selectedTechCardId = null;
        $this->selectedTechCardData = [];
        $this->loadModel($this->activeModelId);
        Notification::make()->title('Техкарта удалена')->success()->send();
    }
}
