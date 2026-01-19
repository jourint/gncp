<?php

namespace App\Filament\Resources\MaterialTypes\Pages;

use App\Filament\Resources\MaterialTypes\MaterialTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMaterialType extends EditRecord
{
    protected static string $resource = MaterialTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
