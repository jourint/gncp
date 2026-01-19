<?php

namespace App\Filament\Resources\OrderEmployees\Pages;

use App\Filament\Resources\OrderEmployees\OrderEmployeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderEmployee extends CreateRecord
{
    protected static string $resource = OrderEmployeeResource::class;
}
