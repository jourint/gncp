<div 
    x-data="{ tab: 'employees' }" 
    class="space-y-4" 
    x-on:copy-to-clipboard.window="window.navigator.clipboard.writeText($event.detail.text);"
>
    {{-- Панель управления: Переключатель и Поиск --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
        <div class="flex p-1 bg-gray-100 dark:bg-white/5 rounded-lg w-fit shadow-inner">
            <button 
                type="button"
                @click="tab = 'employees'" 
                :class="tab === 'employees' ? 'bg-white dark:bg-gray-800 shadow text-primary-600' : 'text-gray-500 opacity-60'" 
                class="px-5 py-2 text-[11px] font-black rounded-md transition-all uppercase tracking-wider outline-none"
            >
                Сотрудники
            </button>
            <button 
                type="button"
                @click="tab = 'customers'" 
                :class="tab === 'customers' ? 'bg-white dark:bg-gray-800 shadow text-primary-600' : 'text-gray-500 opacity-60'" 
                class="px-5 py-2 text-[11px] font-black rounded-md transition-all uppercase tracking-wider outline-none"
            >
                Заказчики
            </button>
        </div>

        <div class="relative">
            <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                <x-filament::input 
                    wire:model.live.debounce.400ms="search" 
                    type="text" 
                    placeholder="Поиск по имени..."
                    class="md:w-80"
                />
            </x-filament::input.wrapper>
        </div>
    </div>

    {{-- Таблица управления --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden text-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-800">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Субъект</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Статус подключений</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Управление</th>
                </tr>
            </thead>
            
            @foreach(['employees' => $employees, 'customers' => $customers] as $key => $collection)
                <tbody 
                    x-show="tab === '{{ $key }}'" 
                    x-cloak 
                    wire:key="messenger-table-{{ $key }}"
                    class="divide-y divide-gray-100 dark:divide-gray-800 font-medium"
                >
                    @forelse($collection as $user)
                        <tr wire:key="user-row-{{ $user->id }}" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                            
                            {{-- 1. Данные пользователя --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div @class([
                                        'w-9 h-9 rounded-full flex items-center justify-center font-black text-xs border border-current/20 shadow-inner',
                                        'bg-primary-100 text-primary-600' => $key === 'employees',
                                        'bg-amber-100 text-amber-600' => $key === 'customers',
                                    ])>
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white leading-tight">{{ $user->name }}</span>
                                        <span class="text-[10px] text-gray-400 mt-0.5">{{ format_phone($user->phone) ?? 'нет телефона' }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- 2. Статусы: Аккаунты и Инвайты --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-wrap justify-center gap-2">
                                    @foreach($user->messengerAccounts as $account)
                                        <div wire:key="acc-{{ $account->id }}" class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-success-50 dark:bg-success-500/10 text-success-700 dark:text-success-400 text-[9px] font-black border border-success-200 dark:border-success-500/20 shadow-sm uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success-500 animate-pulse"></span>
                                            {{ $account->driver }} 
                                            <button 
                                                wire:click="removeAccount({{ $account->id }})" 
                                                wire:confirm="Разорвать связь с устройством?" 
                                                class="ml-1 p-0.5 hover:bg-danger-100 dark:hover:bg-danger-500/20 hover:text-danger-600 rounded transition-colors"
                                            >
                                                <x-heroicon-m-x-mark class="w-3 h-3" />
                                            </button>
                                        </div>
                                    @endforeach

                                    @foreach($user->messengerInvites as $invite)
                                        <button 
                                            wire:key="inv-{{ $invite->id }}"
                                            wire:click="copyExistingInvite({{ $invite->id }})"
                                            class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 text-[9px] font-black border border-amber-200 dark:border-amber-500/20 shadow-sm uppercase group hover:bg-amber-100 dark:hover:bg-amber-500/20 transition-all"
                                        >
                                            <x-heroicon-m-arrow-path class="w-3 h-3 group-hover:rotate-180 transition-transform duration-500" />
                                            {{ $invite->driver }} ({{ $invite->expires_at->diffForHumans(null, true) }})
                                        </button>
                                    @endforeach

                                    @if($user->messengerAccounts->isEmpty() && $user->messengerInvites->isEmpty())
                                        <span class="text-[9px] text-gray-400 opacity-50 italic">Нет активных связей</span>
                                    @endif
                                </div>
                            </td>

                            {{-- 3. Управление (Dropdown) --}}
                            <td class="px-6 py-4">
                                <div class="flex justify-end">
                                    <x-filament::dropdown placement="bottom-end">
                                        <x-slot name="trigger">
                                            <x-filament::button size="xs" color="gray" outlined icon="heroicon-m-link">
                                                Создать инвайт
                                            </x-filament::button>
                                        </x-slot>

                                        <x-filament::dropdown.list>
                                            @foreach($drivers as $driver)
                                                @php $isLinked = $user->messengerAccounts->contains('driver', $driver->value); @endphp
                                                <x-filament::dropdown.list.item 
                                                    wire:click="generateInviteLink({{ $user->id }}, '{{ str($key)->singular() }}', '{{ $driver->value }}')"
                                                    :disabled="$isLinked"
                                                    :icon="$isLinked ? 'heroicon-m-check-circle' : 'heroicon-m-plus-circle'"
                                                    :color="$isLinked ? 'success' : 'gray'"
                                                >
                                                    {{ ucfirst($driver->value) }}
                                                </x-filament::dropdown.list.item>
                                            @endforeach
                                        </x-filament::dropdown.list>
                                    </x-filament::dropdown>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-400 text-xs font-black uppercase tracking-[0.2em] opacity-30">
                                Данные не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            @endforeach
        </table>
    </div>

    {{-- Пагинация --}}
    <div class="mt-4">
        <div x-show="tab === 'employees'">{{ $employees->links() }}</div>
        <div x-show="tab === 'customers'" x-cloak>{{ $customers->links() }}</div>
    </div>
</div>