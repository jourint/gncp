<?php

namespace App\Filament\Resources\OrderPositions\Pages;

use App\Filament\Resources\OrderPositions\OrderPositionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderPosition extends CreateRecord
{
    protected static string $resource = OrderPositionResource::class;
}
