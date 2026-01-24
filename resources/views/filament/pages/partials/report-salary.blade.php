<div class="space-y-4">
    <div class="flex justify-between items-end border-b-2 border-slate-900 pb-1">
        <h2 class="text-2xl font-black uppercase tracking-tighter">Ведомость начислений</h2>
        <div class="text-2xl font-black text-indigo-600 leading-none">
            {{ number_format($data->pluck('employees')->flatten(1)->sum('total_sum'), 0, '.', ' ') }} ₴
        </div>
    </div>

    @foreach($data as $pos)
        <div class="space-y-2">
            <div class="flex justify-between items-end border-b border-slate-300 pb-0.5">
                <h3 class="font-black uppercase text-slate-500 tracking-widest text-[11px]">{{ $pos['job_position_name'] }}</h3>
                <span class="text-slate-900 font-black text-lg leading-none">{{ number_format($pos['employees']->sum('total_sum'), 0) }} ₴</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($pos['employees'] as $emp)
                    <div class="border border-slate-300 rounded-lg p-3 bg-white shadow-sm page-break-inside-avoid">
                        <div class="font-black text-slate-900 border-b border-slate-100 pb-0.5 mb-1 uppercase text-[15px] truncate">
                            {{ $emp['name'] }}
                        </div>
                        <div class="space-y-0.5 mb-2">
                            @foreach($emp['works'] as $work)
                                <div class="flex justify-between text-[13px] leading-tight">
                                    <span class="text-slate-600 truncate pr-2">{{ $work['model_name'] }}</span>
                                    <span class="font-bold text-slate-800">{{ $work['qty'] }}п. × {{ (int)$work['price'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between items-center pt-1 border-t border-slate-100">
                            <span class="text-[10px] font-bold text-slate-400 uppercase leading-none">Итого:</span>
                            <span class="text-xl font-black text-slate-900 leading-none">
                                {{ number_format($emp['total_sum'], 0, '.', ' ') }} ₴
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>