<?php

namespace App\Filament\Resources\ShoeSoles\Pages;

use App\Filament\Resources\ShoeSoles\ShoeSoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoeSoles extends ListRecords
{
    protected static string $resource = ShoeSoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
