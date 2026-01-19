<?php

namespace App\Observers;

use App\Models\MaterialMovement;

class MaterialMovementObserver
{
    /**
     * Handle the MaterialMovement "created" event.
     */
    public function created(MaterialMovement $movement): void
    {
        // Получаем объект, к которому привязано движение (Material или ShoeSole)
        $target = $movement->movable;

        if (!$target) return;

        if ($movement->type->isNegative()) {
            $target->decrement('stock_quantity', $movement->quantity);
        } else {
            $target->increment('stock_quantity', $movement->quantity);
        }
    }

    /**
     * Handle the MaterialMovement "updated" event.
     */
    public function updated(MaterialMovement $materialMovement): void
    {
        //
    }

    /**
     * Handle the MaterialMovement "deleted" event.
     */
    public function deleted(MaterialMovement $materialMovement): void
    {
        //
    }

    /**
     * Handle the MaterialMovement "restored" event.
     */
    public function restored(MaterialMovement $materialMovement): void
    {
        //
    }

    /**
     * Handle the MaterialMovement "force deleted" event.
     */
    public function forceDeleted(MaterialMovement $materialMovement): void
    {
        //
    }
}
