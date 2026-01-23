<x-filament-panels::page>
    <div class="space-y-4">
        <!-- Форма выбора даты -->
        <div class="mb-4 p-3 bg-gray-50 rounded-lg shadow-sm">
            <label class="block text-sm font-medium text-gray-700 mb-1">Дата</label>
            <input type="date" wire:model="selected_date" class="w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            <button wire:click="showReport('cutting')" class="px-3 py-1.5 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200">
                Закройный
            </button>
            <button wire:click="showReport('sewing')" class="px-3 py-1.5 text-sm bg-green-100 text-green-700 rounded-md hover:bg-green-200">
                Швейный
            </button>
            <button wire:click="showReport('shoemaker')" class="px-3 py-1.5 text-sm bg-purple-100 text-purple-700 rounded-md hover:bg-purple-200">
                Сапожный
            </button>
            <button wire:click="showReport('miscellaneous')" class="px-3 py-1.5 text-sm bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200">
                Разнорабочий
            </button>
            <button wire:click="showReport('expedition')" class="px-3 py-1.5 text-sm bg-red-100 text-red-700 rounded-md hover:bg-red-200">
                Экспедиция
            </button>
            <button wire:click="showReport('stock_requirements')" class="px-3 py-1.5 text-sm bg-orange-100 text-orange-700 rounded-md hover:bg-orange-200">
                Требуется на складе
            </button>
        </div>

        <!-- Кнопка печати всегда отображается, если есть активный отчет -->
        @if($active_report)
        <div class="mb-4">
            <button onclick="printSection('{{ $active_report }}')" class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Печать
            </button>
        </div>
        @endif

        <!-- Закройный цех -->
        @if($active_report === 'cutting')
        <div id="cutting-report" class="p-4 bg-white rounded border">
            <h2 class="text-lg font-bold mb-3">Закройный цех ({{ $selected_date }})</h2>
            <div class="text-sm">
                @php
                    $grouped = $this->getCuttingReportProperty()->groupBy('model_name');
                @endphp
                @forelse($grouped as $modelName => $items)
                    <div class="font-bold mb-1 text-lg text-gray-800">{{ $modelName }}</div>
                    @foreach($items as $item)
                        @if($item['type'] === 'tech_card_header')
                            <div class="ml-2 mb-1 text-base">— {{ $item['tech_card_name'] }} (итого: <span class="font-bold text-base">{{ $item['total_quantity'] }}</span>)</div>
                            <div class="ml-4 mb-2 text-gray-600 text-lg font-mono">
                                @foreach($item['sizes'] as $size)
                                    {{ $size->size_id }}: {{ $size->total_quantity }};
                                @endforeach
                            </div>
                        @elseif($item['type'] === 'model_total')
                            <div class="ml-2 mb-2 mt-1 font-bold text-lg text-gray-800 border-t pt-1">Итого {{ $item['model_name'] }}: {{ $item['total_quantity'] }}</div>
                        @elseif($item['type'] === 'overall_total')
                            <div class="mt-3 font-bold text-xl text-gray-900 border-t pt-1">Общий итог: {{ $item['total_quantity'] }}</div>
                        @endif
                    @endforeach
                @empty
                    <p class="text-gray-500">Нет данных</p>
                @endforelse
            </div>
        </div>
        @endif

        <!-- Швейный цех -->
        @if($active_report === 'sewing')
        @php
            $displayDate = \Carbon\Carbon::parse($selected_date)->addDay()->format('Y-m-d');
        @endphp
        <div id="sewing-report" class="p-4 bg-white rounded border">
            <h2 class="text-lg font-bold mb-3">Швейный цех ({{ $displayDate }})</h2>
            <div class="text-sm">
                @php
                    $grouped = $this->getSewingReportProperty()->groupBy('employee_name');
                @endphp
                @forelse($grouped as $employeeName => $items)
                    <div class="font-bold mb-1 text-lg text-gray-800">{{ $employeeName }}</div>
                    @foreach($items as $item)
                        @if($item['type'] === 'tech_card_header')
                            <div class="ml-2 mb-1 text-base">— {{ $item['tech_card_name'] }} (итого: <span class="font-bold text-base">{{ $item['total_quantity'] }}</span>)</div>
                            <div class="ml-4 mb-2 text-gray-600 text-lg font-mono">
                                @foreach($item['sizes'] as $size)
                                    {{ $size->size_id }}: {{ $size->total_quantity }};
                                @endforeach
                            </div>
                        @elseif($item['type'] === 'employee_total')
                            <div class="ml-2 mb-2 mt-1 font-bold text-lg text-gray-800 border-t pt-1">Итого {{ $item['employee_name'] }}: {{ $item['total_quantity'] }}</div>
                        @elseif($item['type'] === 'overall_total')
                            <div class="mt-3 font-bold text-xl text-gray-900 border-t pt-1">Общий итог: {{ $item['total_quantity'] }}</div>
                        @endif
                    @endforeach
                @empty
                    <p class="text-gray-500">Нет данных</p>
                @endforelse
            </div>
        </div>
        @endif

        <!-- Сапожный цех -->
        @if($active_report === 'shoemaker')
        @php
            $displayDate = \Carbon\Carbon::parse($selected_date)->addDays(2)->format('Y-m-d');
        @endphp
        <div id="shoemaker-report" class="p-4 bg-white rounded border">
            <h2 class="text-lg font-bold mb-3">Сапожный цех ({{ $displayDate }})</h2>
            <div class="text-sm">
                @php
                    $grouped = $this->getShoemakerReportProperty()->groupBy('employee_name');
                @endphp
                @forelse($grouped as $employeeName => $items)
                    <div class="font-bold mb-1 text-lg text-gray-800">{{ $employeeName }}</div>
                    @foreach($items as $item)
                        @if($item['type'] === 'tech_card_header')
                            <div class="ml-2 mb-1 text-base">— {{ $item['tech_card_name'] }} (итого: <span class="font-bold text-base">{{ $item['total_quantity'] }}</span>)</div>
                            <div class="ml-4 mb-2 text-gray-600 text-lg font-mono">
                                @foreach($item['sizes'] as $size)
                                    {{ $size->size_id }}: {{ $size->total_quantity }};
                                @endforeach
                            </div>
                        @elseif($item['type'] === 'employee_total')
                            <div class="ml-2 mb-2 mt-1 font-bold text-lg text-gray-800 border-t pt-1">Итого {{ $item['employee_name'] }}: {{ $item['total_quantity'] }}</div>
                        @elseif($item['type'] === 'overall_total')
                            <div class="mt-3 font-bold text-xl text-gray-900 border-t pt-1">Общий итог: {{ $item['total_quantity'] }}</div>
                        @endif
                    @endforeach
                @empty
                    <p class="text-gray-500">Нет данных</p>
                @endforelse
            </div>
        </div>
        @endif

        <!-- Разнорабочий -->
        @if($active_report === 'miscellaneous')
        <div id="miscellaneous-report" class="p-4 bg-white rounded border">
            <h2 class="text-lg font-bold mb-3">Разнорабочий ({{ $selected_date }})</h2>
            <div class="text-sm">
                <div class="mb-3">
                    <h3 class="font-bold text-lg text-gray-800">Яички:</h3>
                    @forelse($this->getMiscellaneousReportProperty()['eggs'] as $egg)
                        <div class="text-lg">{{ $egg->color_name }} — {{ $egg->total_quantity }}</div>
                    @empty
                        <p class="text-gray-500">Нет данных</p>
                    @endforelse
                </div>

                <div class="mb-3">
                    <h3 class="font-bold text-lg text-gray-800">Стельки:</h3>
                    @php
                        $groupedStelki = $this->getMiscellaneousReportProperty()['stelki']->groupBy(['name', 'type', 'lining_id']);
                    @endphp
                    @forelse($groupedStelki as $name => $byType)
                        @foreach($byType as $type => $byLining)
                            @foreach($byLining as $liningId => $items)
                                <div class="text-base">
                                    <span class="font-bold">{{ $name }} ({{ \App\Enums\InsolesType::from($type)->getLabel() }})</span>
                                    @if($liningId)
                                        @php
                                            $lining = \App\Models\MaterialLining::find($liningId);
                                        @endphp
                                        @if($lining)
                                            <span>/ {{ $lining->fullName }}</span>
                                        @endif
                                    @endif
                                    —
                                    <span class="text-lg font-mono">
                                        @foreach($items->sortBy('size_id') as $item)
                                            {{ $item->size_id }}: {{ $item->total_quantity }};
                                        @endforeach
                                    </span>
                                </div>
                            @endforeach
                        @endforeach
                    @empty
                        <p class="text-gray-500">Нет данных</p>
                    @endforelse
                </div>

                <div class="mb-3">
                    <h3 class="font-bold text-lg text-gray-800">Подноски:</h3>
                    @php
                        $puffs = $this->getMiscellaneousReportProperty()['puffCounter']
                            ->whereNotNull('puff_id')
                            ->groupBy('puff_id')
                            ->map(function($items) {
                                $puffName = \App\Models\Puff::find($items->first()->puff_id)?->name ?? 'Неизвестно';
                                return ['name' => $puffName, 'total' => $items->sum('total_quantity')];
                            });
                    @endphp
                    @forelse($puffs as $puff)
                        <div class="text-lg">{{ $puff['name'] }} — {{ $puff['total'] }}</div>
                    @empty
                        <p class="text-gray-500">Нет данных</p>
                    @endforelse

                    <h3 class="font-bold mt-2 text-lg text-gray-800">Задники:</h3>
                    @php
                        $counters = $this->getMiscellaneousReportProperty()['puffCounter']
                            ->whereNotNull('counter_id')
                            ->groupBy('counter_id')
                            ->map(function($items) {
                                $counterName = \App\Models\Counter::find($items->first()->counter_id)?->name ?? 'Неизвестно';
                                return ['name' => $counterName, 'total' => $items->sum('total_quantity')];
                            });
                    @endphp
                    @forelse($counters as $counter)
                        <div class="text-lg">{{ $counter['name'] }} — {{ $counter['total'] }}</div>
                    @empty
                        <p class="text-gray-500">Нет данных</p>
                    @endforelse
                </div>

                <div>
                    <h3 class="font-bold text-lg text-gray-800">Доп. работы:</h3>
                    @forelse($this->getMiscellaneousReportProperty()['workflows'] as $wf)
                        <div class="text-lg">{{ $wf['name'] }} — {{ $wf['total_quantity'] }} пар</div>
                    @empty
                        <p class="text-gray-500">Нет данных</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        <!-- Экспедиция -->
        @if($active_report === 'expedition')
        @php
            $displayDate = \Carbon\Carbon::parse($selected_date)->addDays(2)->format('Y-m-d');
        @endphp
        <div id="expedition-report" class="p-4 bg-white rounded border">
            <h2 class="text-lg font-bold mb-3">Экспедиция ({{ $displayDate }})</h2>
            <div class="text-sm">
                @php
                    $grouped = $this->getExpeditionReportProperty()->groupBy('customer_name');
                @endphp
                @forelse($grouped as $customerName => $items)
                    <div class="font-bold mb-1 text-lg text-gray-800">{{ $customerName }}</div>
                    @foreach($items as $item)
                        @if($item['type'] === 'model_header')
                            <div class="ml-2 mb-1 text-base">— {{ $item['model_name'] }} / {{ $item['tech_card_name'] }} (итого: <span class="font-bold text-base">{{ $item['total_quantity'] }}</span>)</div>
                            <div class="ml-4 mb-2 text-gray-600 text-lg font-mono">
                                @foreach($item['sizes'] as $size)
                                    {{ $size->size_id }}: {{ $size->total_quantity }};
                                @endforeach
                            </div>
                        @elseif($item['type'] === 'customer_model_total')
                            <div class="ml-2 mb-2 mt-1 font-bold text-lg text-gray-800 border-t pt-1">Итого {{ $item['model_name'] }}: {{ $item['total_quantity'] }}</div>
                        @elseif($item['type'] === 'customer_total')
                            <div class="mb-2 mt-1 font-bold text-lg text-gray-800 border-t pt-1">Итого {{ $item['customer_name'] }}: {{ $item['total_quantity'] }}</div>
                        @elseif($item['type'] === 'overall_total')
                            <div class="mt-3 font-bold text-xl text-gray-900 border-t pt-1">Общий итог: {{ $item['total_quantity'] }}</div>
                        @endif
                    @endforeach
                @empty
                    <p class="text-gray-500">Нет данных</p>
                @endforelse
            </div>
        </div>
        @endif

        <!-- Требуется на складе -->
        @if($active_report === 'stock_requirements')
        <div id="stock_requirements-report" class="p-4 bg-white rounded border">
            <h2 class="text-lg font-bold mb-3">Требуется на складе ({{ $selected_date }})</h2>

            <div class="text-sm">
                <div class="mb-4">
                    <h3 class="font-bold text-lg text-gray-800">Для кроя:</h3>
                    @forelse($this->getStockRequirementsReportProperty()['materials_for_cuting'] as $item)
                        <div class="text-lg">
                            <span class="font-bold">{{ $item['material_name'] }}</span> —
                            {{ number_format($item['total_needed'], 2) }} {{ $item['unit_name'] }}
                        </div>
                    @empty
                        <p class="text-gray-500">Нет данных</p>
                    @endforelse
                </div>

                <div>
                    <h3 class="font-bold text-lg text-gray-800">Для стелек:</h3>
                    @forelse($this->getStockRequirementsReportProperty()['materials_for_insoles'] as $item)
                        <div class="text-lg">
                            <span class="font-bold">{{ $item['material_name'] }}</span> —
                            {{ number_format($item['total_needed'], 2) }} {{ $item['unit_name'] }}
                        </div>
                    @empty
                        <p class="text-gray-500">Нет данных</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

    </div>

    <script>
        function printSection(sectionId) {
            // Проверяем, что sectionId не пустой
            if (!sectionId) {
                alert('Нет активного отчета для печати.');
                return;
            }

            const fullId = sectionId + '-report';
            const content = document.getElementById(fullId);

            if (!content) {
                console.error(`Элемент с id="${fullId}" не найден.`);
                alert('Не удалось найти содержимое для печати.');
                return;
            }

            const originalContents = document.body.innerHTML;
            document.body.innerHTML = content.outerHTML;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
</x-filament-panels::page>