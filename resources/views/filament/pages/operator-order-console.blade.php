<x-filament-panels::page>
    <div class="flex flex-col gap-6">

        {{-- Заголовок: Клиент и Дата --}}
        <x-filament::section>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <x-filament::input.wrapper label="Заказчик">
                        <x-filament::input.select wire:model="customer_id">
                            <option value="">Выберите клиента...</option>
                            @foreach(\App\Models\Customer::all() as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div class="md:col-span-1">
                    <x-filament::input.wrapper label="Дата заказа">
                        <x-filament::input type="date" wire:model="delivery_date" />
                    </x-filament::input.wrapper>
                </div>
                <div class="md:col-span-1">
                    <x-filament::button wire:click="saveOrder" color="success" icon="heroicon-m-shopping-cart" class="w-full">
                        Оформить
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Выбор Модели и Техкарты --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-filament::section title="Поиск модели" compact>
                <x-filament::input.select wire:model.live="selected_model_id">
                    <option value="">Выберите из списка...</option>
                    @foreach(\App\Models\ShoeModel::all() as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::section>

            <x-filament::section title="Выбор техкарты (цвета)" compact>
                <div class="flex flex-wrap gap-2">
                    @forelse($this->availableTechCards as $tc)
                        <button wire:click="addTechCardToOrder({{ $tc->id }})"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg bg-primary-600 text-white hover:bg-primary-500 shadow-sm transition">
                            + {{ $tc->name }}
                        </button>
                    @empty
                        <span class="text-gray-400 text-sm">Сначала выберите модель...</span>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        {{-- Основная таблица --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 dark:bg-white/5 dark:border-white/10">
                        <tr>
                            <th class="p-4 text-xs font-black uppercase text-gray-500 w-1/4 whitespace-nowrap">Модель / Техкарта / Подкладка</th>
                            <th class="p-4 text-xs font-black uppercase text-gray-500 text-center">Размерная сетка (заполните нули)</th>
                            <th class="p-4 text-center text-xs font-black uppercase text-gray-500 w-24 whitespace-nowrap">Итого</th>
                            <th class="p-4 w-12 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5 uppercase">
                        @foreach($rows as $idx => $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                {{-- В строке таблицы --}}
                                <td class="p-4 align-top">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                        {{ $row['tech_card_name'] }}
                                    </div>
                                    <div class="mt-1">
                                        <x-filament::input.select 
                                            wire:model.live="rows.{{ $idx }}.lining_id"
                                            x-data
                                            @change="$wire.updateLiningForRow({{ $idx }}, parseInt($event.target.value) || null)"
                                            class="block w-full rounded-lg text-sm bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                                            <option value="">Выберите подкладку...</option>
                                            @foreach($this->availableLinings as $lining)
                                                <option value="{{ $lining->id }}">{{ $lining->fullName }}</option>
                                            @endforeach
                                        </x-filament::input.select>
                                    </div>
                                </td>

                                <td class="p-4">
                                    <div class="flex flex-row flex-nowrap gap-1 overflow-x-auto pb-2 scrollbar-hide">
                                        @foreach($row['grid'] as $sizeId => $qty)
                                            <div class="flex flex-col flex-shrink-0 w-12 border border-gray-200 rounded-lg bg-gray-50 dark:border-white/10 dark:bg-gray-800 focus-within:ring-2 focus-within:ring-primary-500">
                                                <div class="bg-gray-200/50 py-1 text-[9px] font-black text-center border-b border-gray-200 dark:bg-white/5 dark:border-white/10">
                                                    {{ $sizeNames[$sizeId] ?? $sizeId }}
                                                </div>
                                                <input type="number"
                                                       wire:model.live="rows.{{ $idx }}.grid.{{ $sizeId }}"
                                                       onfocus="this.select()"
                                                       min="0"
                                                       class="w-full border-none p-2 text-center text-sm bg-transparent focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full bg-primary-50 text-primary-700 font-black text-sm dark:bg-primary-900/30 dark:text-primary-400">
                                        {{ array_sum($row['grid']) }}
                                    </span>
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="nextTechCard({{ $idx }})" title="Копировать в следующую ТК" class="text-gray-400 hover:text-primary-500">
                                            <x-heroicon-m-document-duplicate class="w-5 h-5"/>
                                        </button>
                                        <button wire:click="removeRow({{ $idx }})" class="text-gray-400 hover:text-danger-500">
                                            <x-heroicon-m-trash class="w-5 h-5"/>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50/50 dark:bg-white/5">
                        <tr>
                            <td colspan="2" class="p-4 text-right text-sm font-bold text-gray-500 uppercase">
                                Всего пар в заказе:
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex px-4 py-2 rounded-xl bg-success-500 text-white font-black text-lg shadow-sm">
                                    {{ collect($rows)->pluck('grid')->flatten()->map(fn($v) => (int)$v)->sum() }}
                                </span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>