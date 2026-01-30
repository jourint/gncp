<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MessengerDriver: string implements HasLabel, HasColor
{
    case Telegram = 'telegram';
    //    case Viber = 'viber';
    //    case WhatsApp = 'whatsapp';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Telegram => 'Telegram',
            //    self::Viber => 'Viber',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Telegram => 'info',
            //    self::Viber => 'primary',
        };
    }

    public function getDriverClass(): string
    {
        return match ($this) {
            self::Telegram => \App\Services\Messenger\Drivers\TelegramDriver::class,
            //    self::Viber => \App\Services\Messenger\Drivers\ViberDriver::class,
        };
    }
}
