<!-- resources/views/filament/pages/partials/report-stock-requirements.blade.php -->
<div>
    <h2 class="text-2xl font-bold mb-6 border-b pb-2">Потребность склада в материалах</h2>

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
            <h3 class="bg-blue-600 text-white px-3 py-1 rounded mb-4 font-bold">КОМПЛЕКТУЮЩИЕ (СТЕЛЬКИ)</h3>
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 text-xs uppercase">
                        <th class="pb-2">Наименование</th>
                        <th class="pb-2 text-right">Кол-во</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($data['materials_for_insoles'] as $item)
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
    </div>
</div>