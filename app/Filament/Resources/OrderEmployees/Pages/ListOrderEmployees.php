<?php

namespace App\Filament\Resources\OrderEmployees\Pages;

use App\Filament\Resources\OrderEmployees\OrderEmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderEmployees extends ListRecords
{
    protected static string $resource = OrderEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
