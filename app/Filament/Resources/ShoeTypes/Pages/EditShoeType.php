<?php

namespace App\Filament\Resources\ShoeTypes\Pages;

use App\Filament\Resources\ShoeTypes\ShoeTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoeType extends EditRecord
{
    protected static string $resource = ShoeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
