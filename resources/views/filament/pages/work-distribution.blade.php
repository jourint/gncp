<x-filament-panels::page>
    {{-- 1. Верхняя панель настроек --}}
    <x-filament::section>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <x-filament::input.wrapper label="Дата заказов">
                <x-filament::input type="date" wire:model.live="selected_date" />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Выбор цеха">
                <x-filament::input.select wire:model.live="selected_job_id">
                    <option value="1">Закройный цех</option>
                    <option value="2">Швейный цех</option>
                    <option value="3">Сапожный цех</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Сотрудник">
                <x-filament::input.select wire:model.live="selected_employee_id">
                    <option value="">Выберите исполнителя...</option>
                    @foreach($this->employees as $emp)
                        <option value="{{ $emp->id }}">
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::dropdown>
                <x-slot name="trigger">
                    <x-filament::button icon="heroicon-m-bolt" color="warning">
                        Распределить заказы на цех
                    </x-filament::button>
                </x-slot>

                <x-filament::dropdown.list>
                    @foreach(\App\Filament\Pages\WorkDistribution\DistributeManager::getList() as $key => $algo)
                        <x-filament::dropdown.list.item 
                            wire:click="autoDistribute('{{ $key }}')"
                            wire:confirm="{{ $algo['label'] }}: {{ $algo['confirm'] }}"
                            icon="{{ $algo['icon'] }}"
                        >
                            {{ $algo['label'] }}
                        </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>


        </div>
    </x-filament::section>

    {{-- 2. Основная рабочая зона --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- КОЛОНКА 1: ДОСТУПНО --}}
        <x-filament::section icon="heroicon-m-plus-circle">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span>Доступные позиции</span>
                    <span class="text-xs font-normal text-gray-400 uppercase tracking-wider">
                        (Остаток: {{ $this->pendingWork->sum('remaining') }} / Всего: {{ $this->totalDayWork }})
                    </span>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm uppercase">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/10 text-gray-400 text-[10px]">
                            <th class="py-2 px-1">Модель/ТК</th>
                            <th class="py-2 px-1 text-center">Разм.</th>
                            <th class="py-2 px-1 text-center">Ост.</th>
                            <th class="py-2 px-1 text-right w-32">Выдать</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($this->pendingWork as $pos)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="py-3 px-1">
                                    <div class="font-bold leading-tight">{{ $pos->shoeTechCard->name }}</div>
                                    <div class="text-[9px] text-gray-400">{{ $pos->shoeTechCard->shoeModel->name }}</div>
                                </td>
                                <td class="py-3 px-1 text-center font-black">{{ $pos->size->name }}</td>
                                <td class="py-3 px-1 text-center font-bold text-primary-600">
                                    {{ $pos->remaining }}
                                </td>
                                <td class="py-3 px-1">
                                    <div class="flex items-center gap-1 justify-end" x-data="{ amount: {{ $pos->remaining }} }">
                                        <input 
                                            type="number" 
                                            x-model="amount" 
                                            class="w-14 p-1 text-xs border-gray-300 rounded-lg dark:bg-gray-800 dark:border-white/10" 
                                            min="1" 
                                            max="{{ $pos->remaining }}"
                                        >
                                        <x-filament::button 
                                            size="xs" 
                                            wire:loading.attr="disabled" 
                                            x-on:click="$wire.assignWork({{ $pos->id }}, amount)"
                                        >
                                            OK
                                        </x-filament::button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-8 text-center text-gray-400 italic">Нет позиций для распределения</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- КОЛОНКА 2: ВЫДАНО --}}
        <x-filament::section icon="heroicon-m-user">
            <x-slot name="heading">
                Выдано: {{ \App\Models\Employee::find($selected_employee_id)?->name ?? '---' }}
                @if($selected_employee_id && $this->assignedWork->isNotEmpty())
                    <span class="ml-4 inline-block"> {{-- Обертка для отступа и выравнивания --}}
                        <x-filament::button 
                            color="danger" 
                            size="xs"
                            icon="heroicon-m-trash"
                            wire:click="clearEmployeeWork"
                            wire:confirm="Вы уверены, что хотите удалить ВСЕ назначения этого сотрудника за сегодня?"
                            class="font-bold uppercase text-[9px] shadow-sm tracking-tighter"
                        >
                            Очистить всё
                        </x-filament::button>
                    </span>
                @endif
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm uppercase">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/10 text-gray-400 text-[10px]">
                            <th class="py-2 px-1">Модель/ТК</th>
                            <th class="py-2 px-1 text-center">Разм.</th>
                            <th class="py-2 px-1 text-center">Кол-во</th>
                            <th class="py-2 px-1 text-right">Вернуть</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($this->assignedWork as $work)
                            <tr class="hover:bg-danger-50 dark:hover:bg-danger-900/10 transition group">
                                <td class="py-3 px-1 text-[11px]">
                                    <div class="font-bold leading-tight">{{ $work->orderPosition->shoeTechCard->name }}</div>
                                </td>
                                <td class="py-3 px-1 text-center font-black">{{ $work->orderPosition->size->name }}</td>
                                <td class="py-3 px-1 text-center font-black text-success-600">{{ $work->quantity }}</td>
                                <td class="py-3 px-1 text-right">
                                    <div class="flex items-center gap-1 justify-end" x-data="{ amount: {{ $work->quantity }} }">
                                        <input 
                                            type="number" 
                                            x-model="amount" 
                                            class="w-14 p-1 text-xs border-gray-300 rounded-lg dark:bg-gray-800 dark:border-white/10" 
                                            min="1" 
                                            max="{{ $work->quantity }}"
                                        >
                                        <x-filament::button 
                                            size="xs"
                                            color="warning"
                                            wire:loading.attr="disabled"
                                            x-on:click="$wire.reduceAssignment({{ $work->id }}, amount)"
                                            wire:confirm="Вы уверены, что хотите списать указанное количество?"
                                        >
                                            OK
                                        </x-filament::button>
                                        <button 
                                            wire:click="removeAssignment({{ $work->id }})"
                                            wire:confirm="Вы уверены, что хотите удалить ВСЮ позицию?"
                                            class="text-gray-300 hover:text-danger-600 p-1"
                                        >
                                            <x-heroicon-m-trash class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-8 text-center text-gray-400 italic font-normal">Ничего не назначено</td></tr>
                        @endforelse
                    </tbody>
                    @if($this->assignedWork->isNotEmpty())
                        <tfoot>
                            <tr class="border-t border-gray-100 dark:border-white/10 font-black">
                                <td colspan="2" class="py-4 px-1 text-right text-gray-500 text-[10px]">ИТОГО:</td>
                                <td class="py-4 px-1 text-center text-lg text-primary-600">{{ $this->assignedWork->sum('quantity') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </x-filament::section>
    </div>

    {{-- 3. Сводная таблица загрузки всего цеха --}}
    <x-filament::section title="Общая картина по цеху" icon="heroicon-m-chart-bar" class="-mt-4">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                    <span>Распределенные пары по сотруднникам цеха</span>
            </div>
        </x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/10 text-gray-400 text-[10px] uppercase font-black">
                        <th class="py-2 px-4">Сотрудник</th>
                        <th class="py-2 px-4">Модели (пары)</th>
                        <th class="py-2 px-4 text-right">Всего</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5 uppercase text-[11px]">
                    @foreach($this->shopFloorLoad as $stat)
                        <tr @class([
                            'hover:bg-gray-50 dark:hover:bg-white/5 transition',
                            'bg-primary-50/30 dark:bg-primary-900/10' => $selected_employee_id == $stat['id']
                        ])>
                            <td class="py-4 px-4 font-bold">{{ $stat['name'] }}</td>
                            <td class="py-4 px-4 text-[10px]">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($stat['details'] as $modelName => $qty)
                                        <span class="bg-white dark:bg-gray-800 border border-gray-200 px-2 py-0.5 rounded shadow-sm">
                                            {{ $modelName }}: <span class="text-primary-600 font-black">{{ $qty }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-4 text-right font-black text-lg text-gray-700 dark:text-gray-300">
                                {{ $stat['total_qty'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- Сравнение заказа по цеху --}}
    <x-filament::modal width="7xl" :lazy="true"> {{-- screen - на весь экран --}}
        <x-slot name="trigger">
            <x-filament::button icon="heroicon-m-table-cells" color="gray">
                Сравнить нагрузку (с размерами)
            </x-filament::button>
        </x-slot>

        <x-slot name="heading">
            Сводная ведомость по цеху: {{ $this->selected_date }}
        </x-slot>

        @php
            $tableData = $this->shopFloorTableData;
        @endphp

        <div class="overflow-x-auto border rounded-xl dark:border-white/10 mt-4 max-h-[75vh]">
            <table class="w-full text-left text-[11px] uppercase tracking-tight border-separate border-spacing-0">
                <thead>
                    <tr class="bg-gray-100 dark:bg-white/5 text-gray-500 sticky top-0 z-20">
                        {{-- 1. Модель/Размер --}}
                        <th class="py-3 px-4 border-r border-b dark:border-white/10 w-80 sticky left-0 bg-gray-100 dark:bg-gray-900 z-30">
                            Модель и Размер
                        </th>
                        {{-- 2. НОВАЯ КОЛОНКА: План из заказа --}}
                        <th class="py-3 px-3 border-r border-b dark:border-white/10 text-center bg-gray-50 dark:bg-gray-800 font-black text-gray-900 dark:text-white w-28">
                            План (Заказ)
                        </th>
                        {{-- 3. Сотрудники --}}
                        @foreach($tableData['employees'] as $emp)
                            <th @class([
                                'py-3 px-2 text-center border-r border-b dark:border-white/10 min-w-[120px]',
                                'bg-primary-50 dark:bg-primary-900/40 text-primary-600' => $selected_employee_id == $emp['id']
                            ])>
                                <div class="font-black">{{ $emp['name'] }}</div>
                                <div class="text-[9px] opacity-70 font-normal">Итого: {{ $emp['total_qty'] }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-white/10">
                    @forelse($tableData['models'] as $rowKey)
                        @php
                            [$mName, $sName] = explode(' | ', $rowKey);
                            $plan = $tableData['orderTotals'][$rowKey] ?? 0;
                            $fact = $tableData['employees']->sum(fn($e) => $e['matrix_details'][$rowKey] ?? 0);
                            $isShortage = $fact < $plan;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            {{-- Модель и Размер --}}
                            <td class="py-2 px-4 border-r dark:border-white/10 sticky left-0 bg-white dark:bg-gray-900 z-10 border-b dark:border-white/5">
                                <div class="flex justify-between items-center gap-4">
                                    <span class="text-gray-600 dark:text-gray-400 font-medium">{{ $mName }}</span>
                                    <span class="bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded font-black text-primary-600 border dark:border-white/10">
                                        {{ str_replace('РАЗМЕР: ', '', $sName) }}
                                    </span>
                                </div>
                            </td>

                            {{-- ПЛАН / ФАКТ (Индикатор дефицита) --}}
                            <td @class([
                                'py-2 px-3 text-center border-r dark:border-white/10 font-black border-b dark:border-white/5',
                                'bg-danger-50 text-danger-700 dark:bg-danger-900/20' => $isShortage,
                                'bg-success-50 text-success-700 dark:bg-success-900/20' => !$isShortage,
                            ])>
                                {{ $fact }} / {{ $plan }}
                            </td>

                            {{-- Колонки сотрудников --}}
                            @foreach($tableData['employees'] as $emp)
                                @php $qty = $emp['matrix_details'][$rowKey] ?? 0; @endphp
                                <td @class([
                                    'py-2 px-2 text-center border-r dark:border-white/10 text-sm border-b dark:border-white/5',
                                    'text-gray-200 dark:text-gray-700' => $qty == 0,
                                    'text-gray-900 dark:text-white font-black bg-primary-50/10' => $qty > 0,
                                ])>
                                    {{ $qty > 0 ? $qty : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($tableData['employees']) + 2 }}" class="p-8 text-center text-gray-400 italic">
                                Данные отсутствуют
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 dark:bg-white/5 font-black border-t-2 dark:border-white/20 sticky bottom-0 z-20">
                    <tr>
                        <td class="py-3 px-4 border-r dark:border-white/10 text-right sticky left-0 bg-gray-100 dark:bg-gray-900 z-30">
                            ОБЩИЙ ВЫПУСК (ФАКТ / ПЛАН):
                        </td>
                        {{-- Общий итог по всему заказу --}}
                        <td class="py-3 px-3 border-r dark:border-white/10 text-center bg-gray-50 dark:bg-gray-800 text-primary-600">
                            {{ $tableData['employees']->sum('total_qty') }} / {{ array_sum($tableData['orderTotals']) }}
                        </td>
                        @foreach($tableData['employees'] as $emp)
                            <td class="py-3 px-2 text-center border-r dark:border-white/10 text-base text-primary-600">
                                {{ $emp['total_qty'] }}
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::modal>

</x-filament-panels::page>