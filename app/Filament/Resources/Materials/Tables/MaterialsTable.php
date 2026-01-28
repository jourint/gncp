<?php

namespace App\Filament\Resources\Materials\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Table;

class MaterialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('materialType.name')
                    ->label('Тип')
                    ->badge()
                    ->color('gray'),

                // Показываем кружок цвета прямо в таблице материалов
                ColorColumn::make('color.hex')
                    ->label('Цвет')
                    ->placeholder('N/A'),

                TextColumn::make('stock_quantity')
                    ->label('Склад')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn($state) => $state <= 0 ? 'danger' : 'success')
                    ->suffix(fn($record) => ' ' . ($record->materialType?->unit_id?->getLabel() ?? '')),

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
