<?php

namespace App\Filament\Resources\MessengerAccounts\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessengerLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'messengerLogs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('messenger_account_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('messenger_account_id')
            ->columns([
                TextColumn::make('messenger_account_id')
                    ->label('Инициатор операции')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),

                TextColumn::make('error_message')
                    ->label('Сообщение об ошибке')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('sent_at')
                    ->label('Время отправки')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
