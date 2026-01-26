<?php

namespace App\Filament\Resources\ShoeSoleItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use App\Models\ShoeSole;
use App\Models\Size;

class ShoeSoleItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('shoe_sole_id')
                            ->label('Модель подошвы')
                            ->relationship(
                                name: 'shoeSole',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->with('color')
                            )
                            ->getOptionLabelFromRecordUsing(fn(ShoeSole $record) => $record->fullName)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('size_id')
                            ->label('Размер')
                            ->options(fn() => Size::pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        TextInput::make('stock_quantity')
                            ->label('Текущий остаток')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(true),
                    ]),
            ]);
    }
}
