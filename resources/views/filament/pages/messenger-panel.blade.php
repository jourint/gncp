<x-filament-panels::page>
    @include('filament.pages.messenger.widgets')
    {{-- Tabs --}}
    <x-filament::tabs label="Модули">
        @foreach($this->getModules() as $key => $class)
            <x-filament::tabs.item 
                :active="$activeModule === $key"
                :icon="$class::getIcon()" 
                wire:click="$set('activeModule', '{{ $key }}')"
            >
                {{ $class::getTitle() }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    <div class="mt-4">
        {{-- Передаем полное имя класса. key() важен для сброса стейта при смене модуля --}}
        @livewire($this->getModules()[$activeModule], [], key($activeModule))
    </div>
</x-filament-panels::page>