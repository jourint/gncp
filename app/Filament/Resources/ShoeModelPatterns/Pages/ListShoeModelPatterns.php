<?php

namespace App\Filament\Resources\ShoeModelPatterns\Pages;

use App\Filament\Resources\ShoeModelPatterns\ShoeModelPatternResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoeModelPatterns extends ListRecords
{
    protected static string $resource = ShoeModelPatternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
