<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel
{
    case Pending = 'pending'; // Ожидает обработки
    case Processing = 'processing'; // В процессе выполнения
    case Completed = 'completed'; // Выполнено
    case Cancelled = 'cancelled'; // Отменено

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Ожидает обработки',
            self::Processing => 'В процессе выполнения',
            self::Completed => 'Выполнено',
            self::Cancelled => 'Отменено',
        };
    }
}
