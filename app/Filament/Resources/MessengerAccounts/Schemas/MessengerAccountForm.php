<?php

namespace App\Filament\Resources\MessengerAccounts\Schemas;

use App\Enums\MessengerDriver;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;

class MessengerAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Владелец аккаунта')
                    ->schema([
                        Select::make('messengerable_type')
                            ->label('Тип владельца')
                            ->options([
                                'employee' => 'Сотрудник',
                                'customer' => 'Заказчик',
                            ])
                            ->required()
                            ->live(),

                        Select::make('messengerable_id')
                            ->label('Личность')
                            ->options(function (callable $get) {
                                $type = $get('messengerable_type');
                                if (!$type) return [];
                                // Получаем имя класса из Morph Map
                                $modelClass = Relation::getMorphedModel($type);
                                if (!$modelClass) return [];
                                return $modelClass::query()
                                    ->get()
                                    ->pluck('fullName', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->fullName),
                    ])->columns(2),

                Section::make('Настройки мессенджера')
                    ->schema([
                        Select::make('driver')
                            ->label('Мессенджер')
                            ->options(MessengerDriver::class)
                            ->required()
                            ->native(false),

                        TextInput::make('chat_id')
                            ->label('ID чата')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('identifier')
                            ->label('Username или Телефон'),

                        TextInput::make('nickname')
                            ->label('Никнейм'),

                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
