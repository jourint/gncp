<?php

namespace App\Filament\Resources\ShoeModels\Pages;

use App\Filament\Resources\ShoeModels\ShoeModelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoeModel extends EditRecord
{
    protected static string $resource = ShoeModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
