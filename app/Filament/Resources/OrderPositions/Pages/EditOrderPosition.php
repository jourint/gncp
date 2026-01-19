<?php

namespace App\Filament\Resources\OrderPositions\Pages;

use App\Filament\Resources\OrderPositions\OrderPositionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderPosition extends EditRecord
{
    protected static string $resource = OrderPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
