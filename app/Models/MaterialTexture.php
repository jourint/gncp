<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class MaterialTexture extends Model
{
    use Sushi;

    public $incrementing = false;
    protected $keyType = 'int';

    protected $rows = [
        ['id' => 0, 'name' => 'нет', 'slug' => 'none'],
        ['id' => 1, 'name' => 'Кожа', 'slug' => 'leather'],
        ['id' => 2, 'name' => 'Замша', 'slug' => 'suede'],
        ['id' => 3, 'name' => 'Флотар', 'slug' => 'flotar'],
        ['id' => 4, 'name' => 'Одежка', 'slug' => 'apparel_leather'],
        ['id' => 5, 'name' => 'Лак', 'slug' => 'patent'],
    ];
}
