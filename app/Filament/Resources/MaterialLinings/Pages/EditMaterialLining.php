<?php

namespace App\Filament\Resources\MaterialLinings\Pages;

use App\Filament\Resources\MaterialLinings\MaterialLiningResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMaterialLining extends EditRecord
{
    protected static string $resource = MaterialLiningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
