<?php

namespace App\Filament\Resources\MessengerAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Enums\MessengerDriver;

class MessengerAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('messengerable_type')
                    ->label('Тип владельца')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'employee' => 'Сотрудник',
                        'customer' => 'Заказчик',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'employee' => 'warning',
                        'customer' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('messengerable.fullName')
                    ->label('Имя владельца')
                    ->sortable(),

                TextColumn::make('driver')
                    ->label('Мессенджер')
                    ->badge(),

                TextColumn::make('chat_id')
                    ->label('ID чата')
                    ->copyable(),

                IconColumn::make('is_active')
                    ->label('Активен')
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
                SelectFilter::make('driver')
                    ->options(MessengerDriver::class),
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
