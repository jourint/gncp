<?php

namespace App\Filament\Resources\Puffs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PuffForm
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
