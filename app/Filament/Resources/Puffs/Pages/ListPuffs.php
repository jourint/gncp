<?php

namespace App\Filament\Resources\Puffs\Pages;

use App\Filament\Resources\Puffs\PuffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPuffs extends ListRecords
{
    protected static string $resource = PuffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
