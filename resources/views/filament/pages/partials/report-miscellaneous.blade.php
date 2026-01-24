<!-- resources/views/filament/pages/partials/report-miscellaneous.blade.php -->
<div>
    <h2 class="text-2xl font-bold mb-6 border-b pb-2">Отчет для разнорабочего</h2>

    <section class="mb-8">
        <h3 class="text-lg font-bold text-red-600 uppercase mb-2">Яички (вставки)</h3>
        <div class="grid grid-cols-2 gap-4">
            @forelse($data['eggs'] as $egg)
                <div class="p-2 border rounded bg-gray-50 flex justify-between">
                    <span>{{ $egg->color_name }}</span>
                    <span class="font-bold">{{ (int)$egg->total_quantity }} пар</span>
                </div>
            @empty
                <p class="text-gray-400">Не требуются</p>
            @endforelse
        </div>
    </section>

    <section class="mb-8">
        <h3 class="text-lg font-bold text-blue-600 uppercase mb-2">Стельки и полустельки</h3>
        @foreach($data['stelki']->groupBy(['name', 'type']) as $name => $byType)
            @foreach($byType as $type => $rows)
                <div class="mb-3 p-3 border rounded shadow-sm">
                    <div class="font-bold text-gray-800">
                        {{ $name }} ({{ \App\Enums\InsolesType::from($type)->getLabel() }})
                    </div>
                    @foreach($rows->groupBy('lining_id') as $liningId => $items)
                        <div class="ml-2 mt-1 text-sm text-gray-600 italic">
                            {{ $liningId ? \App\Models\MaterialLining::find($liningId)?->fullName : 'Без подкладки' }}
                        </div>
                        <div class="font-mono text-lg pl-2">
                            @foreach($items->sortBy('size_id') as $item)
                                <span class="mr-3">{{ $item->size_id }}:<span class="font-bold">{{ (int)$item->total_quantity }}</span></span>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endforeach
    </section>

    <section class="mb-8 grid grid-cols-2 gap-6">
        <div>
            <h3 class="font-bold text-gray-700 border-b mb-2">Подноски</h3>
            @foreach($data['puffCounter']->whereNotNull('puff_id')->groupBy('puff_id') as $id => $items)
                <div class="flex justify-between border-b border-gray-100 py-1">
                    <span>{{ \App\Models\Puff::find($id)?->name }}</span>
                    <span class="font-bold">{{ (int)$items->sum('total_quantity') }}</span>
                </div>
            @endforeach
        </div>
        <div>
            <h3 class="font-bold text-gray-700 border-b mb-2">Задники</h3>
            @foreach($data['puffCounter']->whereNotNull('counter_id')->groupBy('counter_id') as $id => $items)
                <div class="flex justify-between border-b border-gray-100 py-1">
                    <span>{{ \App\Models\Counter::find($id)?->name }}</span>
                    <span class="font-bold">{{ (int)$items->sum('total_quantity') }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h3 class="text-lg font-bold text-green-600 uppercase mb-2">Дополнительные работы</h3>
        <div class="space-y-1">
            @foreach($data['workflows'] as $wf)
                <div class="flex justify-between p-2 hover:bg-gray-50 border-b">
                    <span>{{ $wf['name'] }}</span>
                    <span class="font-bold text-lg">{{ $wf['total_quantity'] }} пар</span>
                </div>
            @endforeach
        </div>
    </section>
</div>