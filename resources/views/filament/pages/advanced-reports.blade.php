<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex flex-wrap items-center justify-between gap-4 no-print">
            <div class="flex items-center gap-4">
                <div class="bg-indigo-50 p-3 rounded-xl">
                    <x-heroicon-o-calendar class="w-6 h-6 text-indigo-600" />
                </div>
                <div class="flex flex-row items-center gap-3">
                    <span class="text-sm font-semibold text-slate-600 uppercase tracking-wider">Дата заказа:</span>
                    <input type="date" wire:model.live="selected_date" 
                        class="border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium text-slate-700">
                </div>
            </div>

            @if($active_report)
            <div class="flex gap-2">
                <button x-on:click="window.printReport('report-content-area')" class="flex items-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-all text-sm font-medium shadow-sm">
                    <x-heroicon-s-printer class="w-4 h-4" /> Печать
                </button>
                <button wire:click="exportToPdf" class="flex items-center gap-2 px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-all text-sm font-medium shadow-sm">
                    <x-heroicon-s-document-arrow-down class="w-4 h-4" /> PDF
                </button>
                <button wire:click="exportToExcel('{{ $active_report }}')" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all text-sm font-medium shadow-sm">
                    <x-heroicon-s-document-text class="w-4 h-4" /> Excel
                </button>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3 no-print">
            @foreach([
                'cutting' => ['Крой', 'bg-blue-500'],
                'sewing' => ['Пошив', 'bg-emerald-500'],
                'shoemaker' => ['Сапожник', 'bg-purple-500'],
                'salary' => ['Зарплата', 'bg-amber-500'],
                'expedition' => ['Экспедиция', 'bg-rose-500'],
                'stock_requirements' => ['Склад', 'bg-orange-500'],
                'miscellaneous' => ['Разное', 'bg-slate-500'],
            ] as $key => $info)
                <button wire:click="showReport('{{ $key }}')" 
                    class="group relative overflow-hidden p-3 rounded-xl border-b-4 transition-all duration-200 {{ $active_report === $key ? 'bg-white border-'.$info[1].' translate-y-1 shadow-inner' : 'bg-white border-slate-200 hover:border-slate-300 shadow-sm hover:-translate-y-0.5' }}">
                    <span class="block text-xs font-black uppercase {{ $active_report === $key ? 'text-slate-800' : 'text-slate-500' }}">{{ $info[0] }}</span>
                </button>
            @endforeach
        </div>

        @if($active_report)
            <div id="report-content-area" class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden min-h-[500px]">
                <div class="p-8">
                    @include("filament.pages.partials.report-{$active_report}", ['data' => $this->report_data])
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>