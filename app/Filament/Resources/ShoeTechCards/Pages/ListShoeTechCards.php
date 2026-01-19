<?php

namespace App\Filament\Resources\ShoeTechCards\Pages;

use App\Filament\Resources\ShoeTechCards\ShoeTechCardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoeTechCards extends ListRecords
{
    protected static string $resource = ShoeTechCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
