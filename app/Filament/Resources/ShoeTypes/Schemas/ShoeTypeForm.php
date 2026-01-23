<?php

namespace App\Filament\Resources\ShoeTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShoeTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название типа')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Например: Туфли'),

                TextInput::make('price_cutting')
                    ->label('Цена за раскрой')
                    ->numeric()
                    ->prefix('₴')
                    ->default(0),

                TextInput::make('price_sewing')
                    ->label('Цена за пошив')
                    ->numeric()
                    ->prefix('₴')
                    ->default(0),

                TextInput::make('price_shoemaker')
                    ->label('Цена за сборку (сапожник)')
                    ->numeric()
                    ->prefix('₴')
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Доступен для моделей')
                    ->default(true),
            ]);
    }
}
