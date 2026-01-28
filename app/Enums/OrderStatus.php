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

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Processing => 'warning', // Оранжевый
            self::Completed => 'success', // Зеленый
            self::Cancelled => 'danger',  // Красный
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::Processing => 'heroicon-m-arrow-path',
            self::Completed => 'heroicon-m-check-circle',
            self::Cancelled => 'heroicon-m-x-circle',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled]);
    }

    public function canBeProcessed(): bool
    {
        return $this === self::Pending;
    }
}
