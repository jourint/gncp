<?php

namespace App\Filament\Resources\MessengerInvites;

use App\Filament\Resources\MessengerInvites\Pages\CreateMessengerInvite;
use App\Filament\Resources\MessengerInvites\Pages\EditMessengerInvite;
use App\Filament\Resources\MessengerInvites\Pages\ListMessengerInvites;
use App\Filament\Resources\MessengerInvites\Schemas\MessengerInviteForm;
use App\Filament\Resources\MessengerInvites\Tables\MessengerInvitesTable;
use App\Models\MessengerInvite;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessengerInviteResource extends Resource
{
    protected static ?string $model = MessengerInvite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Мессенджер';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Инвайт ссылка';
    protected static ?string $pluralLabel = 'Инвайт ссылки';
    protected static ?string $navigationLabel = 'Инвайт ссылки';

    public static function form(Schema $schema): Schema
    {
        return MessengerInviteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessengerInvitesTable::configure($table);
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
            'index' => ListMessengerInvites::route('/'),
            'create' => CreateMessengerInvite::route('/create'),
            'edit' => EditMessengerInvite::route('/{record}/edit'),
        ];
    }
}
