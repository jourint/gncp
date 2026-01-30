<?php

namespace App\Filament\Pages\Messenger\Actions;

use App\Enums\MessengerDriver;
use App\Models\MessengerAccount;
use App\Services\Messenger\MessengerService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

class BroadcastAction
{
    public static function make(): Action
    {
        return Action::make('broadcast')
            ->label('Рассылка')
            ->icon('heroicon-o-megaphone')
            ->color('primary')
            ->modalHeading('Массовая рассылка')
            ->schema([
                Section::make('Параметры')
                    ->schema([
                        Select::make('driver')
                            ->label('Мессенджер')
                            ->options(MessengerDriver::class)
                            ->required()
                            ->native(false),

                        Select::make('recipient_type')
                            ->label('Кому')
                            ->options([
                                'all' => 'Все',
                                'employee' => 'Сотрудники',
                                'customer' => 'Заказчики',
                            ])
                            ->default('all')
                            ->required(),
                    ])->columns(2),

                Section::make('Контент')
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->default('Общее уведомление')
                            ->required(),

                        Textarea::make('message')
                            ->label('Текст сообщения')
                            ->rows(5)
                            ->required(),
                    ]),
            ])
            ->action(function (array $data, MessengerService $service): void {
                // ИСПРАВЛЕНИЕ: Гарантируем, что берем string value из Enum
                $driverValue = $data['driver'] instanceof MessengerDriver
                    ? $data['driver']->value
                    : $data['driver'];

                $query = MessengerAccount::query()
                    ->where('driver', $driverValue)
                    ->where('is_active', true);

                if ($data['recipient_type'] !== 'all') {
                    $query->where('messengerable_type', $data['recipient_type']);
                }

                $accounts = $query->get();
                $sent = 0;

                foreach ($accounts as $account) {
                    // Передаем объект аккаунта, сервис сам разберется с его драйвером
                    if ($service->sendMessage($account, $data['message'], ['title' => $data['title']])) {
                        $sent++;
                    }
                }

                Notification::make()
                    ->title($sent > 0 ? 'Рассылка завершена' : 'Получатели не найдены')
                    ->body("Отправлено: {$sent} из " . $accounts->count())
                    ->status($sent > 0 ? 'success' : 'warning')
                    ->send();
            });
    }
}
