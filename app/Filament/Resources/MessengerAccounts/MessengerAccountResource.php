<?php

namespace App\Filament\Resources\MessengerAccounts;

use App\Filament\Resources\MessengerAccounts\Pages\CreateMessengerAccount;
use App\Filament\Resources\MessengerAccounts\Pages\EditMessengerAccount;
use App\Filament\Resources\MessengerAccounts\Pages\ListMessengerAccounts;
use App\Filament\Resources\MessengerAccounts\Schemas\MessengerAccountForm;
use App\Filament\Resources\MessengerAccounts\Tables\MessengerAccountsTable;
use App\Models\MessengerAccount;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessengerAccountResource extends Resource
{
    protected static ?string $model = MessengerAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Мессенджер';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Аккаунт мессенджера';
    protected static ?string $pluralLabel = 'Аккаунты мессенджеров';
    protected static ?string $navigationLabel = 'Аккаунты мессенджеров';

    public static function form(Schema $schema): Schema
    {
        return MessengerAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessengerAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessengerLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessengerAccounts::route('/'),
            'create' => CreateMessengerAccount::route('/create'),
            'edit' => EditMessengerAccount::route('/{record}/edit'),
        ];
    }
}
