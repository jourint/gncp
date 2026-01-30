<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JobPosition: int implements HasLabel, HasColor
{
    case None = 0;
    case Cutting = 1;
    case Sewing = 2;
    case Shoemaker = 3;
    case Other = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::None => 'Не выбрано',
            self::Cutting => 'Закройный цех',
            self::Sewing => 'Швейный цех',
            self::Shoemaker => 'Сапожный цех',
            self::Other => 'Другое',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::None => 'gray',
            self::Cutting => 'info',    // Синий
            self::Sewing => 'warning',  // Желтый/Оранжевый
            self::Shoemaker => 'success', // Зеленый
            self::Other => 'secondary',
        };
    }

    // Новый метод: HEX-цвет специально для графиков
    public function getChartColor(): string
    {
        return match ($this) {
            self::Cutting => '#3b82f6',   // Blue
            self::Sewing => '#f59e0b',    // Amber
            self::Shoemaker => '#10b981', // Emerald
            self::Other => '#6b7280',     // Gray
            default => '#94a3b8',
        };
    }
}
