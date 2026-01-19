<?php

namespace App\Filament\Resources\MaterialMovements\Pages;

use App\Filament\Resources\MaterialMovements\MaterialMovementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMaterialMovement extends EditRecord
{
    protected static string $resource = MaterialMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
