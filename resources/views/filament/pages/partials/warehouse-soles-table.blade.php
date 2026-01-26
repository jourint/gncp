<div class="bg-white dark:bg-gray-900 rounded-xl border border-blue-200 dark:border-blue-800 overflow-hidden shadow-sm">
    <div class="px-6 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
        <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-100">Потребность подошв ({{ \Carbon\Carbon::createFromFormat('Y-m-d', $this->selected_date)->format('d.m.Y') }})</h3>
    </div>
    
    <div class="space-y-3 p-6 text-sm">
        @forelse(collect($this->sole_analysis)->groupBy('sole_id') as $soleId => $soleRows)
            <div>
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $soleRows->first()['name'] }}:</span>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($soleRows as $row)
                        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border {{ $row['diff'] < 0 ? 'border-danger-300 dark:border-danger-700 bg-danger-50 dark:bg-danger-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}">
                            <div class="text-xl font-bold font-mono text-gray-900 dark:text-gray-100 min-w-12 text-center">
                                {{ $row['size'] }}
                            </div>
                            <div class="border-l border-gray-200 dark:border-gray-700 pl-2">
                                <div class="text-xs {{ $row['diff'] < 0 ? 'text-danger-700 dark:text-danger-300 font-semibold' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ number_format($row['needed'], 0) }}/{{ number_format($row['stock'], 0) }}
                                </div>
                                <div class="text-xs font-bold {{ $row['diff'] < 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                                    @if($row['diff'] < 0)
                                        -{{ number_format(abs($row['diff']), 0) }}
                                    @else
                                        ✓
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 dark:text-gray-400 italic py-4">
                На выбранную дату потребностей подошв не найдено
            </div>
        @endforelse
    </div>
</div>
