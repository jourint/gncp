<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Enums\OrderStatus;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fullName')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Дата начала')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(OrderStatus $state): string => match ($state) {
                        OrderStatus::Pending => 'gray',
                        OrderStatus::Processing => 'warning',
                        OrderStatus::Completed => 'success',
                        OrderStatus::Cancelled => 'danger',
                    }),

                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50),

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
