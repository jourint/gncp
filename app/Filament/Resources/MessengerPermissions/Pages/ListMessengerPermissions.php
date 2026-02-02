<?php

namespace App\Filament\Resources\MessengerPermissions\Pages;

use App\Filament\Resources\MessengerPermissions\MessengerPermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessengerPermissions extends ListRecords
{
    protected static string $resource = MessengerPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
