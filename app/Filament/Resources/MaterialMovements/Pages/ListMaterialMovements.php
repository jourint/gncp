<?php

namespace App\Filament\Resources\MaterialMovements\Pages;

use App\Filament\Resources\MaterialMovements\MaterialMovementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaterialMovements extends ListRecords
{
    protected static string $resource = MaterialMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
