<?php

namespace App\Filament\Resources\ShoeTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShoeTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Тип обуви')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price_cutting')
                    ->label('Раскрой')
                    ->money('UAH')
                    ->sortable(),

                TextColumn::make('price_sewing')
                    ->label('Пошив')
                    ->money('UAH')
                    ->sortable(),

                TextColumn::make('price_shoemaker')
                    ->label('Сборка')
                    ->money('UAH')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean(),

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
