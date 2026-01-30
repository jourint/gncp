<?php

namespace App\Filament\Pages\Messenger\Actions;

use App\Enums\MessengerDriver;
use App\Models\MessengerAccount;
use App\Models\Employee;
use App\Models\Customer;
use App\Services\Messenger\MessengerService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

class BroadcastAction
{
    public static function make(): Action
    {
        return Action::make('broadcast')
            ->label('Рассылка / Личное')
            ->icon('heroicon-o-megaphone')
            ->color('primary')
            ->modalHeading('Отправка сообщения')
            ->modalWidth('4xl')
            ->schema([
                Section::make('Параметры получателя')
                    ->schema([
                        Select::make('driver')
                            ->label('Мессенджер')
                            ->options(MessengerDriver::class)
                            ->required()
                            ->native(false)
                            ->live(), // Нужно для фильтрации людей по драйверу

                        Select::make('recipient_type')
                            ->label('Тип рассылки')
                            ->options([
                                'all' => 'Все активные',
                                'employee' => 'Все сотрудники',
                                'customer' => 'Все заказчики',
                                'personal' => '✨ Лично',
                            ])
                            ->default('all')
                            ->live()
                            ->required(),

                        Select::make('personal_model_type')
                            ->label('Тип контакта')
                            ->options([
                                'employee' => 'Сотрудник',
                                'customer' => 'Заказчик',
                            ])
                            ->required()
                            ->live()
                            ->visible(fn($get) => $get('recipient_type') === 'personal'),

                        Select::make('personal_model_id')
                            ->label('Выберите контакт')
                            ->placeholder(fn($get) => $get('personal_model_type') ? 'Поиск...' : 'Виберите тип и драйвер')
                            ->searchable()
                            ->required()
                            ->visible(fn($get) => $get('recipient_type') === 'personal')
                            ->disabled(fn($get) => !$get('personal_model_type') || !$get('driver'))

                            // 1. Поиск: Только те, у кого есть аккаунт выбранного драйвера
                            ->getSearchResultsUsing(function (string $search, $get) {
                                $type = $get('personal_model_type');
                                $driver = $get('driver');
                                if (!$type || !$driver) return [];

                                $modelClass = $type === 'customer' ? Customer::class : Employee::class;

                                return $modelClass::query()
                                    ->whereHas('messengerAccounts', function ($q) use ($driver) {
                                        $q->where('driver', $driver)->where('is_active', true);
                                    })
                                    ->where('name', 'ilike', "%{$search}%")
                                    ->limit(20)
                                    ->pluck('name', 'id');
                            })
                            // 2. РЕШЕНИЕ LogicException: Позволяет Filament отобразить выбранное имя
                            ->getOptionLabelUsing(function ($value, $get) {
                                $type = $get('personal_model_type');
                                if (!$type || !$value) return null;

                                $modelClass = $type === 'customer' ? Customer::class : Employee::class;
                                return $modelClass::find($value)?->name;
                            }),
                    ])->columns(2),

                Section::make('Контент')
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->default('Уведомление')
                            ->required(),

                        RichEditor::make('message') // Вот наш HTML-редактор
                            ->label('Текст сообщения (поддерживает HTML)')
                            ->fileAttachmentsDisk('public') // Куда сохранять вложения (изображения)
                            ->fileAttachmentsDirectory('messenger-attachments') // Папка для вложений
                            ->toolbarButtons([ // Какие кнопки будут на панели
                                'bold',
                                'italic',
                                'link',
                                'blockquote',
                                'bulletList',
                            ])
                            ->extraInputAttributes([
                                'style' => 'min-height: 12rem;',
                            ])
                            ->required(),

                        // Textarea::make('message')
                        //     ->label('Текст сообщения')
                        //     ->rows(5)
                        //     ->required(),
                    ]),
            ])
            ->action(function (array $data, MessengerService $service): void {
                $driverValue = $data['driver'] instanceof MessengerDriver
                    ? $data['driver']->value
                    : $data['driver'];

                $query = MessengerAccount::query()
                    ->where('driver', $driverValue)
                    ->where('is_active', true);

                if ($data['recipient_type'] === 'personal') {
                    $query->where('messengerable_type', $data['personal_model_type'])
                        ->where('messengerable_id', $data['personal_model_id']);
                } elseif ($data['recipient_type'] !== 'all') {
                    $query->where('messengerable_type', $data['recipient_type']);
                }

                $accounts = $query->get();

                if ($accounts->isEmpty()) {
                    Notification::make()
                        ->title('Ошибка отправки')
                        ->body('Не найдено ни одного привязанного аккаунта для выбранных условий.')
                        ->danger()
                        ->send();
                    return;
                }
                $sent = 0;
                $finalMessage = $service->formatForTelegram($data['message']);
                foreach ($accounts as $account) {
                    if ($service->sendMessage($account, $finalMessage, ['title' => $data['title']])) {
                        $sent++;
                    }
                }

                Notification::make()
                    ->title('Рассылка выполнена')
                    ->body("Успешно отправлено: {$sent}")
                    ->success()
                    ->send();
            });
    }
}
