<?php

namespace App\Filament\Pages\Messenger\Modules;

use App\Models\Employee;
use App\Models\Customer;
use App\Models\MessengerAccount;
use App\Models\MessengerInvite;
use App\Enums\MessengerDriver;
use App\Services\Messenger\MessengerService;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Component;

class AccountManager extends Component
{
    use WithPagination;

    public $search = '';

    // Свойства для идентификации модуля в АРМ
    public static function getTitle(): string
    {
        return 'Управление аккаунтами';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-user-group';
    }

    /**
     * Сброс пагинации при поиске
     */
    public function updatedSearch(): void
    {
        $this->resetPage('employeesPage');
        $this->resetPage('customersPage');
    }

    /**
     * Генерация новой ссылки (Инвайта)
     */
    public function generateInviteLink(int $id, string $morphType, string $driverValue): void
    {
        $driverEnum = MessengerDriver::tryFrom($driverValue);
        $modelClass = $morphType === 'employee' ? Employee::class : Customer::class;
        $user = $modelClass::find($id);

        if (!$user || !$driverEnum) {
            Notification::make()->title('Ошибка данных')->danger()->send();
            return;
        }

        $token = Str::random(32);

        // Используем updateOrCreate, чтобы не плодить инвайты для одного и того же драйвера
        MessengerInvite::updateOrCreate(
            [
                'invitable_id' => $user->id,
                'invitable_type' => $user->getMorphClass(),
                'driver' => $driverEnum->value,
            ],
            [
                'token' => $token,
                'expires_at' => now()->addHours(24),
            ]
        );

        $this->copyLinkToClipboard($driverEnum, $token);
    }

    /**
     * Копирование уже существующего активного инвайта
     */
    public function copyExistingInvite(int $inviteId): void
    {
        $invite = MessengerInvite::find($inviteId);

        if (!$invite || $invite->expires_at->isPast()) {
            Notification::make()->title('Инвайт истек')->danger()->send();
            return;
        }

        //$driverEnum = MessengerDriver::tryFrom($invite->driver);
        $this->copyLinkToClipboard($invite->driver, $invite->token);
    }

    /**
     * Удаление привязки аккаунта
     */
    public function removeAccount(int $accountId): void
    {
        MessengerAccount::destroy($accountId);

        Notification::make()
            ->title('Аккаунт отвязан')
            ->warning()
            ->send();
    }

    /**
     * Вспомогательный метод для отправки ссылки в буфер обмена
     */
    protected function copyLinkToClipboard(MessengerDriver $driver, string $token): void
    {
        $link = app(MessengerService::class)->driver($driver)->getInviteUrl($token);

        $this->dispatch('copy-to-clipboard', text: $link);

        Notification::make()
            ->title('Ссылка скопирована')
            ->body($driver->name . ": " . Str::limit($link, 30))
            ->success()
            ->send();
    }

    public function render()
    {
        $searchQuery = '%' . $this->search . '%';
        $relations = [
            'messengerAccounts',
            'messengerInvites' => fn($q) => $q->where('expires_at', '>', now())
        ];

        return view('filament.pages.messenger.account-manager', [
            'employees' => Employee::with($relations)
                ->where('name', 'iLike', $searchQuery)
                ->paginate(10, ['*'], 'employeesPage'),

            'customers' => Customer::with($relations)
                ->where('name', 'iLike', $searchQuery)
                ->paginate(10, ['*'], 'customersPage'),

            'drivers' => MessengerDriver::cases(),
        ]);
    }
}
