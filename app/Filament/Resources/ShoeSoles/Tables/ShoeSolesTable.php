<?php

namespace App\Filament\Resources\ShoeSoles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Table;
use App\Filament\Actions\ReplicateShoeSoleWithItemsToNextColorAction;

class ShoeSolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Код подошвы')
                    ->searchable()
                    ->sortable(),

                ColorColumn::make('color.hex')
                    ->label('Цвет'),

                TextColumn::make('color.name')
                    ->label('Название цвета')
                    ->searchable(),

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
                ReplicateShoeSoleWithItemsToNextColorAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
