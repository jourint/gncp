<!-- resources/views/filament/pages/partials/report-stock-requirements.blade.php -->
<div>
    <h2 class="text-2xl font-bold mb-6 border-b pb-2">Потребность склада в материалах на заказ от {{ $selected_date }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <section>
            <h3 class="bg-gray-800 text-white px-3 py-1 rounded mb-4 font-bold">ОСНОВНОЙ МАТЕРИАЛ (КРОЙ)</h3>
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 text-xs uppercase">
                        <th class="pb-2">Материал</th>
                        <th class="pb-2 text-right">Кол-во</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($data['materials_for_cutting'] as $item)
                        <tr>
                            <td class="py-2 text-sm">{{ $item['material_name'] }}</td>
                            <td class="py-2 text-right font-mono font-bold">
                                {{ number_format($item['total_needed'], 2) }} <span class="text-xs font-normal">{{ $item['unit_name'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section>
            <h3 class="bg-blue-600 text-white px-3 py-1 rounded mb-4 font-bold">ПОДОШВЫ</h3>
            <div class="space-y-3">
                @foreach($data['soles_needed'] as $sole)
                    <div class="border-l-4 border-blue-500 pl-3 py-1">
                        <div class="font-semibold text-gray-700 text-sm">{{ $sole['sole_name'] }}</div>
                        @if(empty($sole['sizes']))
                            <div class="text-xs text-gray-600 mt-1">Всего: <span class="font-bold">{{ number_format($sole['total_needed'], 0) }} шт.</span></div>
                        @else
                            <div class="text-xs text-gray-600 mt-1">
                                @foreach($sole['sizes'] as $sizeId => $qty)
                                    <span class="inline-block mr-4">
                                        <span class="font-semibold">{{ $sizeId }}</span>: {{ declension_pairs(number_format($qty, 0)) }}.
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>