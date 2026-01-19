<?php

namespace App\Filament\Resources\Colors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Schema;

class ColorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название цвета')
                    ->required()
                    ->unique(ignoreRecord: true),
                ColorPicker::make('hex')
                    ->label('Палитра')
                    ->required()
                    // Опционально: можно ограничить выбор только определенными цветами
                    // ->colors(['#FFFFFF', '#000000', '#FF0000']) 
                    ->default('#ffffff'),
                // TextInput::make('hex')->required(),
            ]);
    }
}
