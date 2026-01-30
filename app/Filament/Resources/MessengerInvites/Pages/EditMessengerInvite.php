<?php

namespace App\Filament\Resources\MessengerInvites\Pages;

use App\Filament\Resources\MessengerInvites\MessengerInviteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessengerInvite extends EditRecord
{
    protected static string $resource = MessengerInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
