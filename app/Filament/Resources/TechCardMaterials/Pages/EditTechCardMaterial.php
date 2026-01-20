<?php

namespace App\Filament\Resources\TechCardMaterials\Pages;

use App\Filament\Resources\TechCardMaterials\TechCardMaterialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTechCardMaterial extends EditRecord
{
    protected static string $resource = TechCardMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
