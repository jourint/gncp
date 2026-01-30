<div class="space-y-4">
    <div class="flex flex-wrap gap-4 items-end bg-white p-4 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-48">
            <x-filament::input.wrapper label="Дата производства">
                <x-filament::input type="date" wire:model.live="reportDate" />
            </x-filament::input.wrapper>
        </div>
        
        <div class="flex-1 min-w-[200px]">
            <x-filament::input.wrapper label="Поиск заказчика">
                <x-filament::input type="text" wire:model.live.debounce.500ms="search" placeholder="Имя клиента..." />
            </x-filament::input.wrapper>
        </div>

        <div class="flex gap-2">
            <x-filament::button wire:click="generateReports" icon="heroicon-m-arrow-path" color="gray">
                Собрать отчёты
            </x-filament::button>

            <x-filament::button wire:click="sendAllPending" icon="heroicon-m-paper-airplane" color="success">
                Отправить все
            </x-filament::button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <table class="w-full text-left divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <th class="px-4 py-3 text-sm font-medium">Заказчик</th>
                    <th class="px-4 py-3 text-sm font-medium">Статус</th>
                    <th class="px-4 py-3 text-sm font-medium text-right">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($reports as $report)
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold">{{ $report->reportable->name }}</td>
                        <td class="px-4 py-3">
                            @if($report->sent_at)
                                <x-filament::badge color="success" icon="heroicon-m-check-badge">
                                    Отправлен в {{ $report->sent_at->format('H:i') }}
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="gray" icon="heroicon-m-clock">
                                    Черновик
                                </x-filament::badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <x-filament::icon-button 
                                    icon="heroicon-m-eye" 
                                    wire:click="openPreview({{ $report->id }})"
                                    tooltip="Предпросмотр"
                                />
                                <x-filament::link 
                                    wire:click="send({{ $report->id }})"
                                    class="cursor-pointer font-semibold"
                                >
                                    {{ $report->sent_at ? 'Повторить' : 'Отправить' }}
                                </x-filament::link>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-400">
                            Заказов на эту дату не найдено или отчеты еще не собраны.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($reports->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    <x-filament::modal id="preview-modal" width="3xl">
        <x-slot name="heading">Предпросмотр отчета для {{ $reportDate }}</x-slot>
        <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-900 dark:border-gray-700 font-mono text-sm whitespace-pre-wrap">
            {!! $previewContent !!}
        </div>
    </x-filament::modal>
</div>