<?php

namespace App\Filament\Resources\MaterialLinings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MaterialLiningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Наименование подкладки')
                    ->maxLength(50)
                    ->placeholder('Например: Мах, байка, дермонтин')
                    ->required(),

                Select::make('color_id')
                    ->label('Цвет')
                    ->relationship('color', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Toggle::make('is_active')
                    ->label('Доступен для использования')
                    ->default(true)
                    ->required(),
            ]);
    }
}
