<?php

namespace App\Filament\Resources\ShoeTechCards\Pages;

use App\Filament\Resources\ShoeTechCards\ShoeTechCardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoeTechCard extends EditRecord
{
    protected static string $resource = ShoeTechCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
