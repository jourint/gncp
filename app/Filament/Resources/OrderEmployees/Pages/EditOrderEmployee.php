<?php

namespace App\Filament\Resources\OrderEmployees\Pages;

use App\Filament\Resources\OrderEmployees\OrderEmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderEmployee extends EditRecord
{
    protected static string $resource = OrderEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
