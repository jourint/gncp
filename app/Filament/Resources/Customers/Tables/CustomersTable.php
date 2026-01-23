<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->color('primary')
                    // Форматируем вывод: превращаем 380991234567 в читаемый вид
                    ->formatStateUsing(
                        fn(string $state): string =>
                        preg_replace('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1 ($2) $3-$4-$5', $state)
                    )
                    // Делаем номер кликабельным для звонка
                    ->url(fn($record) => "tel:{$record->phone}"),

                IconColumn::make('is_active')
                    ->label('Статус')
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
