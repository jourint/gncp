<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        
        {{-- 1. Панель фильтров (4 колонки) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 no-print items-start">
            
            {{-- 1 колонка: Период --}}
            <div class="flex flex-col gap-3">
                <x-filament::input.wrapper label="С">
                    <x-filament::input type="date" wire:model.live="date_from" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="По">
                    <x-filament::input type="date" wire:model.live="date_to" />
                </x-filament::input.wrapper>
            </div>

            {{-- 2 колонка: Выбор сотрудника и цеха --}}
            <div class="flex flex-col gap-3">
                <x-filament::input.wrapper>
                    <x-filament::input type="text" placeholder="Поиск сотрудника..." wire:model.live="search_employee" />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="selected_job_position" class="border-none">
                        <option value="">Все цеха (выбор)</option>
                        @foreach(\App\Models\JobPosition::all() as $pos)
                            <option value="{{ $pos->id }}">{{ $pos->value }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            {{-- 3 колонка: Кнопки действий 1 --}}
            <div class="flex flex-col gap-3">
                <x-filament::button
                    color="success"
                    icon="heroicon-m-arrow-down-tray" 
                    wire:click="exportToExcel"
                >
                    Экспорт в Excel
                </x-filament::button>

                 <x-filament::button color="slate" icon="heroicon-m-printer" x-on:click="window.printReport('report-content-area')" class="w-full">
                    Печать страницы
                </x-filament::button>
            </div>

            {{-- 4 колонка: Кнопки действий 2 --}}
            <div class="flex flex-col gap-3">
                <x-filament::button 
                    color="warning" 
                    icon="heroicon-m-banknotes"
                    wire:click="payAllFiltered"
                    wire:confirm="Внимание! Это отметит ВСЕ текущие задолженности в списке как 'Оплаченные'. Продолжить?"
                    class="w-full"
                >
                    Оплатить всем в списке
                </x-filament::button>

               <x-filament::button 
                    color="amber" 
                    icon="heroicon-m-wrench-screwdriver" 
                    wire:click="openExtraWorks" 
                    class="w-full shadow-sm"
                >
                    Дополнительные работы
                </x-filament::button>
            </div>
        </div>

        {{-- 2. Основная область отчета (ID для глобальной печати) --}}
        <div id="report-content-area">
            {{-- Заголовок только для печати --}}
            <div class="hidden print:block mb-6 text-center border-b-2 border-black pb-4">
                <h1 class="text-2xl font-bold uppercase">Зарплатная ведомость</h1>
                <p class="text-lg italic">Период: {{ $date_from }} — {{ $date_to }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="p-4 text-xs font-bold uppercase text-slate-500">Сотрудник</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-500 text-center">Пар</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-500 text-right">Начислено</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-500 text-right">Выплачено</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-500 text-right">К выплате</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-500 text-right print:hidden">Действие</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($this->payrollData as $row)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4">
                                    <button wire:click="openDetails({{ $row['id'] }})" class="text-left group outline-none">
                                        <div class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $row['name'] }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-tighter flex items-center gap-1">
                                            {{ $row['position'] }} 
                                            <x-heroicon-m-magnifying-glass-circle class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity text-indigo-500 no-print"/>
                                        </div>
                                    </button>
                                </td>
                                <td class="p-4 text-center font-mono font-bold text-slate-600">
                                    {{ $row['qty'] }}
                                </td>
                                <td class="p-4 text-right font-bold text-slate-700">
                                    {{ number_format($row['total'], 0, '.', ' ') }} ₴
                                </td>
                                <td class="p-4 text-right text-emerald-600 font-medium">
                                    {{ number_format($row['paid'], 0, '.', ' ') }} ₴
                                </td>
                                <td class="p-4 text-right">
                                    @if($row['debt'] > 0)
                                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-sm font-bold print:bg-transparent print:p-0">
                                            {{ number_format($row['debt'], 0, '.', ' ') }} ₴
                                        </span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right print:hidden">
                                    @if($row['debt'] > 0)
                                        <x-filament::button 
                                            color="info" 
                                            size="sm"
                                            wire:click="payAllForEmployee({{ $row['id'] }})"
                                            wire:confirm="Отметить выплату {{ number_format($row['debt'], 0) }} ₴ для {{ $row['name'] }}?"
                                        >
                                            Оплатить всё
                                        </x-filament::button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-900 text-white print:bg-slate-100 print:text-black">
                        <tr class="text-right">
                            <td class="p-4 font-bold text-left">ИТОГО ПО ВЫБРАННЫМ:</td>
                            <td class="p-4 text-center font-bold">{{ $this->payrollData->sum('qty') }}</td>
                            <td class="p-4 font-bold text-indigo-300 print:text-black">{{ number_format($this->payrollData->sum('total'), 0, '.', ' ') }} ₴</td>
                            <td class="p-4 font-bold text-emerald-400 print:text-black">{{ number_format($this->payrollData->sum('paid'), 0, '.', ' ') }} ₴</td>
                            <td class="p-4 font-bold text-amber-400 print:text-black">{{ number_format($this->payrollData->sum('debt'), 0, '.', ' ') }} ₴</td>
                            <td class="print:hidden"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- 3. Модальное окно детализации сотрудника --}}
    <x-filament::modal id="employee-details-modal" width="5xl">
        <span autofocus tabindex="-1" style="position: absolute; top: 0;"></span>
        <x-slot name="header">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                    <x-heroicon-s-user class="w-6 h-6"/>
                </div>
                <div>
                    <h2 class="text-xl font-bold leading-tight">{{ $viewing_employee?->name }}</h2>
                    <p class="text-xs text-slate-500 uppercase tracking-widest">Лицевой счет за период: {{ $date_from }} — {{ $date_to }}</p>
                </div>
            </div>
        </x-slot>

        <div id="report-content-area-employee">
            <div class="hidden print:block text-center mb-6 border-b-2 border-black pb-4">
                <h1 class="text-2xl font-bold uppercase">Детализация работ: {{ $viewing_employee?->name }}</h1>
                <p>Период: {{ $date_from }} — {{ $date_to }}</p>
            </div>

            <div class="space-y-6">
                @forelse($employee_details as $date => $models)
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm print:shadow-none">
                        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex justify-between items-center print:bg-slate-100">
                            <span class="font-bold text-slate-700 italic">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
                            <span class="text-xs font-black text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100 print:text-black">
                                За день: {{ number_format(collect($models)->sum(fn($m) => $m['qty'] * $m['price']), 0, '.', ' ') }} ₴
                            </span>
                        </div>

                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] uppercase text-slate-400 border-b bg-white">
                                    <th class="p-3">Модель / Техкарта</th>
                                    <th class="p-3 text-center">Пар</th>
                                    <th class="p-3 text-right">Цена за пару</th>
                                    <th class="p-3 text-right">Итого</th>
                                    <th class="p-3 text-center print:hidden">Статус</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($models as $model)
                                    <tr class="text-sm">
                                        <td class="p-3 font-medium text-slate-800">{{ $model['model_name'] }}</td>
                                        <td class="p-3 text-center font-mono font-bold">{{ $model['qty'] }}</td>
                                        <td class="p-3 text-right text-slate-500">{{ number_format($model['price'], 2, '.', ' ') }} ₴</td>
                                        <td class="p-3 text-right font-bold text-slate-900">
                                            {{ number_format($model['qty'] * $model['price'], 0, '.', ' ') }} ₴
                                        </td>
                                        <td class="p-3 text-center print:hidden">
                                            <span class="text-[10px] font-bold uppercase {{ $model['is_paid'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                                {{ $model['is_paid'] ? 'Оплачено' : 'Долг' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400 italic">Нет данных за этот период</div>
                @endforelse
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-between items-center w-full">
                <x-filament::button color="gray" icon="heroicon-m-printer" x-on:click="window.printReport('report-content-area-employee')">
                    Печать
                </x-filament::button>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-[10px] text-slate-400 uppercase font-bold">Итого к выплате</p>
                        <p class="text-xl font-black text-slate-900">
                            {{ number_format(collect($employee_details)->flatten(1)->sum(fn($i) => $i['qty'] * $i['price']), 0, '.', ' ') }} ₴
                        </p>
                    </div>
                    <x-filament::button color="gray" x-on:click="close">Закрыть</x-filament::button>
                </div>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- 4. Модальное окно дополнительных работ --}}
    <x-filament::modal id="extra-works-modal" width="4xl">
        <span autofocus tabindex="-1" style="position: absolute; top: 0;"></span>
        <x-slot name="header">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                    <x-heroicon-s-wrench-screwdriver class="w-6 h-6"/>
                </div>
                <div>
                    <h2 class="text-xl font-bold leading-tight">Сводная ведомость доп. работ</h2>
                    <p class="text-xs text-slate-500 uppercase tracking-widest italic">Отчет за период: {{ $date_from }} — {{ $date_to }}</p>
                </div>
            </div>
        </x-slot>

        <div id="report-content-area-extra">
            <div class="hidden print:block text-center mb-6 border-b-2 border-black pb-4">
                <h1 class="text-2xl font-bold uppercase">Ведомость дополнительных работ</h1>
                <p>Период: {{ $date_from }} — {{ $date_to }}</p>
            </div>

            <div class="space-y-6">
                @forelse($extra_works_details as $date => $works)
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm bg-white print:shadow-none">
                        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex justify-between items-center print:bg-slate-100">
                            <span class="font-bold text-slate-700 italic">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
                            <span class="text-xs font-black text-amber-600 bg-white px-3 py-1 rounded-full border border-amber-200 print:text-black">
                                За день: {{ number_format(collect($works)->sum(fn($w) => $w['qty'] * $w['price']), 0, '.', ' ') }} ₴
                            </span>
                        </div>

                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] uppercase text-slate-400 border-b">
                                    <th class="p-3 pl-5">Наименование услуги / работы</th>
                                    <th class="p-3 text-center">Всего пар</th>
                                    <th class="p-3 text-right">Цена</th>
                                    <th class="p-3 text-right pr-5">Сумма</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($works as $work)
                                    <tr class="text-sm">
                                        <td class="p-3 pl-5 font-bold text-slate-800">{{ $work['work_name'] }}</td>
                                        <td class="p-3 text-center font-mono font-bold text-slate-600">{{ $work['qty'] }}</td>
                                        <td class="p-3 text-right text-slate-500">{{ number_format($work['price'], 2, '.', ' ') }} ₴</td>
                                        <td class="p-3 text-right font-black text-slate-900 pr-5">{{ number_format($work['qty'] * $work['price'], 0, '.', ' ') }} ₴</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400 italic">Работ не найдено</div>
                @endforelse
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-between items-center w-full">
                <x-filament::button color="gray" icon="heroicon-m-printer" x-on:click="window.printReport('report-content-area-extra')">
                    Печать
                </x-filament::button>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-[10px] text-slate-400 uppercase font-black">Общий итог</p>
                        <p class="text-2xl font-black text-slate-900 leading-none">
                            {{ number_format(collect($extra_works_details)->flatten(1)->sum(fn($w) => $w['qty'] * $w['price']), 0, '.', ' ') }} ₴
                        </p>
                    </div>
                    <x-filament::button color="gray" x-on:click="close">Закрыть</x-filament::button>
                </div>
            </div>
        </x-slot>
    </x-filament::modal>

</x-filament-panels::page>