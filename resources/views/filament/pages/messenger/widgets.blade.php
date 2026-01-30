    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Виджет: Всего аккаунтов --}}
        <x-filament::section>
            <x-slot name="heading">Статистика подключений</x-slot>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Всего привязано</p>
                    <p class="text-2xl font-bold">{{ \App\Models\MessengerAccount::count() }}</p>
                </div>
                <div class="p-3 bg-primary-500/10 rounded-full">
                    <x-heroicon-o-users class="w-6 h-6 text-primary-500" />
                </div>
            </div>
        </x-filament::section>

        {{-- Виджет: Сообщения за сегодня --}}
        <x-filament::section>
            <x-slot name="heading">Активность за 24ч</x-slot>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Отправлено сегодня</p>
                    <p class="text-2xl font-bold">{{ \App\Models\MessengerLog::where('sent_at', '>=', now()->startOfDay())->count() }}</p>
                </div>
                <div class="p-3 bg-success-500/10 rounded-full">
                    <x-heroicon-o-paper-airplane class="w-6 h-6 text-success-500" />
                </div>
            </div>
        </x-filament::section>

        {{-- Виджет: Ошибки --}}
        <x-filament::section>
            <x-slot name="heading">Проблемы</x-slot>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Ошибки доставки</p>
                    <p class="text-2xl font-bold text-danger-600">
                        {{ \App\Models\MessengerLog::where('status', \App\Enums\MessengerStatus::Failed)->count() }}
                    </p>
                </div>
                <div class="p-3 bg-danger-500/10 rounded-full">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-danger-500" />
                </div>
            </div>
        </x-filament::section>
    </div>