<?php

namespace App\Filament\Resources\OrderEmployees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;

class OrderEmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.fullName')
                    ->label('Сотрудник')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order_id')
                    ->label('Заказ')
                    ->formatStateUsing(fn($record) => "{$record->order?->fullName} {$record->order->started_at->format('d.m.Y')}"),

                TextColumn::make('quantity')
                    ->label('Пар')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('price_per_pair')
                    ->label('За пару')
                    ->money('UAH'),

                // Просто умножаем в памяти для отображения
                TextColumn::make('total')
                    ->label('Итого')
                    ->state(fn($record) => $record->quantity * $record->price_per_pair)
                    ->money('UAH')
                    ->color('success'),

                IconColumn::make('is_paid')
                    ->label('Оплата')
                    ->boolean()
                    ->sortable(),


                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
