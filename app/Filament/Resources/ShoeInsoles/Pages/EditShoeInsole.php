<?php

namespace App\Filament\Resources\ShoeInsoles\Pages;

use App\Filament\Resources\ShoeInsoles\ShoeInsoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoeInsole extends EditRecord
{
    protected static string $resource = ShoeInsoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
