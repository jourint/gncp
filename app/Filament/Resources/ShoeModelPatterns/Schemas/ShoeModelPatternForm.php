<?php

namespace App\Filament\Resources\ShoeModelPatterns\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShoeModelPatternForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('shoe_model_id')
                    ->relationship('shoeModel', 'name')
                    ->required(),
                Select::make('size_id')
                    ->relationship('size', 'name'),
                TextInput::make('file_path')
                    ->required(),
                TextInput::make('file_name')
                    ->required(),
                TextInput::make('type')
                    ->required()
                    ->default('vector'),
                TextInput::make('scale')
                    ->required()
                    ->numeric()
                    ->default(100),
                TextInput::make('note'),
            ]);
    }
}
