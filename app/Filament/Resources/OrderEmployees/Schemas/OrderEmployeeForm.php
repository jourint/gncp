<?php

namespace App\Filament\Resources\OrderEmployees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\OrderPosition;
use App\Models\Order;

class OrderEmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label('Заказ')
                    ->relationship('order', 'id')
                    // Делаем поиск удобным: "№5 - Иван Иванов (15.01.2026)"
                    ->getOptionLabelFromRecordUsing(fn(Order $record) => "№{$record->id} — {$record->customer?->name} (" . ($record->started_at?->format('d.m.Y') ?? 'нет даты') . ")")
                    ->searchable(['id']) // Можно добавить поиск по имени клиента, если прописать через query
                    ->required()
                    ->live(),

                Select::make('order_position_id')
                    ->label('Позиция заказа')
                    ->options(function (callable $get) {
                        $orderId = $get('order_id');
                        if (!$orderId) return [];

                        return OrderPosition::where('order_id', $orderId)
                            ->with(['shoeTechCard'])
                            ->get()
                            ->mapWithKeys(fn($pos) => [
                                $pos->id => "{$pos->shoeTechCard?->name} (Размер: {$pos->size_id}, Всего: {$pos->quantity} пар)"
                            ]);
                    })
                    ->required()
                    ->searchable(),

                Select::make('employee_id')
                    ->label('Сотрудник')
                    ->relationship('employee', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('quantity')
                    ->label('Сделано пар')
                    ->numeric()
                    ->default(1)
                    ->required(),

                TextInput::make('price_per_pair')
                    ->label('Цена за пару')
                    ->numeric()
                    ->prefix('₴') // UAH
                    ->default(0.00),

                Toggle::make('is_paid')
                    ->label('Оплачено')
                    ->default(false),
            ]);
    }
}
