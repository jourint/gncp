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
            
            <x-filament::button 
                color="warning" 
                icon="heroicon-m-arrows-right-left" 
                x-on:click="$dispatch('open-modal', { id: 'movement-modal' })">
                Управление остатками
            </x-filament::button>
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
    </div>
</x-filament-panels::page>