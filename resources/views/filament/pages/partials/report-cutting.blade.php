<div class="space-y-2">
    @php 
        $items = collect($data)->where('type', 'tech_card_header');
        $grouped = $items->groupBy('title');
    @endphp

    <h2 class="text-lg font-bold border-b border-slate-400 pb-1 italic text-slate-700">
        Заказ на производство: {{ $selected_date }}
    </h2>

    @foreach($grouped as $modelName => $techCards)
        <div class="border border-slate-300 rounded overflow-hidden page-break-inside-avoid mb-2">
            {{-- Модель (спокойный заголовок) --}}
            <div class="bg-slate-50 px-3 py-0.5 flex justify-between items-center border-b border-slate-300">
                <span class="text-sm font-bold text-slate-500">{{ $modelName }}</span>
                <span class="text-xs italic text-slate-400">{{ declension_pairs((int)$techCards->sum('total_quantity')) }}</span>
            </div>

            <div class="p-2 space-y-3">
                @foreach($techCards as $tc)
                    <div class="flex flex-col border-b border-slate-100 last:border-0 pb-1.5">
                        {{-- Техкарта - теперь крупнее и важнее --}}
                        <div class="text-[14px] font-black text-slate-800 italic mb-1 leading-none">
                            {{ $tc['tech_card_name'] }}
                        </div>
                        {{-- Размерная сетка --}}
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 ml-2">
                            @foreach($tc['sizes'] as $s)
                                <div class="flex items-baseline gap-1 border-b border-slate-300 px-1">
                                    <span class="text-[11px] font-bold text-slate-400">{{ $s->size_id }}:</span>
                                    <span class="text-xl font-black text-slate-900 leading-none">{{ (int)$s->total_quantity }}</span>
                                </div>
                            @endforeach
                            <div class="font-black text-slate-900 text-sm italic ml-4">
                                {{ declension_pairs((int)$tc['total_quantity']) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Сводка типов --}}
    @php 
        $typeSummary = $items->groupBy(function($item) {
            preg_match('/\((.*?)\)/', $item['tech_card_name'], $matches);
            return $matches[1] ?? 'Прочее';
        });
    @endphp
    <div class="mt-2 pt-1 border-t border-slate-800 flex justify-between items-center text-[12px]">
        <div class="flex gap-4">
            @foreach($typeSummary as $typeName => $group)
                <span class="font-bold text-slate-600">
                    <span class="text-slate-400 font-medium italic">{{ $typeName }}:</span> 
                    {{ declension_pairs((int)$group->sum('total_quantity')) }}
                </span>
            @endforeach
        </div>
        <div class="font-black text-lg italic text-slate-900">
            Итого: {{ declension_pairs((int)$items->sum('total_quantity')) }}
        </div>
    </div>
</div>