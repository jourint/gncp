<x-filament-panels::page>
    @if($activeModelId)
        <div class="space-y-6" x-data="{ 
            catalog: @js($catalog),
            state: @entangle('state'),
            search: { color: '', sole: '', mat1: '', mat2: '', comp: '' }
        }">
            {{-- 1. ОСНОВНЫЕ ДАННЫЕ --}}
            <x-filament::section>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-filament-forms::field-wrapper label="Артикул модели">
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" x-model="state.name" wire:model="state.name" />
                        </x-filament::input.wrapper>
                    </x-filament-forms::field-wrapper>

                    <x-filament-forms::field-wrapper label="Тип обуви">
                        <x-filament::input.wrapper>
                            <select wire:model="state.shoe_type_id" class="block w-full border-none bg-transparent px-3 py-1.5 text-sm focus:ring-0 appearance-none dark:text-white">
                                <option value="">Выберите тип...</option>
                                <template x-for="(name, id) in catalog.types" :key="id">
                                    <option :value="id" x-text="name" :selected="state.shoe_type_id == id"></option>
                                </template>
                            </select>
                        </x-filament::input.wrapper>
                    </x-filament-forms::field-wrapper>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t dark:border-gray-800">
                    <x-filament-forms::field-wrapper label="Тип стельки">
                        <x-filament::input.wrapper>
                            <select wire:model="state.shoe_insole_id" class="block w-full border-none bg-transparent px-3 py-1.5 text-sm focus:ring-0 appearance-none dark:text-white">
                                <option value="">Без стельки</option>
                                <template x-for="(name, id) in catalog.insoles" :key="id">
                                    <option :value="id" x-text="name"></option>
                                </template>
                            </select>
                        </x-filament::input.wrapper>
                    </x-filament-forms::field-wrapper>

                    <x-filament-forms::field-wrapper label="Тип задника">
                        <x-filament::input.wrapper>
                            <select wire:model="state.counter_id" class="block w-full border-none bg-transparent px-3 py-1.5 text-sm focus:ring-0 appearance-none dark:text-white">
                                <option value="">Без задника</option>
                                <template x-for="(name, id) in catalog.counters" :key="id">
                                    <option :value="id" x-text="name"></option>
                                </template>
                            </select>
                        </x-filament::input.wrapper>
                    </x-filament-forms::field-wrapper>

                    <x-filament-forms::field-wrapper label="Тип подноска">
                        <x-filament::input.wrapper>
                            <select wire:model="state.puff_id" class="block w-full border-none bg-transparent px-3 py-1.5 text-sm focus:ring-0 appearance-none dark:text-white">
                                <option value="">Без подноска</option>
                                <template x-for="(name, id) in catalog.puffs" :key="id">
                                    <option :value="id" x-text="name"></option>
                                </template>
                            </select>
                        </x-filament::input.wrapper>
                    </x-filament-forms::field-wrapper>
                </div>
            </x-filament::section>

            {{-- 2. НАСТРОЙКИ (Размеры и Процессы) --}}
            <x-filament::section collapsible collapsed icon="heroicon-o-adjustments-horizontal">
                <x-slot name="heading">Настройки производства</x-slot>
                <div class="space-y-8 py-4">
                    <x-filament-forms::field-wrapper label="Размерный ряд">
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($sizeNames as $id => $name)
                                <button wire:click="toggleSize({{ $id }})" type="button"
                                    class="px-4 py-2 rounded-lg border-2 font-bold transition-all text-sm
                                    {{ in_array($id, $state['available_sizes']) ? 'border-primary-600 bg-primary-50 text-primary-600' : 'border-gray-100 text-gray-400 dark:border-gray-800' }}">
                                    {{ $name }}
                                </button>
                            @endforeach
                        </div>
                    </x-filament-forms::field-wrapper>

                    <x-filament-forms::field-wrapper label="Рабочие процессы">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2">
                            @foreach($workflowNames as $id => $name)
                                <button wire:click="toggleWorkflow({{ $id }})" type="button"
                                    class="flex items-center justify-between px-3 py-2 rounded-xl border transition-all text-sm
                                    {{ in_array($id, $state['workflows']) ? 'border-success-500 bg-success-50 text-success-700' : 'border-gray-200 text-gray-500 dark:border-gray-800' }}">
                                    <span class="truncate">{{ $name }}</span>
                                    @if(in_array($id, $state['workflows'])) <x-filament::icon icon="heroicon-m-check-circle" class="w-4 h-4 ml-2" /> @endif
                                </button>
                            @endforeach
                        </div>
                    </x-filament-forms::field-wrapper>

                    <div class="pt-6 border-t dark:border-gray-800 grid grid-cols-3 gap-6">
                        @foreach(['price_coeff_cutting' => 'Закройка', 'price_coeff_sewing' => 'Пошив', 'price_coeff_shoemaker' => 'Сборка'] as $k => $l)
                            <x-filament-forms::field-wrapper :label="$l">
                                <x-filament::input.wrapper prefix="x">
                                    <x-filament::input type="number" step="0.01" wire:model="state.{{ $k }}" />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>
                        @endforeach
                    </div>
                </div>
            </x-filament::section>

            @include('filament.pages.model-builder.tech-cards-lite')
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-24 border border-dashed rounded-3xl dark:border-gray-800">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-12 h-12 text-gray-300 mb-4" />
            <p class="text-gray-400 font-medium">Выберите модель для начала работы</p>
        </div>
    @endif
</x-filament-panels::page>