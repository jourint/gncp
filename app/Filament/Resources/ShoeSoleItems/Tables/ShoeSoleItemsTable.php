<?php

namespace App\Filament\Resources\ShoeSoleItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShoeSoleItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shoeSole.fullName')
                    ->label('Подошва')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('size_id')
                    ->label('Размер')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('stock_quantity')
                    ->label('На складе')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger')
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
