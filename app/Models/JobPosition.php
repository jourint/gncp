<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class JobPosition extends Model
{
    use Sushi;

    public $incrementing = false;
    protected $keyType = 'int';

    protected $rows = [
        ['id' => 0, 'value' => 'Не выбрано'],
        ['id' => 1, 'value' => 'Закройный цех'],
        ['id' => 2, 'value' => 'Швейный цех'],
        ['id' => 3, 'value' => 'Сапожний цех'],
    ];
}
