<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InsolesType: string implements HasLabel
{
    case Inset = 'inset';
    case Fitting = 'fitting';
    case HalfInsole = 'half-insole';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Inset => 'Вкладная',
            self::Fitting => 'Обтяжная',
            self::HalfInsole => 'Полустелька',
        };
    }
}
