<x-filament-panels::page>
    {{-- 1. Верхняя панель настроек --}}
    <x-filament::section>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
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
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm uppercase">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/10 text-gray-400 text-[10px]">
                            <th class="py-2 px-1">Модель/ТК</th>
                            <th class="py-2 px-1 text-center">Разм.</th>
                            <th class="py-2 px-1 text-center">Кол-во</th>
                            <th class="py-2 px-1 text-right text-danger-600">X</th>
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
                                    <button wire:click="removeAssignment({{ $work->id }})" class="text-gray-300 hover:text-danger-600">
                                        <x-heroicon-m-trash class="w-4 h-4"/>
                                    </button>
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
    <x-filament::section title="Общая картина по цеху" icon="heroicon-m-chart-bar" class="mt-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/10 text-gray-400 text-[10px] uppercase font-black">
                        <th class="py-3 px-4">Сотрудник</th>
                        <th class="py-3 px-4">Модели (пары)</th>
                        <th class="py-3 px-4 text-right">Всего</th>
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

    {{-- 4. Секция Магического деления --}}
    <div class="mt-6 flex justify-center">
        <x-filament::button 
            wire:click="autoDistribute" 
            color="warning" 
            size="xl"
            icon="heroicon-m-sparkles"
            class="w-full max-w-md shadow-xl"
            wire:confirm="Это действие автоматически распределит все ОСТАВШИЕСЯ пары поровну между всеми активными сотрудниками цеха. Продолжить?"
        >
            РАСПРЕДЕЛИТЬ ОСТАТКИ АВТОМАТИЧЕСКИ
        </x-filament::button>
    </div>

</x-filament-panels::page>