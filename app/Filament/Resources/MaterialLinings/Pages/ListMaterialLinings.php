<?php

namespace App\Filament\Resources\MaterialLinings\Pages;

use App\Filament\Resources\MaterialLinings\MaterialLiningResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaterialLinings extends ListRecords
{
    protected static string $resource = MaterialLiningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
