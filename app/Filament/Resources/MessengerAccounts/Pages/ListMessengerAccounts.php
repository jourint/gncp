<?php

namespace App\Filament\Resources\MessengerAccounts\Pages;

use App\Filament\Resources\MessengerAccounts\MessengerAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessengerAccounts extends ListRecords
{
    protected static string $resource = MessengerAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
