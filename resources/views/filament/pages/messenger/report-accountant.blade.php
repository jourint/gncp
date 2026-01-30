<div class="space-y-6">
    {{-- Верхняя панель управления --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            {{-- Дата отчета --}}
            <div>
                <x-filament::input.wrapper label="Дата финансового отчета">
                    <x-filament::input 
                        type="date" 
                        wire:model.live="reportDate" 
                    />
                </x-filament::input.wrapper>
            </div>

            {{-- Выбор Бухгалтера/Админа --}}
            <div>
                <x-filament::input.wrapper label="Кому отправить (Бухгалтер)">
                    <x-filament::input.select wire:model.live="targetEmployeeId">
                        <option value="">-- Выберите сотрудника --</option>
                        @foreach(\App\Models\Employee::where('is_active', true)->get() as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            {{-- Кнопка генерации --}}
            <x-filament::button 
                wire:click="generateReports" 
                icon="heroicon-m-chart-bar-square" 
                color="primary"
                size="lg"
            >
                Сформировать отчет
            </x-filament::button>
        </div>
    </div>

    {{-- Список сформированных отчетов --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 dark:bg-gray-900/50 dark:border-gray-700">
            <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">История финансовых сводок</h3>
        </div>

        <table class="w-full text-left divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50/50 dark:bg-gray-900/80">
                    <th class="px-4 py-3 text-xs font-bold text-gray-500">Получатель</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500">Дата отчета</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500">Статус</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 text-right">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($reports as $report)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-semibold">
                            {{ $report->reportable->name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $report->production_date->format('d.m.Y') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($report->sent_at)
                                <x-filament::badge color="success" icon="heroicon-m-check-badge">
                                    Отправлен {{ $report->sent_at->format('H:i') }}
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="warning" icon="heroicon-m-clock">
                                    Готов к отправке
                                </x-filament::badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <x-filament::icon-button 
                                    icon="heroicon-m-eye" 
                                    wire:click="openPreview({{ $report->id }})" 
                                    tooltip="Посмотреть цифры"
                                />
                                <x-filament::button 
                                    size="sm" 
                                    wire:click="send({{ $report->id }})"
                                    color="{{ $report->sent_at ? 'gray' : 'success' }}"
                                >
                                    {{ $report->sent_at ? 'Повторить' : 'Отправить в TG' }}
                                </x-filament::button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-gray-400">
                            Отчетов за выбранную дату еще не создавалось.
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

    {{-- Модалка предпросмотра финансового отчета --}}
    <x-filament::modal id="preview-modal" width="2xl">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-banknotes" class="w-6 h-6 text-success-500" />
                <span>Предпросмотр финансовой сводки</span>
            </div>
        </x-slot>

        <div class="p-6 bg-slate-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-inner">
            <div class="font-mono text-sm leading-relaxed whitespace-pre-wrap text-gray-800 dark:text-gray-100">
                {!! $previewContent !!}
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-filament::button color="gray" x-on:click="close">
                    Закрыть
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>