<div class="report-production">
    <h2 class="text-2xl font-black mb-6 border-b-2 border-black pb-2">
        План: {{ $active_report === 'sewing' 
        ? 'Швейный цех ' . \Carbon\Carbon::createFromFormat('Y-m-d', $selected_date)->addDays()->format('d.m.Y')
        : 'Сапожный цех'  . \Carbon\Carbon::createFromFormat('Y-m-d', $selected_date)->addDays(2)->format('d.m.Y') }} (заказ от {{ $selected_date }})
    </h2>

    @php 
        // Группируем по сотруднику для кладовщика
        $byEmployee = collect($data)->where('type', 'tech_card_header')->groupBy('title');
    @endphp

    @forelse($byEmployee as $employee => $tasks)
        <div class="mb-10 avoid-break">
            <div class="bg-gray-100 p-2 border-l-8 border-gray-800 mb-4">
                <h3 class="text-xl font-black uppercase">{{ $employee }}</h3>
            </div>

            <table class="w-full border-collapse table-fixed">
                <thead>
                    <tr class="border-b-2 border-gray-300 text-left text-xs uppercase font-bold">
                        <th class="py-2">Модель / Техкарта</th>
                        <th class="py-2">Размерная сетка</th>
                        <th class="py-2 text-right w-20">Итого</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($tasks as $task)
                        <tr>
                            <td class="py-3 pr-4">
                                <div class="font-bold text-gray-900 leading-tight">{{ $task['tech_card_name'] }}</div>
                            </td>
                            <td class="py-3">
                                <div class="font-mono text-base flex flex-wrap gap-x-3 gap-y-1">
                                    @foreach($task['sizes'] as $s)
                                        <span class="border-b border-gray-300">
                                            <span class="text-gray-400 text-[10px]">{{ $s->size_id }}:</span><span class="font-bold">{{ (int)$s->total_quantity }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 text-right font-black text-lg">
                                {{ $task['total_quantity'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="mt-2 text-right font-bold text-sm uppercase tracking-widest text-gray-500">
                Всего по сотруднику: {{ declension_pairs((int)$tasks->sum('total_quantity')) }}
            </div>
        </div>
    @empty
        <div class="text-center p-20 border-4 border-dashed rounded-3xl text-gray-300 font-black text-3xl uppercase">
            Нет заданий на этот день
        </div>
    @endforelse
</div>

<style>
    .avoid-break { page-break-inside: avoid; }
    @media print {
        table { font-size: 12px; }
    }
</style>