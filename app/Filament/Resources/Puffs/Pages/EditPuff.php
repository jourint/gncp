<?php

namespace App\Filament\Resources\Puffs\Pages;

use App\Filament\Resources\Puffs\PuffResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPuff extends EditRecord
{
    protected static string $resource = PuffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
