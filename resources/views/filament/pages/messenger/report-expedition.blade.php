<div class="space-y-6">
    {{-- Виджеты статистики --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                <x-filament::icon icon="heroicon-m-truck" class="w-8 h-8 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Общий план отгрузки</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($grandTotal, 0, '.', ' ') }} <span class="text-lg font-medium text-gray-500">пар</span>
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                <x-filament::icon icon="heroicon-m-user-group" class="w-8 h-8 text-orange-600 dark:text-orange-400" />
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Активных заказов</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $reports->total() }} <span class="text-lg font-medium text-gray-500">клиентов</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Панель управления --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            {{-- Выбор даты --}}
            <div>
                <x-filament::input.wrapper label="Дата производства">
                    <x-filament::input type="date" wire:model.live="reportDate" />
                </x-filament::input.wrapper>
            </div>

            {{-- Выбор получателя (Сотрудника склада) --}}
            <div>
                <x-filament::input.wrapper label="Отправить сотруднику">
                    <x-filament::input.select wire:model.live="targetEmployeeId">
                        <option value="">Не выбрано</option>
                        @foreach(\App\Models\Employee::where('is_active', true)->get() as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            {{-- Поиск по клиенту (внутри нарядов) --}}
            <div class="md:col-span-1">
                <x-filament::input.wrapper label="Поиск клиента">
                    <x-filament::input type="text" wire:model.live.debounce.500ms="search" placeholder="Имя заказчика..." />
                </x-filament::input.wrapper>
            </div>

            {{-- Действия --}}
            <div class="flex gap-2">
                <x-filament::button 
                    wire:click="generateReports" 
                    icon="heroicon-m-cog-6-tooth" 
                    color="gray"
                    class="flex-1"
                >
                    Сформировать
                </x-filament::button>

                <x-filament::button 
                    wire:click="sendFiltered" 
                    icon="heroicon-m-paper-airplane" 
                    color="success"
                    class="flex-1"
                    :disabled="!$targetEmployeeId"
                >
                    Разослать
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- Список сформированных нарядов --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <table class="w-full text-left divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Заказчик / Объект</th>
                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">Статус</th>
                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 text-right">Управление</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($reports as $report)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                        <td class="px-4 py-3 font-medium">
                            {{ $report->reportable->name ?? 'Сотрудник #' . $report->reportable_id }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($report->sent_at)
                                <x-filament::badge color="success" icon="heroicon-m-check-badge">
                                    {{ $report->sent_at->format('H:i') }}
                                </x-badge>
                            @else
                                <x-filament::badge color="gray" icon="heroicon-m-document-text">Черновик</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end items-center gap-2">
                                <x-filament::icon-button 
                                    icon="heroicon-m-eye" 
                                    wire:click="openPreview({{ $report->id }})" 
                                />
                                <x-filament::button 
                                    size="sm" 
                                    wire:click="send({{ $report->id }})"
                                    outline
                                >
                                    Отправить
                                </x-filament::button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-12 text-center text-gray-400">
                            Нет данных. Выберите дату и нажмите «Сформировать».
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($reports->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    {{-- Окно предпросмотра --}}
    <x-filament::modal id="preview-modal" width="3xl">
        <x-slot name="heading">Состав наряда для экспедиции</x-slot>
        
        <div class="p-4 bg-slate-50 dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 shadow-inner">
            <div class="font-mono text-sm leading-relaxed whitespace-pre-wrap">
                {!! $previewContent !!}
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end">
                <x-filament::button color="gray" x-on:click="close">Закрыть</x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>