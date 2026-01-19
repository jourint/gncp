<?php

namespace App\Filament\Resources\ShoeTypes\Pages;

use App\Filament\Resources\ShoeTypes\ShoeTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoeTypes extends ListRecords
{
    protected static string $resource = ShoeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
