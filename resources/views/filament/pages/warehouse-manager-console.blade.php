<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        {{-- Аналитическая панель --}}
        <div class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl border dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-4">
                <x-filament::input.wrapper>
                    <x-filament::input type="date" wire:model.live="selected_date" />
                </x-filament::input.wrapper>
                <div class="text-xs text-gray-500 max-w-xs leading-tight">
                    Анализ потребности материалов на основе запланированных заказов.
                </div>
            </div>
            
            <div class="flex gap-2">
                <x-filament::button 
                    :color="$this->hasActiveOrders ? 'danger' : 'gray'" 
                    :icon="$this->hasActiveOrders ? 'heroicon-m-arrow-down-on-square-stack' : 'heroicon-m-check-circle'" 
                    :disabled="!$this->hasActiveOrders"
                    wire:click="applyBulkOrderWriteOff"
                    wire:confirm="Это спишет материалы и пометит ВСЕ заказы на эту дату как 'Выполненные'. Продолжить?"
                >
                    {{ $this->hasActiveOrders ? 'Списать по плану' : 'План выполнен' }}
                </x-filament::button>

                <x-filament::button 
                    color="gray" 
                    icon="heroicon-m-table-cells" 
                    x-on:click="$dispatch('open-modal', { id: 'inventory-modal' })">
                    Текущие остатки
                </x-filament::button>

                <x-filament::button 
                    color="warning" 
                    icon="heroicon-m-arrows-right-left" 
                    x-on:click="$dispatch('open-modal', { id: 'movement-modal' })">
                    Управление остатками
                </x-filament::button>
            </div>
        </div>

        {{-- Таблица дефицита материалов --}}
        @include('filament.pages.partials.warehouse-analysis-table')

        {{-- Таблица дефицита подошв --}}
        @include('filament.pages.partials.warehouse-soles-table')

        {{-- Вынесенная модалка --}}
        <x-filament::modal id="movement-modal" width="6xl" sticky-header :lazy="true">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-truck class="w-6 h-6 text-primary-500"/>
                    <span>Складские движения</span>
                </div>
            </x-slot>

            @include('filament.pages.partials.warehouse-movement-content')

            <x-slot name="footer">
                <div class="flex justify-end gap-3">
                    <x-filament::button color="gray" x-on:click="close()">Отмена</x-filament::button>
                    <x-filament::button color="success" wire:click="processMovements" icon="heroicon-m-check-circle">
                        Провести {{ count($items) }} поз.
                    </x-filament::button>
                </div>
            </x-slot>
        </x-filament::modal>

        <!--  -->
        <x-filament::modal id="inventory-modal" width="5xl" sticky-header>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-primary-600">
                    <x-heroicon-o-squares-2x2 class="w-5 h-5"/>
                    <span class="text-sm font-black uppercase tracking-tight">Складская ведомость</span>
                </div>
            </x-slot>

            {{-- Компактная верхняя панель --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 border-b dark:border-gray-800 pb-4 print:hidden">
                <x-filament::tabs label="Раздел" class="bg-gray-100/50 dark:bg-white/5 rounded-lg p-1">
                    <x-filament::tabs.item :active="$activeTab === 'materials'" wire:click="$set('activeTab', 'materials')" class="px-3 py-1 text-xs">
                        Материалы
                    </x-filament::tabs.item>
                    <x-filament::tabs.item :active="$activeTab === 'soles'" wire:click="$set('activeTab', 'soles')" class="px-3 py-1 text-xs">
                        Подошва
                    </x-filament::tabs.item>
                </x-filament::tabs>

                <div class="flex items-center gap-3">
                    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass" size="sm" class="w-64 shadow-none">
                        <x-filament::input type="text" wire:model.live.debounce.300ms="searchStock" placeholder="Быстрый фильтр..." class="text-xs" />
                    </x-filament::input.wrapper>
                    <x-filament::icon-button icon="heroicon-m-printer" color="gray" size="sm" x-on:click="window.printReport('report-content-area')" />
                </div>
            </div>

            <div id="report-content-area">
                {{-- МАТЕРИАЛЫ: Плитки с тегами --}}
                @if($activeTab === 'materials')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($this->inventoryGroups as $typeId => $baseMaterials)
                            <div class="col-span-full mt-2 mb-1 flex items-center gap-2">
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">{{ $baseMaterials->first()->first()->materialType->name }}</span>
                                <div class="h-px flex-1 bg-gray-100 dark:bg-gray-800"></div>
                            </div>

                            @foreach($baseMaterials as $baseName => $items)
                                <div class="flex flex-col">
                                    <h4 class="text-[11px] font-black text-gray-800 dark:text-gray-200 uppercase mb-2 px-1">{{ $baseName }}</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($items as $material)
                                            <div class="relative group">
                                                {{-- Тег цвета и остатка --}}
                                                <div wire:click="startEditing({{ $material->id }})" 
                                                    class="cursor-pointer flex items-center gap-2 px-2 py-1 rounded-lg border dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-primary-500 transition shadow-sm
                                                            {{ $material->stock_quantity < 0 ? 'border-danger-300 bg-danger-50/50' : '' }}">
                                                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">{{ $material->color?->name ?? '—' }}</span>
                                                    <span class="text-[11px] font-mono font-black {{ $material->stock_quantity < 0 ? 'text-danger-600' : 'text-primary-600' }}">
                                                        {{ number_format($material->stock_quantity, 1) }}
                                                    </span>
                                                </div>

                                                {{-- Выпадающее меню правки --}}
                                                @if($this->editingMaterialId === $material->id)
                                                    <div class="absolute z-20 top-full mt-1 left-0 bg-white dark:bg-gray-800 shadow-2xl border dark:border-gray-700 rounded-xl p-2 flex items-center gap-2 animate-in zoom-in duration-100">
                                                        <input type="number" step="0.01" wire:model="editQuantity" class="w-16 text-xs p-1 border-gray-200 dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-900" autofocus />
                                                        <x-filament::icon-button icon="heroicon-m-minus" color="danger" size="sm" wire:click="applyQuickMovement('outcome', 'material')" />
                                                        <x-filament::icon-button icon="heroicon-m-plus" color="success" size="sm" wire:click="applyQuickMovement('income', 'material')" />
                                                        <x-filament::icon-button icon="heroicon-m-x-mark" color="gray" size="sm" wire:click="$set('editingMaterialId', null)" />
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endif

                {{-- ПОДОШВА: Сетка размеров --}}
                @if($activeTab === 'soles')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($this->soleInventory as $soleId => $items)
                            <div class="bg-gray-50/30 dark:bg-white/5 rounded-2xl p-4 border dark:border-gray-800">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="text-xs font-black text-gray-900 dark:text-gray-100 uppercase">{{ $items->first()->shoeSole->name }}</h4>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $items->first()->shoeSole->color?->name }}</span>
                                    </div>
                                    <span class="text-[10px] bg-primary-500/10 text-primary-600 px-2 py-0.5 rounded-full font-black italic">Σ {{ $items->sum('stock_quantity') }}</span>
                                </div>

                                <div class="grid grid-cols-5 gap-2">
                                    @foreach($items as $item)
                                        <div wire:click="startEditing({{ $item->id }})" 
                                            class="relative cursor-pointer flex flex-col items-center justify-center p-2 rounded-xl border border-white dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm hover:border-primary-500 transition group
                                                    {{ $item->stock_quantity < 0 ? 'bg-danger-50 border-danger-200' : '' }}">
                                            <span class="text-[9px] font-black text-gray-300 group-hover:text-primary-400 transition">{{ $item->size?->name }}</span>
                                            <span class="text-[11px] font-mono font-black {{ $item->stock_quantity < 0 ? 'text-danger-600' : 'text-gray-700 dark:text-gray-200' }}">
                                                {{ number_format($item->stock_quantity, 0) }}
                                            </span>

                                            @if($this->editingMaterialId === $item->id)
                                                <div class="absolute inset-0 z-10 bg-white dark:bg-gray-800 rounded-xl flex flex-col items-center justify-center p-1 gap-1 shadow-inner border border-primary-500 animate-in fade-in">
                                                    <input type="number" wire:model="editQuantity" class="w-full text-center text-[10px] p-0 border-none bg-transparent" autofocus />
                                                    <div class="flex gap-2">
                                                        <x-heroicon-m-minus class="w-4 h-4 text-danger-500" wire:click="applyQuickMovement('outcome', 'sole')" />
                                                        <x-heroicon-m-plus class="w-4 h-4 text-success-500" wire:click="applyQuickMovement('income', 'sole')" />
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-filament::modal>
        <!--  -->
</x-filament-panels::page>