<div class="space-y-4">
    <div class="flex flex-wrap gap-4 items-end bg-white p-4 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-48">
            <x-filament::input.wrapper label="Дата выработки">
                <x-filament::input type="date" wire:model.live="reportDate" />
            </x-filament::input.wrapper>
        </div>

        <div class="w-64">
            <x-filament::input.wrapper label="Цех / Должность">
                <x-filament::input.select wire:model.live="selectedPosition">
                    @foreach($positions as $pos)
                        <option value="{{ $pos->value }}">{{ $pos->getLabel() }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
        
        <div class="flex-1 min-w-[200px]">
            <x-filament::input.wrapper label="Поиск сотрудника">
                <x-filament::input type="text" wire:model.live.debounce.500ms="search" placeholder="ФИО..." />
            </x-filament::input.wrapper>
        </div>

        <div class="flex gap-2">
            <x-filament::button wire:click="generateReports" icon="heroicon-m-calculator" color="gray">
                Рассчитать
            </x-filament::button>
            <x-filament::button wire:click="sendFiltered" icon="heroicon-m-paper-airplane" color="success">
                Отправить всем
            </x-filament::button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <table class="w-full text-left divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <th class="px-4 py-3 text-sm font-medium">Сотрудник</th>
                    <th class="px-4 py-3 text-sm font-medium">Цех</th>
                    <th class="px-4 py-3 text-sm font-medium">Статус</th>
                    <th class="px-4 py-3 text-sm font-medium text-right">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($reports as $report)
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold">{{ $report->reportable->name }}</td>
                        <td class="px-4 py-3 text-sm">
                            {{ $report->reportable->job_position_id?->getLabel() ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($report->sent_at)
                                <x-filament::badge color="success">Отправлен {{ $report->sent_at->format('H:i') }}</x-filament::badge>
                            @else
                                <x-filament::badge color="gray">Ожидает</x-filament::badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <x-filament::icon-button icon="heroicon-m-eye" wire:click="openPreview({{ $report->id }})" />
                                <x-filament::button size="sm" wire:click="send({{ $report->id }})">
                                    Отправить
                                </x-filament::button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">Данных нет</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $reports->links() }}</div>
    </div>

    <x-filament::modal id="preview-modal" width="2xl">
        <x-slot name="heading">Детализация выработки</x-slot>
        <div class="p-4 bg-gray-50 rounded dark:bg-gray-900 font-mono text-sm whitespace-pre-wrap">{!! $previewContent !!}</div>
    </x-filament::modal>
</div>