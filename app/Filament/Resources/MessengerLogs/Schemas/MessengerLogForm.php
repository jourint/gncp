<?php

namespace App\Filament\Resources\MessengerLogs\Schemas;

use App\Enums\MessengerStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MessengerLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация об отправке')
                    ->schema([
                        Select::make('messenger_account_id')
                            ->label('Аккаунт получателя')
                            ->relationship('account', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->messengerable?->fullName} [{$record->driver->getLabel()}]")
                            ->disabled(),

                        Select::make('status')
                            ->label('Статус')
                            ->options(MessengerStatus::class)
                            ->required(),

                        DateTimePicker::make('sent_at')
                            ->label('Дата и время отправки')
                            ->native(false),
                    ])->columns(1),

                Section::make('Содержимое')
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок'),

                        Textarea::make('message')
                            ->label('Текст сообщения')
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('error_message')
                            ->label('Текст ошибки API')
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'font-mono text-danger-600']),
                    ]),
            ]);
    }
}
