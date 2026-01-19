<?php

namespace App\Enums;

enum MovementType: string
{
    case Income = 'income';
    case Outcome = 'outcome';
    case WriteOff = 'write-off';

    /**
     * Возвращает true, если операция должна уменьшать остаток.
     */
    public function isNegative(): bool
    {
        return match ($this) {
            self::Income => false,
            self::Outcome, self::WriteOff => true,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Приход',
            self::Outcome => 'Расход',
            self::WriteOff => 'Списание',
        };
    }
}
