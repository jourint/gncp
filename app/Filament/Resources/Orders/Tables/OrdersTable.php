<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Traits\HasCustomTableSearch;

class OrdersTable
{
    use HasCustomTableSearch;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['customer']))
            ->defaultSort('started_at', 'desc')
            ->columns([
                TextColumn::make('fullName')
                    ->label('Клиент')
                    ->searchable(query: self::searchRelation('customer', 'name'))
                    ->sortable(query: self::sortRelation('customers', 'orders.customer_id', 'name')),

                TextColumn::make('started_at')
                    ->label('Дата начала')
                    ->date('d.m.Y')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50),

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
