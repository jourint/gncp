<?php

namespace App\Filament\Resources\MessengerLogs;

use App\Filament\Resources\MessengerLogs\Pages\CreateMessengerLog;
use App\Filament\Resources\MessengerLogs\Pages\EditMessengerLog;
use App\Filament\Resources\MessengerLogs\Pages\ListMessengerLogs;
use App\Filament\Resources\MessengerLogs\Schemas\MessengerLogForm;
use App\Filament\Resources\MessengerLogs\Tables\MessengerLogsTable;
use App\Models\MessengerLog;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessengerLogResource extends Resource
{
    protected static ?string $model = MessengerLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Мессенджер';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Лог мессенджера';
    protected static ?string $pluralLabel = 'Логи мессенджеров';
    protected static ?string $navigationLabel = 'Логи мессенджеров';

    public static function form(Schema $schema): Schema
    {
        return MessengerLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessengerLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessengerLogs::route('/'),
            'create' => CreateMessengerLog::route('/create'),
            'edit' => EditMessengerLog::route('/{record}/edit'),
        ];
    }
}
