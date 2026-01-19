<?php

namespace App\Filament\Resources\ShoeInsoles\Pages;

use App\Filament\Resources\ShoeInsoles\ShoeInsoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoeInsoles extends ListRecords
{
    protected static string $resource = ShoeInsoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
