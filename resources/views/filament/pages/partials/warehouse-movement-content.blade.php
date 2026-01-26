<div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
    {{-- Левая колонка: Дерево --}}
    <div class="space-y-4 flex flex-col h-full">
        <div class="flex items-center justify-between h-[40px]">
            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Тип товаров</label>
            <x-filament::tabs label="Entity Type" class="border-none shadow-none bg-transparent">
                <x-filament::tabs.item wire:click="$set('entityType', 'materials')" :active="$entityType === 'materials'">
                    Материалы
                </x-filament::tabs.item>
                <x-filament::tabs.item wire:click="$set('entityType', 'soles')" :active="$entityType === 'soles'">
                    Подошвы
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>

        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-100 dark:border-gray-700 flex-1 min-h-[500px]">
            <div class="space-y-2 max-h-[500px] overflow-y-auto pr-2">
                @foreach($entityType === 'materials' ? $this->materialTree : $this->soleTree as $node)
                    <div class="pl-2 border-l-2 border-gray-200 dark:border-gray-700">
                        <div class="select-none font-bold text-gray-900 dark:text-gray-100 flex items-center cursor-pointer py-1 text-sm"
                             wire:click="toggleNode('{{ $node['nodeKey'] }}')">
                            <x-filament::icon 
                                alias="panels::pages.dashboard.navigation-item" 
                                icon="{{ $node['expanded'] ? 'heroicon-m-chevron-down' : 'heroicon-m-chevron-right' }}" 
                                class="w-4 h-4 mr-2 text-gray-400" 
                            />
                            {{ $node['name'] }}
                        </div>

                        @if($node['expanded'])
                            <div class="pl-4 mt-1 space-y-1">
                                @foreach($node['children'] as $child)
                                    <div class="select-none cursor-pointer flex items-center justify-between text-xs bg-white dark:bg-gray-700 p-2 rounded shadow-sm group">
                                        <div class="flex-1" wire:click="addItemFromTree({{ $child['id'] }}, '{{ addslashes($child['name']) }}')">
                                            <span class="font-medium">{{ $child['name'] }}</span>
                                            <span class="text-gray-400 ml-2">(ост: {{ $child['stock'] }})</span>
                                        </div>
                                        <button wire:click="addItemFromTree({{ $child['id'] }}, '{{ addslashes($child['name']) }}')"
                                                class="text-primary-500 hover:text-primary-700 p-1">
                                            <x-heroicon-m-plus-circle class="w-5 h-5"/>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Правая колонка: Корзина --}}
    <div class="space-y-4 flex flex-col h-full">
        <div class="flex items-center justify-between h-[40px]">
            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Параметры</label>
            <x-filament::tabs label="Movement Type" class="border-none shadow-none bg-transparent">
                @foreach(\App\Enums\MovementType::cases() as $case)
                    <x-filament::tabs.item 
                        wire:click="$set('movementType', '{{ $case->value }}')" 
                        :active="$movementType === $case->value"
                    >
                        {{ $case->label() }}
                    </x-filament::tabs.item>
                @endforeach
            </x-filament::tabs>
        </div>

        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-100 dark:border-gray-700 flex-1 flex flex-col min-h-[500px]">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Позиции к проведению:</h3>

            <div class="space-y-2 overflow-y-auto pr-2 flex-1 min-h-0">
                @forelse($items as $index => $item)
                    <div class="flex justify-between items-center bg-white dark:bg-gray-700 p-3 rounded shadow-sm border border-gray-100 dark:border-gray-600">
                        <div class="text-xs flex-1 pr-4">
                            <span class="font-bold block text-gray-900 dark:text-gray-100">{{ $item['name'] }}</span>
                            <span class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ $item['type'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-500 font-medium whitespace-nowrap">
                                {{ $item['quantity'] ?? 0 }}
                            </span>
                            <x-filament::input.wrapper>
                                <x-filament::input type="number" step="1" 
                                    wire:model.live.debounce.300ms="items.{{ $index }}.quantity" 
                                    class="w-16 text-right text-xs font-mono" 
                                />
                            </x-filament::input.wrapper>
                            <x-filament::icon-button 
                                icon="heroicon-m-trash" 
                                color="danger" 
                                size="sm"
                                wire:click="removeItem({{ $index }})" 
                            />
                        </div>
                    </div>
                @empty
                    <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
                        <x-heroicon-o-document-plus class="w-10 h-10 mb-2 opacity-20"/>
                        <p class="text-xs italic">Корзина пуста</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>