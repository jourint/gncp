<?php

namespace App\Filament\Resources\ShoeSoleItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Traits\HasCustomTableSearch;

class ShoeSoleItemsTable
{
    use HasCustomTableSearch;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['shoeSole.color']))
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('shoeSole.fullName')
                    ->label('Подошва')
                    ->searchable(query: self::searchRelation('shoeSole', ['name', 'color.name']))
                    ->sortable(query: self::sortRelation('shoe_soles', 'shoe_sole_items.shoe_sole_id', 'name')),

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
