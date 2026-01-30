<?php

namespace App\Filament\Resources\MessengerInvites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Enums\MessengerDriver;

class MessengerInvitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invitable_type')
                    ->label('Тип аккаунта')
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

                TextColumn::make('invitable.fullName')
                    ->label('Имя аккаунта')
                    ->sortable(),

                TextColumn::make('token')
                    ->label('Токен приглашения')
                    ->searchable(),

                TextColumn::make('expires_at')
                    ->label('Истекает в')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('driver')
                    ->label('Мессенджер')
                    ->badge(),

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
                    ->options(MessengerDriver::class)
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
