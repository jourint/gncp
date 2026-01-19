<?php

namespace App\Filament\Resources\ShoeSoles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShoeSoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Код подошвы')
                    ->required()
                    ->maxLength(50),

                Select::make('color_id')
                    ->label('Цвет')
                    ->relationship('color', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Toggle::make('is_active')
                    ->label('Активна')
                    ->default(true),
            ]);
    }
}
