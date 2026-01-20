<?php

namespace App\Filament\Resources\TechCardMaterials\Pages;

use App\Filament\Resources\TechCardMaterials\TechCardMaterialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTechCardMaterials extends ListRecords
{
    protected static string $resource = TechCardMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
