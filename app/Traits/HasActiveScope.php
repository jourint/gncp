<?php

namespace App\Traits;

trait HasActiveScope
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
