<?php

namespace App\Filament\Resources\MessengerReports\Pages;

use App\Filament\Resources\MessengerReports\MessengerReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessengerReports extends ListRecords
{
    protected static string $resource = MessengerReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
