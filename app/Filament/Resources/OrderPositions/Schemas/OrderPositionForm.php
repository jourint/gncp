<?php

namespace App\Filament\Resources\OrderPositions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Order;
use App\Models\Size;

class OrderPositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label('Заказ')
                    ->relationship('order', 'id')
                    ->getOptionLabelFromRecordUsing(fn(Order $record) => "Заказ №{$record->id} ({$record->customer?->name})")
                    ->searchable()
                    ->required(),

                Select::make('shoe_tech_card_id')
                    ->label('Техкарта')
                    ->relationship('shoeTechCard', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('size_id')
                    ->label('Размер')
                    ->options(Size::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                TextInput::make('quantity')
                    ->label('Количество пар')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
            ]);
    }
}
