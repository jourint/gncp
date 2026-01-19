<?php

namespace App\Observers;

use App\Models\ShoeModel;

class ShoeModelObserver
{
    /**
     * Handle the ShoeModel "created" event.
     */
    public function created(ShoeModel $shoeModel): void
    {
        //
    }

    /**
     * Handle the ShoeModel "updated" event.
     */
    public function updated(ShoeModel $shoeModel): void
    {
        // Проверяем, изменилось ли именно поле name
        if ($shoeModel->isDirty('name')) {
            // Получаем все техкарты этой модели
            $shoeModel->techCards()->each(function ($techCard) {
                $techCard->save();
            });
        }
    }

    /**
     * Handle the ShoeModel "deleted" event.
     */
    public function deleted(ShoeModel $shoeModel): void
    {
        //
    }

    /**
     * Handle the ShoeModel "restored" event.
     */
    public function restored(ShoeModel $shoeModel): void
    {
        //
    }

    /**
     * Handle the ShoeModel "force deleted" event.
     */
    public function forceDeleted(ShoeModel $shoeModel): void
    {
        //
    }
}
