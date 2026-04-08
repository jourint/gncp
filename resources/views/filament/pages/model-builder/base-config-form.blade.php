<div class="bg-white dark:bg-gray-900 border dark:border-white/10 rounded-2xl p-6 shadow-sm space-y-6">
    <div class="space-y-2">
        <label class="text-[10px] font-black uppercase text-gray-400 italic tracking-widest">Описание модели</label>
        <textarea 
            wire:blur="updateBaseField('description', $event.target.value)"
            class="w-full text-xs bg-gray-50 dark:bg-white/5 border dark:border-white/10 rounded-xl p-3 outline-none focus:ring-1 ring-primary-500 transition min-h-[60px]"
        >{{ $model->description }}</textarea>
    </div>

    <div class="grid grid-cols-3 gap-4">
        @php
            $configFields = [
                ['label' => 'Стелька', 'field' => 'shoe_insole_id', 'options' => \App\Models\ShoeInsole::all()],
                ['label' => 'Задник', 'field' => 'counter_id', 'options' => \App\Models\Counter::all()],
                ['label' => 'Подносок', 'field' => 'puff_id', 'options' => \App\Models\Puff::all()],
            ];
        @endphp

        @foreach($configFields as $item)
            <div class="space-y-1.5">
                <label class="text-[9px] font-black uppercase text-gray-400 italic tracking-wider">{{ $item['label'] }}</label>
                <select 
                    wire:change="updateBaseField('{{ $item['field'] }}', $event.target.value)"
                    class="w-full text-[11px] font-bold uppercase bg-gray-50 dark:bg-white/5 border dark:border-white/10 rounded-lg p-2 outline-none focus:border-primary-500"
                >
                    <option value="">—</option>
                    @foreach($item['options'] as $opt)
                        <option value="{{ $opt->id }}" @selected($model->{$item['field']} == $opt->id)>
                            {{ $item['field'] === 'shoe_insole_id' ? $opt->fullName : $opt->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</div>