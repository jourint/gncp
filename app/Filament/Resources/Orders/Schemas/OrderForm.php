<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Клиент')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                DatePicker::make('started_at')
                    ->label('Дата начала')
                    ->required()
                    ->default(now()),

                Select::make('status')
                    ->label('Статус')
                    ->options(OrderStatus::class)
                    ->required()
                    ->native(false) // Чтобы выпадал красивый список вместо браузерного
                    ->default(OrderStatus::Pending),

                Textarea::make('comment')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
