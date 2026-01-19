<?php

namespace App\Filament\Resources\ShoeInsoles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShoeInsolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Код резака')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('is_black')
                    ->label('Цвет')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Черная' : 'Светлая')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'gray' : 'warning'),

                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
