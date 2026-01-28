<?php

namespace App\Filament\Resources\TechCardMaterials\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Material;

class TechCardMaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('shoe_tech_card_id')
                    ->label('Техническая карта')
                    ->relationship('shoeTechCard', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('material_id')
                    ->label('Материал')
                    ->relationship(
                        name: 'material',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn($query) => $query->with(['color', 'materialType'])
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->fullName)
                    ->required()
                    ->searchable(['name'])
                    ->preload()
                    ->live(),

                TextInput::make('quantity')
                    ->label('Расход на 1 пару')
                    ->numeric()
                    ->step(0.5)
                    ->default(0.00)
                    ->suffix(function (callable $get) {
                        $materialId = $get('material_id');
                        if (!$materialId) return '';
                        $material = Material::with('materialType')->find($materialId);
                        return $material?->materialType?->unit_id?->getLabel() ?? 'ед.';
                    })
                    ->required(),
            ]);
    }
}
