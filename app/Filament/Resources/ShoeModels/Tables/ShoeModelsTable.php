<?php

namespace App\Filament\Resources\ShoeModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShoeModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название модели')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shoeType.name')
                    ->label('Тип обуви')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('shoeInsole.fullName')
                    ->label('Тип стельки')
                    ->sortable(),

                // Просто выводим массив ID, так как они равны названиям
                TextColumn::make('available_sizes')
                    ->label('Размерная сетка')
                    ->badge() // Сделаем в виде меток для красоты
                    ->separator(',')
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),


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
