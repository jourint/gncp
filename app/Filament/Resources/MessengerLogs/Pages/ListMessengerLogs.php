<?php

namespace App\Filament\Resources\MessengerLogs\Pages;

use App\Filament\Resources\MessengerLogs\MessengerLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessengerLogs extends ListRecords
{
    protected static string $resource = MessengerLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
