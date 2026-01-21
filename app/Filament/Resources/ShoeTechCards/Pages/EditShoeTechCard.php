<?php

namespace App\Filament\Resources\ShoeTechCards\Pages;

use App\Filament\Resources\ShoeTechCards\ShoeTechCardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoeTechCard extends EditRecord
{
    protected static string $resource = ShoeTechCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Если у техкарты есть привязанная модель, возвращаемся в неё
        if ($this->record->shoe_model_id) {
            return \App\Filament\Resources\ShoeModels\ShoeModelResource::getUrl('edit', [
                'record' => $this->record->shoe_model_id
            ]);
        }

        // Иначе идем в список техкарт (стандартное поведение)
        return $this->getResource()::getUrl('index');
    }
}
