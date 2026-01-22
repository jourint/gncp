<x-filament-panels::page>
    <div class="space-y-4">
        <!-- Форма выбора даты -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Дата</label>
            <input type="date" wire:model="selected_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
        </div>

        <div class="flex flex-wrap gap-2">
            <x-filament::button wire:click="showReport('cutting')" outlined>
                Закройный цех
            </x-filament::button>
            <x-filament::button wire:click="showReport('sewing')" outlined>
                Швейный цех
            </x-filament::button>
            <x-filament::button wire:click="showReport('shoemaker')" outlined>
                Сапожный цех
            </x-filament::button>
            <x-filament::button wire:click="showReport('miscellaneous')" outlined>
                Разнорабочий
            </x-filament::button>
            <x-filament::button wire:click="showReport('expedition')" outlined>
                Экспедиция
            </x-filament::button>
        </div>

        <!-- Закройный цех -->
        @if($active_report === 'cutting')
        <div id="cutting-report" class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Закройный цех ({{ $selected_date }})</h2>
                <button onclick="printSection('cutting-report')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Печать
                </button>
            </div>
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b">Модель</th>
                        <th class="py-2 px-4 border-b">Цвет / Текстура</th>
                        <th class="py-2 px-4 border-b">Размер</th>
                        <th class="py-2 px-4 border-b">Количество</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getCuttingReportProperty() as $item)
                        @if($item['type'] === 'tech_card_header')
                            <tr class="bg-blue-50">
                                <td class="py-2 px-4 border-b font-bold">{{ $item['model_name'] }}</td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['tech_card_name'] }}</td>
                                <td class="py-2 px-4 border-b font-bold text-right">Итого:</td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                            @foreach($item['sizes'] as $size)
                            <tr>
                                <td class="py-1 px-4"></td>
                                <td class="py-1 px-4"></td>
                                <td class="py-1 px-4 border-b">{{ $size->size_id }}</td>
                                <td class="py-1 px-4 border-b">{{ $size->total_quantity }}</td>
                            </tr>
                            @endforeach
                        @elseif($item['type'] === 'model_total')
                            <tr class="bg-green-50">
                                <td class="py-2 px-4 border-b font-bold">Итого {{ $item['model_name'] }}:</td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                        @elseif($item['type'] === 'overall_total')
                            <tr class="bg-yellow-50">
                                <td class="py-2 px-4 border-b font-bold">Общий итог:</td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="py-2 px-4 text-center">Нет данных</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        <!-- Швейный цех -->
        @if($active_report === 'sewing')
        <div id="sewing-report" class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Швейный цех ({{ $selected_date }})</h2>
                <button onclick="printSection('sewing-report')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Печать
                </button>
            </div>
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b">Сотрудник</th>
                        <th class="py-2 px-4 border-b">Модель / Цвет</th>
                        <th class="py-2 px-4 border-b">Размер</th>
                        <th class="py-2 px-4 border-b">Количество</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getSewingReportProperty() as $item)
                        @if($item['type'] === 'tech_card_header')
                            <tr class="bg-blue-50">
                                <td class="py-2 px-4 border-b font-bold">{{ $item['employee_name'] }}</td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['tech_card_name'] }}</td>
                                <td class="py-2 px-4 border-b font-bold text-right">Итого:</td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                            @foreach($item['sizes'] as $size)
                            <tr>
                                <td class="py-1 px-4"></td>
                                <td class="py-1 px-4"></td>
                                <td class="py-1 px-4 border-b">{{ $size->size_id }}</td>
                                <td class="py-1 px-4 border-b">{{ $size->total_quantity }}</td>
                            </tr>
                            @endforeach
                        @elseif($item['type'] === 'employee_total')
                            <tr class="bg-green-50">
                                <td class="py-2 px-4 border-b font-bold">Итого {{ $item['employee_name'] }}:</td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                        @elseif($item['type'] === 'overall_total')
                            <tr class="bg-yellow-50">
                                <td class="py-2 px-4 border-b font-bold">Общий итог:</td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="py-2 px-4 text-center">Нет данных</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        <!-- Сапожный цех -->
        @if($active_report === 'shoemaker')
        <div id="shoemaker-report" class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Сапожный цех ({{ $selected_date }})</h2>
                <button onclick="printSection('shoemaker-report')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Печать
                </button>
            </div>
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b">Сотрудник</th>
                        <th class="py-2 px-4 border-b">Модель / Цвет</th>
                        <th class="py-2 px-4 border-b">Размер</th>
                        <th class="py-2 px-4 border-b">Количество</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getShoemakerReportProperty() as $item)
                        @if($item['type'] === 'tech_card_header')
                            <tr class="bg-blue-50">
                                <td class="py-2 px-4 border-b font-bold">{{ $item['employee_name'] }}</td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['tech_card_name'] }}</td>
                                <td class="py-2 px-4 border-b font-bold text-right">Итого:</td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                            @foreach($item['sizes'] as $size)
                            <tr>
                                <td class="py-1 px-4"></td>
                                <td class="py-1 px-4"></td>
                                <td class="py-1 px-4 border-b">{{ $size->size_id }}</td>
                                <td class="py-1 px-4 border-b">{{ $size->total_quantity }}</td>
                            </tr>
                            @endforeach
                        @elseif($item['type'] === 'employee_total')
                            <tr class="bg-green-50">
                                <td class="py-2 px-4 border-b font-bold">Итого {{ $item['employee_name'] }}:</td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                        @elseif($item['type'] === 'overall_total')
                            <tr class="bg-yellow-50">
                                <td class="py-2 px-4 border-b font-bold">Общий итог:</td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="py-2 px-4 text-center">Нет данных</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        <!-- Разнорабочий -->
        @if($active_report === 'miscellaneous')
        <div id="miscellaneous-report" class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Разнорабочий ({{ $selected_date }})</h2>
                <button onclick="printSection('miscellaneous-report')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Печать
                </button>
            </div>
            <h3 class="text-lg font-semibold mt-4">Стельки:</h3>
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b">Название</th>
                        <th class="py-2 px-4 border-b">Черная?</th>
                        <th class="py-2 px-4 border-b">Размер</th>
                        <th class="py-2 px-4 border-b">Количество</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getMiscellaneousReportProperty()['stelki'] as $item)
                    <tr>
                        <td class="py-2 px-4 border-b">{{ $item->name }}</td>
                        <td class="py-2 px-4 border-b">{{ $item->is_black ? 'Да' : 'Нет' }}</td>
                        <td class="py-2 px-4 border-b">{{ $item->size_id }}</td>
                        <td class="py-2 px-4 border-b">{{ $item->total_quantity }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-2 px-4 text-center">Нет данных</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <h3 class="text-lg font-semibold mt-4">Подноски/Задники:</h3>
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b">Подносок</th>
                        <th class="py-2 px-4 border-b">Задник</th>
                        <th class="py-2 px-4 border-b">Количество</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getMiscellaneousReportProperty()['puffCounter'] as $item)
                    <tr>
                        <td class="py-2 px-4 border-b">{{ optional(\App\Models\Puff::find($item->puff_id))->name ?? 'Не указан' }}</td>
                        <td class="py-2 px-4 border-b">{{ optional(\App\Models\Counter::find($item->counter_id))->name ?? 'Не указан' }}</td>
                        <td class="py-2 px-4 border-b">{{ $item->total_quantity }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-2 px-4 text-center">Нет данных</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <h3 class="text-lg font-semibold mt-4">Доп. работы:</h3>
            <ul class="list-disc pl-5 mt-2">
                @forelse($this->getMiscellaneousReportProperty()['workflows'] as $wf)
                    <li>{{ $wf->model_name }}: {{ json_encode($wf->workflows) }}</li>
                @empty
                    <li>Нет данных</li>
                @endforelse
            </ul>
        </div>
        @endif

        <!-- Экспедиция -->
        @if($active_report === 'expedition')
        <div id="expedition-report" class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Экспедиция ({{ $selected_date }})</h2>
                <button onclick="printSection('expedition-report')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Печать
                </button>
            </div>
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b">Клиент</th>
                        <th class="py-2 px-4 border-b">Модель</th>
                        <th class="py-2 px-4 border-b">Размер</th>
                        <th class="py-2 px-4 border-b">Количество</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getExpeditionReportProperty() as $item)
                        @if($item['type'] === 'model_header')
                            <tr class="bg-blue-50">
                                <td class="py-2 px-4 border-b font-bold">{{ $item['customer_name'] }}</td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['model_name'] }}</td>
                                <td class="py-2 px-4 border-b font-bold text-right">Итого:</td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                            @foreach($item['sizes'] as $size)
                            <tr>
                                <td class="py-1 px-4"></td>
                                <td class="py-1 px-4"></td>
                                <td class="py-1 px-4 border-b">{{ $size->size_id }}</td>
                                <td class="py-1 px-4 border-b">{{ $size->total_quantity }}</td>
                            </tr>
                            @endforeach
                        @elseif($item['type'] === 'customer_total')
                            <tr class="bg-green-50">
                                <td class="py-2 px-4 border-b font-bold">Итого {{ $item['customer_name'] }}:</td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                        @elseif($item['type'] === 'overall_total')
                            <tr class="bg-yellow-50">
                                <td class="py-2 px-4 border-b font-bold">Общий итог:</td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b"></td>
                                <td class="py-2 px-4 border-b font-bold">{{ $item['total_quantity'] }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="py-2 px-4 text-center">Нет данных</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <script>
        function printSection(sectionId) {
            const content = document.getElementById(sectionId);
            const originalContents = document.body.innerHTML;
            document.body.innerHTML = content.innerHTML;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
</x-filament-panels::page>