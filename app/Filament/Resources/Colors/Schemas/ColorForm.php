<?php

namespace App\Filament\Resources\Colors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
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
                    ->default('#ffffff'),
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
