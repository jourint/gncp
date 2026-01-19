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
                        // Выбор "головы" подошвы (Модель + Цвет)
                        Select::make('shoe_sole_id')
                            ->label('Модель подошвы')
                            ->relationship('shoeSole', 'name')
                            ->getOptionLabelFromRecordUsing(fn(ShoeSole $record) => "{$record->name} (Цвет: {$record->color?->name})")
                            ->searchable()
                            ->preload()
                            ->required(),

                        // Выбор размера из Sushi-модели
                        Select::make('size_id')
                            ->label('Размер')
                            ->options(Size::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        TextInput::make('stock_quantity')
                            ->label('Текущий остаток')
                            ->numeric()
                            ->default(0)
                            ->disabled() // Лучше сделать disabled, если остаток должен меняться только через Movement
                            ->dehydrated(true),
                    ]),
            ]);
    }
}
