<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
    <table class="w-full text-left">
        <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider">Материал / Заказчики</th>
                <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-right">Нужно</th>
                <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-right">На складе</th>
                <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-right">Дефицит</th>
                <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-right">Действия</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">

            @forelse($this->stock_analysis as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-900 dark:text-gray-100 {{ !$this->hasActiveOrders ? 'opacity-50' : '' }}">
                                {{ $row['name'] }}
                            </span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($row['details'] as $customer => $amount)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
                                        {{ $customer }}: {{ number_format($amount, 1) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-right font-mono text-gray-600 dark:text-gray-400">
                        {{ number_format($row['needed'], 2) }} <span class="text-[10px]">{{ $row['unit'] }}</span>
                    </td>

                    {{-- Скрываем данные склада и дефицита, если план уже выполнен --}}
                    @if($this->hasActiveOrders)
                        <td class="px-6 py-4 text-sm text-right font-mono text-gray-600 dark:text-gray-400">
                            {{ number_format($row['stock'], 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-mono font-bold {{ $row['diff'] < 0 ? 'text-danger-600' : 'text-success-600' }}">
                            {{ $row['diff'] > 0 ? '+' : '' }}{{ number_format($row['diff'], 2) }}
                        </td>
                    @else
                        <td colspan="2" class="px-6 py-4 text-right">
                            <span class="text-[10px] font-black uppercase text-success-600 bg-success-50 dark:bg-success-900/20 px-2 py-1 rounded-md">
                                Материалы списаны / Заказ выполнен
                            </span>
                        </td>
                    @endif

                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            @if($this->hasActiveOrders)
                                @if($row['stock'] < 0)
                                    <x-filament::button size="xs" color="gray" outline
                                        wire:click="resetToZero({{ $row['material_id'] }}, '{{ addslashes($row['name']) }}')">
                                        Обнулить
                                    </x-filament::button>
                                @endif
                                
                                @if($row['diff'] < 0)
                                    <x-filament::button size="xs" color="success"
                                        wire:click="fillToBalance({{ $row['material_id'] }}, {{ $row['needed'] }}, '{{ addslashes($row['name']) }}')">
                                        В баланс
                                    </x-filament::button>
                                @endif
                            @else
                                <x-heroicon-m-check-badge class="w-5 h-5 text-success-500" />
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                        На выбранную дату потребностей не найдено
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>