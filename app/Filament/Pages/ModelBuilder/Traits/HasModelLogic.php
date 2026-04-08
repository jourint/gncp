<?php

namespace App\Filament\Pages\ModelBuilder\Traits;

use App\Models\ShoeModel;
use Filament\Notifications\Notification;

trait HasModelLogic
{
    public function loadModel(int $id): void
    {
        $model = ShoeModel::find($id);
        if ($model) {
            $this->activeModelId = $model->id;
            $this->state = [
                'name' => $model->name,
                'shoe_type_id' => $model->shoe_type_id,
                'price_coeff_cutting' => $model->price_coeff_cutting ?? 1.0,
                'price_coeff_sewing' => $model->price_coeff_sewing ?? 1.0,
                'price_coeff_shoemaker' => $model->price_coeff_shoemaker ?? 1.0,
                'available_sizes' => collect($model->available_sizes ?? [])->map(fn($v) => (string)$v)->toArray(),
                'workflows' => collect($model->workflows ?? [])->map(fn($v) => (string)$v)->toArray(),
                'is_active' => (bool)$model->is_active,
            ];
            $this->selectedTechCardId = null;
            $this->selectedTechCardData = [];
        }
    }

    public function saveModel(): void
    {
        ShoeModel::findOrFail($this->activeModelId)->update($this->state);
        Notification::make()->title('Данные модели обновлены')->success()->send();
    }
}
