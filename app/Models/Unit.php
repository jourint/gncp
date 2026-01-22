<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Unit extends Model
{
    use Sushi;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $rows = [
        ['id' => 0, 'name' => 'отсутствует', 'label' => 'нет'],
        ['id' => 1, 'name' => 'сантиметр', 'label' => 'см.'],
        ['id' => 2, 'name' => 'штука', 'label' => 'шт.'],
        ['id' => 3, 'name' => 'дециметр квадратный', 'label' => 'дцм.2'],
        ['id' => 4, 'name' => 'метр', 'label' => 'м.'],
        ['id' => 5, 'name' => 'пара', 'label' => 'пара'],
        ['id' => 6, 'name' => 'миллилитр', 'label' => 'мл.'],
    ];
}
