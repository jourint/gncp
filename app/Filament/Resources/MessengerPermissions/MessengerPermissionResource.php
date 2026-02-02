<?php

namespace App\Filament\Resources\MessengerPermissions;

use App\Filament\Resources\MessengerPermissions\Pages\CreateMessengerPermission;
use App\Filament\Resources\MessengerPermissions\Pages\EditMessengerPermission;
use App\Filament\Resources\MessengerPermissions\Pages\ListMessengerPermissions;
use App\Filament\Resources\MessengerPermissions\Schemas\MessengerPermissionForm;
use App\Filament\Resources\MessengerPermissions\Tables\MessengerPermissionsTable;
use App\Models\MessengerPermission;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessengerPermissionResource extends Resource
{
    protected static ?string $model = MessengerPermission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Мессенджер';
    protected static ?int $navigationSort = 5;
    protected static ?string $label = 'Права доступа';
    protected static ?string $pluralLabel = 'Права доступа';
    protected static ?string $navigationLabel = 'Права доступа';

    public static function form(Schema $schema): Schema
    {
        return MessengerPermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessengerPermissionsTable::configure($table);
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
            'index' => ListMessengerPermissions::route('/'),
            'create' => CreateMessengerPermission::route('/create'),
            'edit' => EditMessengerPermission::route('/{record}/edit'),
        ];
    }
}
