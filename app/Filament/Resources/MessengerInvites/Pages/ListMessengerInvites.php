<?php

namespace App\Filament\Resources\MessengerInvites\Pages;

use App\Filament\Resources\MessengerInvites\MessengerInviteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessengerInvites extends ListRecords
{
    protected static string $resource = MessengerInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
