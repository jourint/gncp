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
        ['id' => 1, 'code' => 'cm', 'name' => 'сантиметр'],
        ['id' => 2, 'code' => 'pcs', 'name' => 'штука'],
        ['id' => 3, 'code' => 'dm2', 'name' => 'дециметр квадратный'],
        ['id' => 4, 'code' => 'm', 'name' => 'метр'],
        ['id' => 5, 'code' => 'kg', 'name' => 'килограмм'],
    ];
    // $unitOptions = Unit::all()->pluck('name', 'id');

}
