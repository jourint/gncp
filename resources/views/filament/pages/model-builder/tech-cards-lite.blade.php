<div class="grid grid-cols-1 lg:grid-cols-12 gap-4 h-[800px] antialiased" wire:key="tc-main-container" x-data="{ imgFull: false, imgSrc: '' }">
    
    {{-- СПИСОК СЛЕВА --}}
    <div class="lg:col-span-5 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl overflow-hidden flex flex-col shadow-sm border-t-4 border-primary-500">
        <div class="p-3 flex justify-between items-center border-b dark:border-gray-800 bg-gray-50/30">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Техкарты модели</span>
            <x-filament::icon-button icon="heroicon-m-plus" size="sm" color="success" wire:click="mountAction('addTechCard')" tooltip="Создать новую" />
        </div>
        
        <div class="flex-1 overflow-y-auto p-2 space-y-1 bg-gray-50/20 custom-scrollbar">
            @forelse($this->techCards as $tc)
                <div @class([
                    'flex items-center gap-2 p-2 rounded-xl border transition-all group relative',
                    'border-primary-500 bg-primary-50 dark:bg-primary-950/20 shadow-sm' => $selectedTechCardId === $tc->id,
                    'border-transparent hover:bg-white dark:hover:bg-gray-800 hover:shadow-md' => $selectedTechCardId !== $tc->id,
                ]) wire:key="tc-item-{{ $tc->id }}">
                    
                    <button wire:click="selectTechCard({{ $tc->id }})" class="flex-1 text-left overflow-hidden py-1">
                        <div @class([
                            'font-black text-[11px] uppercase truncate',
                            'text-primary-600' => $selectedTechCardId === $tc->id,
                            'text-gray-700 dark:text-gray-200' => $selectedTechCardId !== $tc->id,
                        ])>
                            {{ $tc->name }}
                        </div>
                        <div class="text-[9px] text-gray-400 font-bold truncate uppercase mt-0.5">
                            {{ $tc->shoeSole?->fullName ?? 'БЕЗ ПОДОШВЫ' }}
                        </div>
                    </button>

                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity pr-1">
                        <x-filament::icon-button 
                            icon="heroicon-m-trash" 
                            color="danger" 
                            size="sm" 
                            wire:click="deleteTechCard({{ $tc->id }})" 
                            wire:confirm="Удалить техкарту?" 
                        />
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-[10px] text-gray-400 uppercase italic">Нет созданных карт</div>
            @endforelse
        </div>
    </div>

    {{-- РЕДАКТОР СПРАВА --}}
    <div class="lg:col-span-7 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl flex flex-col overflow-hidden shadow-sm">
        @if($selectedTechCardId)
            {{-- ВЕРХНЯЯ ЧАСТЬ: ИНФО --}}
            <div class="p-4 border-b dark:border-gray-800 bg-gray-50/50">
                <div class="flex gap-5">
                    {{-- Превью фото --}}
                    <div class="relative w-28 h-28 bg-gray-200 dark:bg-gray-800 rounded-xl overflow-hidden border dark:border-gray-700 shrink-0 group/img cursor-pointer shadow-inner"
                         @click="imgSrc = '{{ $selectedTechCardData['image_path'] ? \Storage::url($selectedTechCardData['image_path']) : '' }}'; imgFull = true">
                        @if(!empty($selectedTechCardData['image_path']) && \Storage::exists($selectedTechCardData['image_path']))
                            <img src="{{ \Storage::url($selectedTechCardData['image_path']) }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover/img:opacity-100 flex items-center justify-center transition-opacity">
                                <x-filament::icon icon="heroicon-m-magnifying-glass-plus" class="w-6 h-6 text-white" />
                            </div>
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 flex flex-col items-center justify-center relative overflow-hidden">
                                <div class="absolute -top-5 -right-5 w-16 h-16 bg-primary-500/5 rounded-full blur-xl"></div>
                                <div class="absolute -bottom-5 -left-5 w-16 h-16 bg-primary-500/5 rounded-full blur-xl"></div>
                                <x-filament::icon icon="heroicon-o-photo" class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-1" />
                                <span class="text-[8px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">НЕТ ФОТО</span>
                            </div>
                        @endif
                        <button onclick="event.stopPropagation(); document.getElementById('tc-img-lite-up').click()" 
                                class="absolute bottom-1 right-1 p-1.5 bg-white/90 rounded-lg shadow-sm text-primary-600 hover:bg-white">
                            <x-filament::icon icon="heroicon-m-pencil" class="w-3.5 h-3.5" />
                        </button>
                    </div>
                    <input type="file" id="tc-img-lite-up" class="hidden" wire:model.live="tcImage">

                    <div class="flex flex-col justify-between py-1">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <h2 class="text-[13px] font-black uppercase text-gray-800 dark:text-white tracking-tight">
                                    {{ $selectedTechCardData['name'] }}
                                </h2>
                                <x-filament::badge size="sm" :color="$selectedTechCardData['is_active'] ? 'success' : 'gray'">
                                    {{ $selectedTechCardData['is_active'] ? 'Техкарта в работе' : 'Техкарта в архиве' }}
                                </x-filament::badge>
                            </div>
                            {{-- Чекбокс активности --}}
                            <label class="flex items-center gap-2 cursor-pointer w-fit group">
                                <input type="checkbox" wire:model.live="selectedTechCardData.is_active" class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-0">
                                <span class="text-[10px] font-bold text-gray-500 group-hover:text-primary-500 uppercase">Использовать</span>
                            </label>
                        </div>

                        {{-- КНОПКИ В РЯД --}}
                        <div class="flex items-center gap-2 mt-4">
                            <x-filament::button 
                                size="sm" 
                                color="success" 
                                icon="heroicon-m-check-badge" 
                                wire:click="saveTechCardComposition"
                                class="shadow-sm">
                                Сохранить
                            </x-filament::button>

                            <x-filament::button 
                                size="sm" 
                                color="info" 
                                outline 
                                icon="heroicon-m-sparkles" 
                                wire:click="replicateCurrentTechCard">
                                В след. цвет
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                {{-- ПАРАМЕТРЫ --}}
                <div class="grid grid-cols-2 gap-x-6 gap-y-3 mt-5">
                    @foreach([['key' => 'color_id', 'label' => 'Цвет', 'src' => 'colors'], ['key' => 'shoe_sole_id', 'label' => 'Подошва', 'src' => 'soles'], ['key' => 'material_id', 'label' => 'Материал 1', 'src' => 'materials'], ['key' => 'material_two_id', 'label' => 'Материал 2', 'src' => 'materials']] as $f)
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $f['label'] }}</label>
                            <x-filament::input.wrapper size="sm">
                                <select wire:model.live="selectedTechCardData.{{ $f['key'] }}" class="w-full border-none bg-transparent px-3 py-1 text-xs focus:ring-0 dark:text-white font-bold">
                                    <option value="">Не выбрано</option>
                                    @foreach($catalog[$f['src']] as $id => $val)
                                        <option value="{{ $id }}">{{ is_array($val) ? $val['fullName'] : $val }}</option>
                                    @endforeach
                                </select>
                            </x-filament::input.wrapper>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ПОИСК МАТЕРИАЛОВ --}}
            <div class="p-3 bg-gray-100/50 dark:bg-gray-800/50 border-b dark:border-gray-800 relative" x-data="{ open: false, q: '' }">
                <x-filament::input.wrapper size="sm" prefix-icon="heroicon-m-magnifying-glass">
                    <x-filament::input type="text" placeholder="Добавить материал..." x-model="q" @focus="open = true" @click.away="open = false" />
                </x-filament::input.wrapper>
                <div x-show="open && q.length > 1" class="absolute z-[200] left-3 right-3 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl shadow-2xl max-h-48 overflow-y-auto border-t-4 border-primary-500">
                    <template x-for="(mat, id) in catalog.materials" :key="id">
                        <button x-show="mat.fullName.toLowerCase().includes(q.toLowerCase())" @click="$wire.addMaterialToComposition(id, 1.00); q = ''; open = false" class="w-full text-left px-4 py-2 text-xs hover:bg-primary-600 hover:text-white border-b last:border-0 dark:border-gray-700 transition-colors flex justify-between items-center group">
                            <span x-text="mat.fullName" class="font-bold"></span>
                            <x-filament::icon icon="heroicon-m-plus-circle" class="w-4 h-4 opacity-50 group-hover:opacity-100" />
                        </button>
                    </template>
                </div>
            </div>

            {{-- ТАБЛИЦА СОСТАВА --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col" wire:key="tc-comp-{{ $selectedTechCardId }}">
                <div class="flex-1">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900 text-[9px] uppercase text-gray-400 font-black z-10 shadow-sm">
                            <tr>
                                <th class="px-5 py-2.5">Материал</th>
                                <th class="px-5 py-2.5 w-32 text-center">Расход</th>
                                <th class="px-5 py-2.5 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-800">
                            @forelse($selectedTechCardData['tech_card_materials'] ?? [] as $index => $item)
                                <tr class="hover:bg-primary-50/20 transition-all group" wire:key="mat-{{ $index }}">
                                    <td class="px-5 py-2 text-[10px] font-bold text-gray-700 dark:text-gray-200 uppercase">{{ $item['material']['fullName'] ?? 'Неизвестно' }}</td>
                                    <td class="px-5 py-2 text-center">
                                        <input type="number" step="0.5" wire:model.blur="selectedTechCardData.tech_card_materials.{{ $index }}.quantity" class="w-20 text-center bg-transparent border-none p-0 text-[11px] font-black focus:ring-1 focus:ring-primary-500 rounded">
                                    </td>
                                    <td class="px-5 py-2 text-right">
                                        <button wire:click="removeMaterial({{ $index }})" type="button" class="text-gray-300 hover:text-danger-600 transition-all opacity-0 group-hover:opacity-100">
                                            <x-filament::icon icon="heroicon-m-x-circle" class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="p-12 text-center text-[10px] text-gray-300 uppercase font-black italic tracking-widest">Состав не заполнен</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800/20 border-t dark:border-gray-800 flex justify-between items-center shrink-0">
                    <x-filament::button size="xs" color="danger" variant="ghost" icon="heroicon-m-trash" wire:click="deleteTechCard({{ $selectedTechCardId }})" wire:confirm="Удалить техкарту?">Удалить ТК</x-filament::button>
                    <x-filament::button size="sm" color="success" icon="heroicon-m-check-badge" class="px-8 shadow-md" wire:click="saveTechCardComposition">Сохранить техкарту</x-filament::button>
                </div>
            </div>
        @else
            <div class="h-full flex flex-col items-center justify-center text-gray-200 bg-gray-50/10">
                <x-filament::icon icon="heroicon-o-beaker" class="w-20 h-20 opacity-5 mb-4" />
                <p class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-400">Выберите техкарту для работы</p>
            </div>
        @endif
    </div>

    {{-- LIGHTBOX --}}
    <div x-show="imgFull" x-transition.opacity @click="imgFull = false" @keydown.escape.window="imgFull = false" class="fixed inset-0 z-[1000] bg-black/90 backdrop-blur-md flex items-center justify-center p-10 cursor-zoom-out" style="display: none;">
        <img :src="imgSrc" class="max-w-full max-h-full rounded-2xl shadow-2xl border border-white/10">
        <div class="absolute top-10 right-10 p-2 bg-white/10 rounded-full text-white/50 hover:text-white"><x-filament::icon icon="heroicon-m-x-mark" class="w-8 h-8" /></div>
    </div>
</div>