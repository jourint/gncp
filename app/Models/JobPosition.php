<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class JobPosition extends Model
{
    use Sushi;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';

    public const CUTTING = 1;
    public const SEWING = 2;
    public const SHOEMAKER = 3;

    protected $rows = [
        ['id' => 0, 'value' => 'Не выбрано'],
        ['id' => self::CUTTING, 'value' => 'Закройный цех'],
        ['id' => self::SEWING, 'value' => 'Швейный цех'],
        ['id' => self::SHOEMAKER, 'value' => 'Сапожний цех'],
    ];
}
