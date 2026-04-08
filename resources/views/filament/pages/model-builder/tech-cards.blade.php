<div class="mt-12 space-y-4" wire:key="tech-cards-area">
    
    {{-- 1. ПАНЕЛЬ БЫСТРОГО СОЗДАНИЯ --}}
    <x-filament::section collapsible collapsed class="border-t-4 border-t-success-500">
        <x-slot name="heading">Создать новую техкарту</x-slot>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            
            {{-- Цвет (Создание) --}}
            <div class="relative" x-data="{ open: false }">
                <x-filament-forms::field-wrapper label="Цвет">
                    <x-filament::input.wrapper>
                        <x-filament::input placeholder="Поиск..." wire:model.live.debounce.300ms="newTcColorSearch" x-on:focus="open = true" x-on:click.away="open = false" />
                    </x-filament::input.wrapper>
                </x-filament-forms::field-wrapper>
                <div x-show="open" class="absolute z-[210] w-full mt-1 bg-white dark:bg-gray-800 border rounded-lg shadow-xl max-h-40 overflow-y-auto">
                    @foreach($this->filteredColors as $item)
                        <button type="button" wire:click="$set('techCardState.color_id', {{ $item->id }}); $set('newTcColorSearch', '{{ $item->name }}'); open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-600 hover:text-white border-b last:border-0 dark:border-gray-700 transition-colors"> {{ $item->name }} </button>
                    @endforeach
                </div>
            </div>

            {{-- Подошва (Создание) --}}
            <div class="relative" x-data="{ open: false }">
                <x-filament-forms::field-wrapper label="Подошва">
                    <x-filament::input.wrapper>
                        <x-filament::input placeholder="Поиск..." wire:model.live.debounce.300ms="newTcSoleSearch" x-on:focus="open = true" x-on:click.away="open = false" />
                    </x-filament::input.wrapper>
                </x-filament-forms::field-wrapper>
                <div x-show="open" class="absolute z-[210] w-full mt-1 bg-white dark:bg-gray-800 border rounded-lg shadow-xl max-h-40 overflow-y-auto">
                    @foreach($this->filteredSoles as $item)
                        <button type="button" wire:click="$set('techCardState.shoe_sole_id', {{ $item->id }}); $set('newTcSoleSearch', '{{ $item->fullName }}'); open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-600 hover:text-white border-b last:border-0 dark:border-gray-700 transition-colors"> {{ $item->fullName }} </button>
                    @endforeach
                </div>
            </div>

            {{-- Материалы (Создание) --}}
            <div class="md:col-span-2 grid grid-cols-2 gap-2">
                <div class="relative" x-data="{ open: false }">
                    <x-filament-forms::field-wrapper label="Материал 1">
                        <x-filament::input.wrapper>
                            <x-filament::input placeholder="Мат 1..." wire:model.live.debounce.300ms="newTcMat1Search" x-on:focus="open = true" x-on:click.away="open = false" />
                        </x-filament::input.wrapper>
                    </x-filament-forms::field-wrapper>
                    <div x-show="open" class="absolute z-[210] w-full mt-1 bg-white dark:bg-gray-800 border rounded-lg shadow-xl max-h-40 overflow-y-auto">
                        @foreach($this->filteredMaterials as $item)
                            <button type="button" wire:click="$set('techCardState.material_id', {{ $item->id }}); $set('newTcMat1Search', '{{ $item->fullName }}'); open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-600 hover:text-white border-b last:border-0 dark:border-gray-700 transition-colors"> {{ $item->fullName }} </button>
                        @endforeach
                    </div>
                </div>
                <div class="relative" x-data="{ open: false }">
                    <x-filament-forms::field-wrapper label="Материал 2">
                        <x-filament::input.wrapper>
                            <x-filament::input placeholder="Мат 2..." wire:model.live.debounce.300ms="newTcMat2Search" x-on:focus="open = true" x-on:click.away="open = false" />
                        </x-filament::input.wrapper>
                    </x-filament-forms::field-wrapper>
                    <div x-show="open" class="absolute z-[210] w-full mt-1 bg-white dark:bg-gray-800 border rounded-lg shadow-xl max-h-40 overflow-y-auto">
                        @foreach($this->filteredMaterials as $item)
                            <button type="button" wire:click="$set('techCardState.material_two_id', {{ $item->id }}); $set('newTcMat2Search', '{{ $item->fullName }}'); open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-600 hover:text-white border-b last:border-0 dark:border-gray-700 transition-colors"> {{ $item->fullName }} </button>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <x-filament::button wire:click="addTechCard" color="success" icon="heroicon-m-plus" class="mb-[2px]">Создать</x-filament::button>
        </div>
    </x-filament::section>

    {{-- 2. ОСНОВНОЙ ИНТЕРФЕЙС --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 h-[750px]">
        
        {{-- СПИСОК ТК (ЛЕВО) --}}
        <div class="lg:col-span-4 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl overflow-hidden flex flex-col shadow-sm border-t-4 border-primary-500">
            <div class="p-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b dark:border-gray-800 bg-gray-50/30">Техкарты модели</div>
            <div class="flex-1 overflow-y-auto p-2 custom-scrollbar space-y-1">
                @foreach($this->techCards as $tc)
                    <button wire:click="selectTechCard({{ $tc->id }})" wire:key="tc-card-{{ $tc->id }}"
                        class="w-full text-left p-3 rounded-xl border transition-all {{ $selectedTechCardId === $tc->id ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/20 shadow-sm' : 'border-transparent hover:bg-gray-50 dark:hover:bg-white/5' }}">
                        <div class="font-bold text-sm leading-tight {{ $selectedTechCardId === $tc->id ? 'text-primary-600' : 'text-gray-700 dark:text-gray-200' }}">
                            {{ $tc->name }}
                        </div>
                        <div class="text-[10px] text-gray-400 mt-1 italic uppercase truncate">{{ $tc->shoeSole?->fullName ?? 'Без подошвы' }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- РЕДАКТОР (ПРАВО) --}}
        <div class="lg:col-span-8 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl flex flex-col overflow-hidden shadow-sm" x-data="{ imgPreview: false }">
            @if($selectedTechCardId)
                
                <div class="p-5 border-b dark:border-gray-800 bg-gray-50/50">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                        <div class="md:col-span-9 space-y-5">
                            <div class="flex items-center gap-3">
                                <h2 class="text-base font-bold text-gray-800 dark:text-white leading-tight">
                                    {{ $selectedTechCardData['name'] ?? '' }}
                                </h2>
                                <x-filament::badge size="sm" :color="$selectedTechCardData['is_active'] ? 'success' : 'gray'">
                                    {{ $selectedTechCardData['is_active'] ? 'Активна' : 'Черновик' }}
                                </x-filament::badge>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <x-filament::button size="sm" color="info" icon="heroicon-m-arrow-right-circle" wire:click="replicateCurrentTechCard">В след. цвет</x-filament::button>
                                <x-filament::button size="sm" color="success" icon="heroicon-m-check-badge" wire:click="saveTechCardComposition">Сохранить</x-filament::button>
                                <x-filament::button size="sm" color="danger" icon="heroicon-m-trash" outline wire:click="deleteTechCard({{ $selectedTechCardId }})" wire:confirm="Удалить техкарту?">Удалить ТК</x-filament::button>

                                <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 cursor-pointer hover:bg-gray-100 transition-colors">
                                    <input type="checkbox" wire:model.live="selectedTechCardData.is_active" wire:change="updateTechCardField('is_active', $event.target.checked)" class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="text-xs font-bold uppercase text-gray-600 dark:text-gray-400">Активность</span>
                                </label>
                            </div>
                        </div>

                        {{-- ФОТО С ПРОСМОТРОМ --}}
                        <div class="md:col-span-3">
                            <div class="relative group h-28 bg-gray-200 dark:bg-gray-800 rounded-xl overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-700 flex flex-col items-center justify-center transition-all">
                                @if(!empty($selectedTechCardData['image_path']))
                                    @php $fullImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($selectedTechCardData['image_path']); @endphp
                                    
                                    <img src="{{ $fullImageUrl }}?v={{ time() }}" 
                                        @click="imgPreview = true"
                                        class="w-full h-full object-cover cursor-zoom-in hover:opacity-90 transition-opacity">
                                    
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 px-2 pointer-events-none">
                                        <div class="flex gap-2 pointer-events-auto">
                                            <button onclick="document.getElementById('tc-image-upload').click()" class="px-2 py-1 text-[9px] font-bold text-white bg-primary-500 rounded uppercase hover:bg-primary-400 shadow-sm transition-colors">Сменить</button>
                                            <button wire:click="deleteTcImage" class="px-2 py-1 text-[9px] font-bold text-white bg-danger-600 rounded uppercase hover:bg-danger-500 shadow-sm transition-colors">Удалить</button>
                                        </div>
                                    </div>

                                    {{-- Lightbox --}}
                                    <template x-teleport="body">
                                        <div x-show="imgPreview" 
                                             x-transition.opacity
                                             class="fixed inset-0 z-[999] flex items-center justify-center bg-black/90 p-4"
                                             @keydown.escape.window="imgPreview = false">
                                            <button @click="imgPreview = false" class="absolute top-5 right-5 text-white/70 hover:text-white transition-colors">
                                                <x-filament::icon icon="heroicon-o-x-mark" class="w-10 h-10" />
                                            </button>
                                            <img src="{{ $fullImageUrl }}" class="max-w-full max-h-full rounded-lg shadow-2xl" @click.away="imgPreview = false">
                                        </div>
                                    </template>
                                @else
                                    <x-filament::icon icon="heroicon-o-camera" class="w-8 h-8 text-gray-400 mb-2" />
                                    <button onclick="document.getElementById('tc-image-upload').click()" class="text-[9px] font-bold uppercase text-gray-500 hover:text-primary-500">Загрузить фото</button>
                                @endif
                                <input type="file" id="tc-image-upload" class="hidden" wire:model.live="tcImage" accept="image/*">
                            </div>
                        </div>
                    </div>

                    {{-- СЕЛЕКТЫ РЕДАКТИРОВАНИЯ --}}
                    <div class="mt-7 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 px-2">
                        <div x-data="{ open: @entangle('showColorDropdown') }" class="relative z-[150]">
                            <label class="text-[10px] font-bold text-gray-400 uppercase ml-0.5">Цвет изделия</label>
                            <x-filament::input.wrapper size="sm">
                                <x-filament::input type="text" wire:model.live.debounce.250ms="colorSearch" x-on:focus="open = true" x-on:click.away="open = false" />
                            </x-filament::input.wrapper>
                            <div x-show="open" x-transition class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-2xl max-h-48 overflow-y-auto z-[160]">
                                @foreach($this->filteredColors as $item)
                                    <button type="button" wire:click="updateTechCardField('color_id', {{ $item->id }}, 'colorSearch', '{{ $item->name }}'); open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-600 hover:text-white border-b last:border-0 dark:border-gray-700 transition-colors"> {{ $item->name }} </button>
                                @endforeach
                            </div>
                        </div>

                        <div x-data="{ open: @entangle('showSoleDropdown') }" class="relative z-[140]">
                            <label class="text-[10px] font-bold text-gray-400 uppercase ml-0.5">Тип подошвы</label>
                            <x-filament::input.wrapper size="sm">
                                <x-filament::input type="text" wire:model.live.debounce.250ms="soleSearch" x-on:focus="open = true" x-on:click.away="open = false" />
                            </x-filament::input.wrapper>
                            <div x-show="open" x-transition class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-2xl max-h-48 overflow-y-auto z-[150]">
                                @foreach($this->filteredSoles as $item)
                                    <button type="button" wire:click="updateTechCardField('shoe_sole_id', {{ $item->id }}, 'soleSearch', '{{ $item->name }}'); open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-600 hover:text-white border-b last:border-0 dark:border-gray-700 transition-colors"> {{ $item->fullName }} </button>
                                @endforeach
                            </div>
                        </div>

                        <div x-data="{ open: @entangle('showMat1Dropdown') }" class="relative z-[130]">
                            <label class="text-[10px] font-bold text-gray-400 uppercase ml-0.5">Материал 1 (Осн)</label>
                            <x-filament::input.wrapper size="sm">
                                <x-filament::input type="text" wire:model.live.debounce.250ms="mat1Search" x-on:focus="open = true" x-on:click.away="open = false" />
                            </x-filament::input.wrapper>
                            <div x-show="open" x-transition class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-2xl max-h-48 overflow-y-auto z-[140]">
                                @foreach($this->filteredMaterials as $item)
                                    <button type="button" wire:click="updateTechCardField('material_id', {{ $item->id }}, 'mat1Search', '{{ $item->fullName }}'); open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-600 hover:text-white border-b last:border-0 dark:border-gray-700 transition-colors"> {{ $item->fullName }} </button>
                                @endforeach
                            </div>
                        </div>

                        <div x-data="{ open: @entangle('showMat2Dropdown') }" class="relative z-[120]">
                            <label class="text-[10px] font-bold text-gray-400 uppercase ml-0.5">Материал 2 (Доп)</label>
                            <x-filament::input.wrapper size="sm">
                                <x-filament::input type="text" wire:model.live.debounce.250ms="mat2Search" x-on:focus="open = true" x-on:click.away="open = false" />
                            </x-filament::input.wrapper>
                            <div x-show="open" x-transition class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-2xl max-h-48 overflow-y-auto z-[130]">
                                @foreach($this->filteredMaterials as $item)
                                    <button type="button" wire:click="updateTechCardField('material_two_id', {{ $item->id }}, 'mat2Search', '{{ $item->fullName }}'); open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-600 hover:text-white border-b last:border-0 dark:border-gray-700 transition-colors"> {{ $item->fullName }} </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Поиск для ТАБЛИЦЫ состава --}}
                    <div class="mt-6 pt-5 border-t dark:border-gray-800 relative" x-data="{ open: false }">
                        <label class="text-[10px] font-bold text-primary-500 uppercase ml-1 tracking-wider">Добавить в состав</label>
                        <x-filament::input.wrapper size="sm" :inner-prefix-icon="'heroicon-m-magnifying-glass'">
                            <x-filament::input type="text" placeholder="Поиск материала..." wire:model.live.debounce.300ms="compositionSearch" x-on:focus="open = true" x-on:click.away="open = false" />
                        </x-filament::input.wrapper>
                        <div x-show="open && $wire.compositionSearch.length >= 2" class="absolute z-[200] w-full mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                            @forelse($this->filteredMaterials as $mat)
                                <button type="button" wire:click="addMaterialToComposition({{ $mat->id }}); open = false" class="w-full text-left px-4 py-2.5 border-b last:border-0 dark:border-gray-700 hover:bg-primary-600 hover:text-white transition-colors text-sm font-medium"> {{ $mat->fullName }} </button>
                            @empty
                                <div class="p-4 text-xs text-center text-gray-500 italic">Ничего не найдено</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ТАБЛИЦА РАСХОДА --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-gray-100 dark:bg-gray-800 text-[10px] uppercase text-gray-500 font-bold z-10 shadow-sm">
                            <tr>
                                <th class="px-5 py-2.5">Материал</th>
                                <th class="px-5 py-2.5 w-32 text-center">Расход</th>
                                <th class="px-5 py-2.5 w-10 text-right pr-5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-800">
                            @forelse($selectedTechCardData['tech_card_materials'] ?? [] as $index => $item)
                                <tr class="hover:bg-primary-50/10 dark:hover:bg-white/5 transition-colors" wire:key="row-{{ $item['id'] }}">
                                    <td class="px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ $item['material']['fullName'] ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <x-filament::input.wrapper size="sm">
                                            <input type="number" step="0.001" wire:model.live.debounce.500ms="selectedTechCardData.tech_card_materials.{{ $index }}.quantity" class="w-full text-center bg-transparent border-none p-0 text-sm focus:ring-0 font-bold dark:text-white">
                                        </x-filament::input.wrapper>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <button wire:click="removeMaterial({{ $item['id'] }})" class="text-gray-400 hover:text-danger-600 transition-all active:scale-90">
                                            <x-filament::icon icon="heroicon-m-trash" class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-10 text-center text-gray-400 text-xs italic">Состав пуст.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="h-full flex flex-col items-center justify-center text-gray-300 p-8">
                    <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="w-16 h-16 mb-4 opacity-10" />
                    <p class="text-sm italic">Выберите техкарту слева</p>
                </div>
            @endif
        </div>
    </div>
</div>