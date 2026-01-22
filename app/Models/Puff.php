<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\hasMany;

class Puff extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'description' => 'string',
        ];
    }

    public function shoeModels(): hasMany
    {
        return $this->hasMany(ShoeModel::class);
    }
}
