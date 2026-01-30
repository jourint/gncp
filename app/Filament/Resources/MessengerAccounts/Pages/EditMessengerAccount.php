<?php

namespace App\Filament\Resources\MessengerAccounts\Pages;

use App\Filament\Resources\MessengerAccounts\MessengerAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessengerAccount extends EditRecord
{
    protected static string $resource = MessengerAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
