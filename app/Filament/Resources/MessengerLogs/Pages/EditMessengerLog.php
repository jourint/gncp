<?php

namespace App\Filament\Resources\MessengerLogs\Pages;

use App\Filament\Resources\MessengerLogs\MessengerLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessengerLog extends EditRecord
{
    protected static string $resource = MessengerLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
