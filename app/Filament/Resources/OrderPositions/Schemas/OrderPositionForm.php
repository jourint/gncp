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
                    ->relationship('order', 'id', modifyQueryUsing: fn($query) => $query->orderBy('started_at', 'desc'))
                    ->getOptionLabelFromRecordUsing(fn(Order $record) => "{$record->fullName} ({$record->started_at->format('d.m.Y')})")
                    ->preload()
                    ->required(),

                Select::make('shoe_tech_card_id')
                    ->label('Техкарта')
                    ->relationship('shoeTechCard', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('material_lining_id')
                    ->label('Подкладка')
                    ->relationship('materialLining', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn($record) => $record->fullName
                    )
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
