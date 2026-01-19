<?php

namespace App\Filament\Resources\ShoeSoleItems\Pages;

use App\Filament\Resources\ShoeSoleItems\ShoeSoleItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoeSoleItem extends EditRecord
{
    protected static string $resource = ShoeSoleItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
