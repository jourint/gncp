<?php

namespace App\Filament\Pages\Messenger\Modules;

use Livewire\Component;

abstract class MessengerModule extends Component
{
    abstract public static function getTitle(): string;
    abstract public static function getIcon(): string;
}
