<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Color extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'hex'];

    public function shoeTechCards(): HasMany
    {
        return $this->hasMany(ShoeTechCard::class);
    }
    public function shoeModels(): HasMany
    {
        return $this->hasMany(ShoeModel::class);
    }

    public function shoeSoles(): HasMany
    {
        return $this->hasMany(ShoeSole::class);
    }

    public function shoeInsoles(): HasMany
    {
        return $this->hasMany(ShoeInsole::class);
    }
}
