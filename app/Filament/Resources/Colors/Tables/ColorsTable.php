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
            ->columns([
                TextColumn::make('name')
                    ->label('Название цвета')
                    ->sortable()
                    ->searchable(),

                ColorColumn::make('hex')
                    ->label('Цвет'),

                TextColumn::make('hex_code')
                    ->label('HEX код')
                    ->getStateUsing(fn($record) => $record->hex) // Указываем брать данные из поля hex
                    ->fontFamily('mono')
                    ->copyMessage('HEX код скопирован')
                    ->copyable(), // Очень удобно для работы с дизайном
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
