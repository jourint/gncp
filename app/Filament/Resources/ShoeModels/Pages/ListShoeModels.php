<?php

namespace App\Filament\Resources\ShoeModels\Pages;

use App\Filament\Resources\ShoeModels\ShoeModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoeModels extends ListRecords
{
    protected static string $resource = ShoeModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
