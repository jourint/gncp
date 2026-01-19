<?php

namespace App\Filament\Resources\OrderPositions\Pages;

use App\Filament\Resources\OrderPositions\OrderPositionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderPositions extends ListRecords
{
    protected static string $resource = OrderPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
