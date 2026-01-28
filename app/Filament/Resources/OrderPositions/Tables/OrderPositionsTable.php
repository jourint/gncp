<?php

namespace App\Filament\Resources\OrderPositions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\Size;

class OrderPositionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('order.fullName')
                    ->label('Заказ №')
                    ->sortable(),

                TextColumn::make('order.status')
                    ->label('Статус заказа')
                    ->badge()
                    ->sortable(),

                TextColumn::make('shoeTechCard.name')
                    ->label('Техкарта / Модель')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('size_id')
                    ->label('Размер')
                    ->formatStateUsing(fn($state) => Size::find($state)?->name ?? $state)
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Пар')
                    ->badge()
                    ->color('info')
                    ->sortable(),


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
                SelectFilter::make('shoe_tech_card_id')
                    ->label('Модель')
                    ->relationship('shoeTechCard', 'name'),
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
