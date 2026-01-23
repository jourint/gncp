<?php

namespace App\Filament\Resources\Materials\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\MaterialType;
use App\Models\Unit;

class MaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Наименование материала')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Например: Кожа КРС'),

                Select::make('material_type_id')
                    ->label('Тип материала')
                    ->relationship('materialType', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('color_id')
                    ->label('Цвет')
                    ->relationship('color', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('stock_quantity')
                    ->label('Остаток на складе')
                    ->disabled()
                    ->numeric()
                    ->default(0.00)
                    ->step(1)
                    ->suffix(
                        // Динамически подтягиваем единицу измерения из типа материала
                        fn($get) => MaterialType::find($get('material_type_id'))?->unit_id ? Unit::find(MaterialType::find($get('material_type_id'))->unit_id)?->name : ''
                    ),

                Toggle::make('is_active')
                    ->label('Доступен для использования')
                    ->default(true),
            ]);
    }
}
