<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Size extends Model
{
    use Sushi;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $rows = [
        ['id' => 36, 'name' => '36'],
        ['id' => 37, 'name' => '37'],
        ['id' => 38, 'name' => '38'],
        ['id' => 39, 'name' => '39'],
        ['id' => 40, 'name' => '40'],
        ['id' => 41, 'name' => '41'],
        ['id' => 42, 'name' => '42'],
        ['id' => 43, 'name' => '43'],
    ];
}
