<?php

namespace App\Filament\Resources\MessengerReports\Pages;

use App\Filament\Resources\MessengerReports\MessengerReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessengerReport extends EditRecord
{
    protected static string $resource = MessengerReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
