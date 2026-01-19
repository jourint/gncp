<?php

namespace App\Filament\Resources\MaterialTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Unit;

class MaterialTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Категория материала')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit_id')
                    ->label('Ед. изм.')
                    ->formatStateUsing(fn($state) => Unit::find($state)?->name ?? '-')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean()
                    ->sortable(),
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
