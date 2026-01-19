<?php

namespace App\Filament\Resources\Counters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CounterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required(),
            ]);
    }
}
