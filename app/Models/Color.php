<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Color extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'hex'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'hex' => 'string',
        ];
    }

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

    public function materialLinings(): HasMany
    {
        return $this->hasMany(MaterialLining::class);
    }
}
