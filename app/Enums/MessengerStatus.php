<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MessengerStatus: string implements HasLabel, HasColor
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'В очереди',
            self::Sent => 'Отправлено',
            self::Failed => 'Ошибка',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Sent => 'success',
            self::Failed => 'danger',
        };
    }
}
