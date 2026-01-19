<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('ФИО / Название')
                    ->required()
                    ->maxLength(100),

                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->required()
                    ->unique('customers', 'phone', ignoreRecord: true)
                    ->mask('+38 (999) 999-99-99')
                    ->stripCharacters([' ', '(', ')', '-', '+'])
                    ->placeholder('+38 (___) ___-__-__'),

                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
