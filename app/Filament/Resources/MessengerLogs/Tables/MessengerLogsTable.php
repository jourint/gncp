<?php

namespace App\Filament\Resources\MessengerLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessengerLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('account.messengerable.fullName')
                    ->label('Получатель')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Заголовок')
                    ->limit(20)
                    ->searchable(),

                // TextColumn::make('message')
                //     ->label('Сообщение')
                //     ->limit(50)
                //     ->searchable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),

                TextColumn::make('error_message')
                    ->label('Ошибка')
                    ->wrap()
                    ->color('danger'),

                TextColumn::make('sent_at')
                    ->label('Отправлено')
                    ->dateTime()
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
