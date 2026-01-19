<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Puff extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];

    public function shoeModels(): BelongsToMany
    {
        return $this->belongsToMany(ShoeModel::class);
    }
}
