<?php

namespace App\Filament\Resources\ShoeModelPatterns\Pages;

use App\Filament\Resources\ShoeModelPatterns\ShoeModelPatternResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoeModelPattern extends EditRecord
{
    protected static string $resource = ShoeModelPatternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
