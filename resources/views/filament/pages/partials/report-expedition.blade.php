<div class="space-y-1 print:text-black">
    <div class="flex justify-between items-center border-b-2 border-rose-600 pb-1">
        <h2 class="text-xl font-black uppercase italic text-rose-700 leading-none">
            План отгрузки на {{ \Carbon\Carbon::parse($selected_date)->addDays(2)->format('d.m.Y') }}
        </h2>
        <div class="text-xl font-black leading-none uppercase">
            ИТОГО: {{ (int)collect($data)->where('type', 'overall_total')->first()['total_quantity'] }} пар
        </div>
    </div>

    @foreach($data as $row)
        @if($row['type'] === 'customer_header')
            @php 
                $cTotal = collect($data)->where('type', 'customer_footer')->where('customer_name', $row['customer_name'])->first()['total_quantity'] ?? 0;
            @endphp
            <div class="mt-2 mb-1 flex justify-between items-center bg-slate-50 border-y border-slate-300 px-3 py-0.5">
                <h3 class="text-md font-black uppercase text-slate-900">{{ $row['customer_name'] }}</h3>
                <span class="font-bold text-slate-700 text-sm italic">Отгрузить: {{ (int)$cTotal }} пар</span>
            </div>
        @elseif($row['type'] === 'model_row')
            <div class="ml-4 py-1 border-b border-slate-100 last:border-0">
                <div class="text-[12px] font-extrabold text-slate-800 leading-tight mb-1 uppercase">
                    @php 
                        $parts = explode(' / ', $row['full_model_name']);
                        if(count($parts) > 1) array_shift($parts); 
                        $cleanName = implode(' / ', $parts);
                    @endphp
                    {{ $cleanName }}
                </div>
                
                <div class="flex flex-wrap gap-x-4 gap-y-1">
                    @foreach($row['sizes'] as $s)
                        <div class="flex items-baseline gap-1 bg-white px-1 border-b border-slate-300">
                            {{-- РАЗМЕР И КОЛИЧЕСТВО --}}
                            <span class="text-sm font-bold text-slate-400">{{ $s->size_id }}:</span>
                            <span class="text-xl font-black text-slate-900">{{ (int)$s->qty_sum }}</span>
                        </div>
                    @endforeach
                    <div class="ml-auto font-bold text-slate-400 text-xs self-center">
                        {{ (int)$row['total_quantity'] }} пар
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- СИГНАЛ ЗАВЕРШЕНИЯ ДОКУМЕНТА --}}
    <div class="mt-4 pt-1 border-t-4 border-double border-slate-400 flex justify-between items-center">
        <span class="text-[9px] font-bold uppercase text-slate-400 tracking-tighter italic">*** Конец отчета экспедиции / Документ сформирован автоматически ***</span>
        <span class="text-[9px] font-bold text-slate-400">{{ now()->format('H:i d.m.Y') }}</span>
    </div>
</div>