<?php

namespace App\Filament\Resources\ShoeSoles\Pages;

use App\Filament\Resources\ShoeSoles\ShoeSoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoeSole extends EditRecord
{
    protected static string $resource = ShoeSoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
