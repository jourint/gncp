<?php

namespace App\Filament\Resources\Colors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Table;

class ColorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Название цвета')
                    ->sortable()
                    ->searchable(),

                ColorColumn::make('hex')
                    ->label('Цвет'),

                TextColumn::make('hex_code')
                    ->label('HEX код')
                    ->getStateUsing(fn($record) => $record->hex)
                    ->fontFamily('mono')
                    ->copyMessage('HEX код скопирован')
                    ->copyable(),

                TextColumn::make('is_active')
                    ->label('Активен')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Да' : 'Нет')
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->sortable()
                    ->searchable(),
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
