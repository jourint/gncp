<?php

namespace App\Filament\Resources\MaterialTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use App\Models\Unit;
use Filament\Schemas\Schema;

class MaterialTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название категории')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('unit_id')
                    ->label('Единица измерения')
                    ->options(Unit::all()->pluck('name', 'id'))
                    ->required()
                    ->native(false), // Красивый выпадающий список

                TextInput::make('description')
                    ->label('Описание')
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
