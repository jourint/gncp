<div class="space-y-6">
    {{-- Панель управления --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <div>
                <x-filament::input.wrapper label="Дата планирования">
                    <x-filament::input type="date" wire:model.live="reportDate" />
                </x-filament::input.wrapper>
            </div>

            <div>
                <x-filament::input.wrapper label="Кладовщик (Получатель)">
                    <x-filament::input.select wire:model.live="targetEmployeeId">
                        <option value="">-- Выберите сотрудника --</option>
                        @foreach(\App\Models\Employee::where('is_active', true)->get() as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <x-filament::button wire:click="generateReports" icon="heroicon-m-beaker" color="primary">
                Рассчитать материалы
            </x-filament::button>
        </div>
    </div>

    {{-- Список отчетов --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <table class="w-full text-left divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <th class="px-4 py-3 text-xs font-bold uppercase">Получатель</th>
                    <th class="px-4 py-3 text-xs font-bold uppercase">Дата</th>
                    <th class="px-4 py-3 text-xs font-bold uppercase text-right">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($reports as $report)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-bold">{{ $report->reportable->name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $report->production_date->format('d.m.Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <x-filament::icon-button icon="heroicon-m-eye" wire:click="openPreview({{ $report->id }})" />
                                <x-filament::button size="sm" wire:click="send({{ $report->id }})">
                                    {{ $report->sent_at ? 'Повторить' : 'Отправить' }}
                                </x-filament::button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-10 text-center text-gray-400">Нет сформированных отчетов</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Предпросмотр --}}
    <x-filament::modal id="preview-modal" width="2xl">
        <x-slot name="heading">Список материалов для подготовки</x-slot>
        <div class="p-6 bg-slate-50 dark:bg-gray-950 rounded-xl font-mono text-sm leading-relaxed whitespace-pre-wrap">
            {!! $previewContent !!}
        </div>
        <x-slot name="footer">
            <div class="flex justify-end"><x-filament::button color="gray" x-on:click="close">Закрыть</x-filament::button></div>
        </x-slot>
    </x-filament::modal>
</div>