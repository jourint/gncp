<?php

namespace App\Filament\Resources\MaterialTypes\Pages;

use App\Filament\Resources\MaterialTypes\MaterialTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaterialTypes extends ListRecords
{
    protected static string $resource = MaterialTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
