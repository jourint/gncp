<?php

namespace App\Filament\Resources\MessengerInvites\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Relations\Relation;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Enums\MessengerDriver;

class MessengerInviteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Владелец аккаунта')
                    ->schema([
                        Select::make('invitable_type')
                            ->label('Тип владельца')
                            ->options([
                                'employee' => 'Сотрудник',
                                'customer' => 'Заказчик',
                            ])
                            ->required()
                            ->live(),

                        Select::make('invitable_id')
                            ->label('Личность')
                            ->options(function (callable $get) {
                                $type = $get('invitable_type');
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

                Section::make('Настройки приглашения')
                    ->schema([
                        TextInput::make('token')
                            ->label('Токен')
                            ->unique()
                            ->required(),

                        Select::make('driver')
                            ->label('Мессенджер')
                            ->options(MessengerDriver::class)
                            ->required()
                            ->native(false),

                        DateTimePicker::make('expires_at')
                            ->label('Истекает в')
                            ->required(),
                    ])->columns(1),
            ]);
    }
}
