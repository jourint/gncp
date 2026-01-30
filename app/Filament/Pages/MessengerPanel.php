<?php

namespace App\Filament\Pages;

use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Pages\Page;
use App\Filament\Pages\Messenger\Actions\BroadcastAction;
use App\Filament\Pages\Messenger\Actions\SyncTelegramAction;
use App\Filament\Pages\Messenger\Modules\AccountManager;
use App\Filament\Pages\Messenger\Modules\ReportCustomer;
use App\Filament\Pages\Messenger\Modules\ReportEmployee;
use App\Filament\Pages\Messenger\Modules\ReportExpedition;
use App\Filament\Pages\Messenger\Modules\ReportAccountant;
use App\Filament\Pages\Messenger\Modules\ReportWarehouse;


class MessengerPanel extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;
    protected string $view = 'filament.pages.messenger-panel';
    protected static ?string $title = 'АРМ - Уведомления';
    protected static ?int $navigationSort = 6;

    public string $activeModule = 'accounts';

    /**
     * Регистрация модулей панели из отдельных классов
     */
    public function getModules(): array
    {
        return [
            'accounts' => AccountManager::class,
            'report_customers' => ReportCustomer::class,
            'report_employees' => ReportEmployee::class,
            'report_expedition' => ReportExpedition::class,
            'report_accountants' => ReportAccountant::class,
            'report_warehouse' => ReportWarehouse::class,
        ];
    }

    /**
     * Регистрация заголовков (Header Actions) из отдельных классов
     */
    protected function getHeaderActions(): array
    {
        return [
            BroadcastAction::make(),
            SyncTelegramAction::make()
        ];
    }
}
