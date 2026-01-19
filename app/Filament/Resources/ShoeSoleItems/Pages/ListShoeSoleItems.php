<?php

namespace App\Filament\Resources\ShoeSoleItems\Pages;

use App\Filament\Resources\ShoeSoleItems\ShoeSoleItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoeSoleItems extends ListRecords
{
    protected static string $resource = ShoeSoleItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
