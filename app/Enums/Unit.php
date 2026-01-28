<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Unit: int implements HasLabel
{
    case None = 0;
    case Centimeter = 1;
    case Piece = 2;
    case SquareDecimeter = 3;
    case Meter = 4;
    case Pair = 5;
    case Milliliter = 6;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::None => 'нет',
            self::Centimeter => 'см.',
            self::Piece => 'шт.',
            self::SquareDecimeter => 'дцм.2',
            self::Meter => 'м.',
            self::Pair => 'пара',
            self::Milliliter => 'мл.',
        };
    }

    public function getFullName(): string
    {
        return match ($this) {
            self::None => 'отсутствует',
            self::Centimeter => 'сантиметр',
            self::Piece => 'штука',
            self::SquareDecimeter => 'дециметр квадратный',
            self::Meter => 'метр',
            self::Pair => 'пара',
            self::Milliliter => 'миллилитр',
        };
    }
}
