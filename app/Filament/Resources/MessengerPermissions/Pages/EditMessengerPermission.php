<?php

namespace App\Filament\Resources\MessengerPermissions\Pages;

use App\Filament\Resources\MessengerPermissions\MessengerPermissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessengerPermission extends EditRecord
{
    protected static string $resource = MessengerPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
