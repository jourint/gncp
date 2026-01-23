<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        {{-- Настройки операции --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип операции</label>
                    <select wire:model="movementType"
                            class="block w-full rounded-lg border-gray-300 bg-white py-1.5 pl-3 pr-10 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        @foreach(\App\Enums\MovementType::cases() as $case)
                            <option value="{{ $case->value }}">{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип товаров</label>
                    <select wire:model.live="entityType"
                            class="block w-full rounded-lg border-gray-300 bg-white py-1.5 pl-3 pr-10 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        <option value="materials">Материалы</option>
                        <option value="soles">Подошвы</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button wire:click="processMovements"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Провести операции
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Левая колонка: дерево --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $entityType === 'materials' ? 'Материалы' : 'Подошвы' }}
                </h3>

                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($entityType === 'materials' ? $materialTree : $soleTree as $node)
                        <div class="pl-2 border-l-2 border-gray-200 dark:border-gray-700">
                            <div class="font-bold text-gray-900 dark:text-gray-100 flex items-center cursor-pointer py-1"
                                 wire:click="toggleNode({{ $node['id'] }})">
                                @if($node['expanded'])
                                    <x-heroicon-m-chevron-down class="w-4 h-4 mr-2"/>
                                @else
                                    <x-heroicon-m-chevron-right class="w-4 h-4 mr-2"/>
                                @endif
                                {{ $node['name'] }}
                            </div>

                            @if(isset($node['children']) && $node['expanded'])
                                <div class="pl-6 mt-1 space-y-1">
                                    @foreach($node['children'] as $child)
                                        <div class="flex items-center justify-between text-sm bg-gray-50 dark:bg-gray-700 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600"
                                             x-on:mousedown.prevent
                                             x-on:dblclick="setTimeout(() => $wire.call('addItemFromTree', {{ $child['id'] }}, '{{ addslashes($child['name']) }}'), 0)">
                                            <div class="flex-1 cursor-pointer">
                                                <span>{{ $child['name'] }}</span>
                                                <span class="text-xs text-gray-500 ml-2">(остаток: {{ $child['stock'] ?? 0 }})</span>
                                            </div>
                                            <button wire:click.stop="addItemFromTree({{ $child['id'] }}, '{{ addslashes($child['name']) }}')"
                                                    class="ml-2 text-primary-500 hover:text-primary-700">
                                                <x-heroicon-m-arrow-right class="w-4 h-4"/>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Правая колонка: добавление позиций --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Текущие позиции:</h3>

                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($items as $index => $item)
                        <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 p-3 rounded">
                            <div class="text-sm flex-1">
                                <span class="font-medium">{{ $item['name'] }}</span>
                            </div>
                            <input type="number" step="0.01"
                                   wire:model.live.debounce.300ms="items.{{ $index }}.quantity"
                                   x-on:input="$nextTick(() => {
                                       let val = parseFloat($el.value);
                                       if (isNaN(val)) {
                                           $el.value = 0;
                                           $wire.set('items.{{ $index }}.quantity', 0);
                                       } else {
                                           $wire.set('items.{{ $index }}.quantity', val);
                                       }
                                   })"
                                   x-on:keydown.tab.prevent="$nextTick(() => {
                                       const inputs = document.querySelectorAll('[data-quantity-input]');
                                       const currentIndex = Array.from(inputs).indexOf($el);
                                       if (currentIndex < inputs.length - 1) {
                                           inputs[currentIndex + 1].focus();
                                       }
                                   })"
                                   data-quantity-input
                                   class="ml-2 w-20 block rounded border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-600 dark:text-white">
                            <button wire:click="removeItem({{ $index }})"
                                    tabindex="-1"
                                    class="text-red-500 hover:text-red-700 ml-2">
                                <x-heroicon-m-trash class="w-4 h-4"/>
                            </button>
                        </div>
                    @empty
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Позиций нет</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>