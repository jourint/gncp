<div>
    <h2 class="text-2xl font-bold mb-6 border-b pb-2">Дополнительные работы на заказ от {{ $selected_date }}</h2>

    {{-- 1. Секция: Яички --}}
    <section class="mb-8">
        <h3 class="text-lg font-bold text-red-600 uppercase mb-2">Яички (вставки)</h3>
        <div class="grid grid-cols-2 gap-4">
            @forelse($data['eggs'] as $egg)
                <div class="p-2 border rounded bg-gray-50 flex justify-between">
                    <span>{{ $egg->color_name }}</span>
                    <span class="font-bold">{{ declension_pairs((int)$egg->total_quantity) }}</span>
                </div>
            @empty
                <p class="text-gray-400">Не требуются</p>
            @endforelse
        </div>
    </section>

    {{-- 2. Секция: Стельки --}}
    <section class="mb-8">
        <h3 class="text-lg font-bold text-blue-600 uppercase mb-2">Стельки и полустельки</h3>
    
        @php
            $grandTotalTexon = collect();
            $grandTotalLining = collect();
        @endphp
    
        @foreach($data['stelki']->groupBy(['name', 'type']) as $name => $byType)
            @foreach($byType as $type => $byInsole)
                <div class="mb-1 p-4 border-b-2 border-gray-300">
                    <div class="font-extrabold text-gray-800 text-xl mb-3">
                        {{ $name }} ({{ \App\Enums\InsolesType::from($type)->getLabel() }}):
                    </div>
    
                    <div class="space-y-1 pl-4">
                        {{-- Сводка по Тексону --}}
                        @foreach($byInsole->groupBy('is_soft_texon') as $isSoftTexon => $byTexon)
                            @php
                                $texonName = $isSoftTexon ? 'Тексон с губкой' : 'Тексон жесткий';
                                $texonTotal = $byTexon->sum('total_quantity');
                                $grandTotalTexon[$texonName] = ($grandTotalTexon[$texonName] ?? 0) + $texonTotal;
                            @endphp
                            <div>
                                <p class="text-gray-600 italic font-mono">
                                    {{ $texonName }}:&nbsp;
                                    @forelse($byTexon->groupBy('size_id')->sortBy(fn($val, $key) => (int)$key) as $sizeId => $itemsForSize)
                                        {{ $sizeId }}:<span class="font-bold">{{ (int)$itemsForSize->sum('total_quantity') }}</span>@if(!$loop->last),&nbsp;@endif
                                    @empty
                                        <span class="text-gray-400">- нет -</span>
                                    @endforelse
                                    @if($texonTotal > 0)
                                        <span class="font-bold text-red-600 ml-2">
                                            ({{ declension_pairs((int)$texonTotal) }})
                                        </span>
                                    @endif
                                </p>
                            </div>
                        @endforeach
    
                        {{-- Детализация по подкладкам --}}
                        @foreach($byInsole->groupBy('lining_id') as $liningId => $items)
                            @php
                                // ОПТИМИЗАЦИЯ: Берем имя из заранее подготовленного массива $data['liningNames']
                                $liningName = $liningId ? ($data['liningNames'][$liningId] ?? 'Неизвестная подкладка') : 'Без подкладки';
                                $liningTotal = $items->sum('total_quantity');
                                if ($liningTotal > 0) {
                                    $grandTotalLining[$liningName] = ($grandTotalLining[$liningName] ?? 0) + $liningTotal;
                                }
                            @endphp
                            @if ($liningTotal > 0)
                            <div>
                                <p class="text-gray-600 italic font-mono">
                                    {{ $liningName }}:&nbsp;
                                    @foreach($items->sortBy('size_id') as $item)
                                        {{ $item->size_id }}:<span class="font-bold">{{ (int)$item->total_quantity }}</span>@if(!$loop->last),&nbsp;@endif
                                    @endforeach
                                    <span class="font-bold text-red-600 ml-2">
                                        ({{ declension_pairs((int)$liningTotal) }})
                                    </span>
                                </p>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endforeach
    
        {{-- Итоговая сводка по стелькам --}}
        @if($grandTotalTexon->isNotEmpty() || $grandTotalLining->isNotEmpty())
        <div class="mt-8 pt-4 border-t-4 border-double">
            <h3 class="text-xl font-bold mb-4">Итоговая сводка по стелькам:</h3>
            <div class="space-y-1 text-base pl-4">
                @foreach($grandTotalTexon->sortKeys() as $name => $total)
                    @if($total > 0)
                    <div><span class="font-semibold">{{ $name }}:</span>&nbsp;{{ declension_pairs((int)$total) }}</div>
                    @endif
                @endforeach
                @foreach($grandTotalLining->sortKeys() as $name => $total)
                     @if($total > 0)
                    <div><span class="font-semibold">{{ $name }}:</span>&nbsp;{{ declension_pairs((int)$total) }}</div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </section>

    {{-- 3. Секция: Подноски и Задники --}}
    <section class="mb-8 grid grid-cols-2 gap-6">
        <div>
            <h3 class="font-bold text-gray-700 border-b mb-2">Подноски</h3>
            @foreach($data['puffCounter']->whereNotNull('puff_id')->groupBy('puff_id') as $id => $items)
                <div class="flex justify-between border-b border-gray-100 py-1">
                    {{-- ОПТИМИЗАЦИЯ: Используем $data['puffNames'] --}}
                    <span>{{ $data['puffNames'][$id] ?? 'Неизвестно' }}</span>
                    <span class="font-bold">{{ declension_pairs((int)$items->sum('total_quantity')) }}</span>
                </div>
            @endforeach
        </div>
        <div>
            <h3 class="font-bold text-gray-700 border-b mb-2">Задники</h3>
            @foreach($data['puffCounter']->whereNotNull('counter_id')->groupBy('counter_id') as $id => $items)
                <div class="flex justify-between border-b border-gray-100 py-1">
                    {{-- ОПТИМИЗАЦИЯ: Используем $data['counterNames'] --}}
                    <span>{{ $data['counterNames'][$id] ?? 'Неизвестно' }}</span>
                    <span class="font-bold">{{ declension_pairs((int)$items->sum('total_quantity')) }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 4. Секция: Дополнительные работы --}}
    <section>
        <h3 class="text-lg font-bold text-green-600 uppercase mb-2">Дополнительные работы</h3>
        <div class="space-y-1">
            @foreach($data['workflows'] as $wf)
                <div class="flex justify-between p-2 hover:bg-gray-50 border-b">
                    <span>{{ $wf['name'] }}</span>
                    <span class="font-bold text-lg">{{ declension_pairs((int)$wf['total_quantity']) }}</span>
                </div>
            @endforeach
        </div>
    </section>
</div>