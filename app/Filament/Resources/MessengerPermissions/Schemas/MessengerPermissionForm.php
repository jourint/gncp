<?php

namespace App\Filament\Resources\MessengerPermissions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MessengerPermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('label')
                    ->required(),
            ]);
    }
}
