<?php

namespace App\Filament\Resources\MaterialTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use App\Enums\Unit;
use Filament\Schemas\Schema;

class MaterialTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название категории')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                Select::make('unit_id')
                    ->label('Единица измерения')
                    ->options(
                        collect(Unit::cases())->mapWithKeys(fn(Unit $u) => [$u->value => $u->getFullName()])
                    )
                    ->required()
                    ->native(false),

                TextInput::make('description')
                    ->label('Описание')
                    ->columnSpanFull()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
