<?php

namespace App\Filament\Resources\ShoeInsoles;

use App\Filament\Resources\ShoeInsoles\Pages\CreateShoeInsole;
use App\Filament\Resources\ShoeInsoles\Pages\EditShoeInsole;
use App\Filament\Resources\ShoeInsoles\Pages\ListShoeInsoles;
use App\Filament\Resources\ShoeInsoles\Schemas\ShoeInsoleForm;
use App\Filament\Resources\ShoeInsoles\Tables\ShoeInsolesTable;
use App\Models\ShoeInsole;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoeInsoleResource extends Resource
{
    protected static ?string $model = ShoeInsole::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string |UnitEnum|null $navigationGroup = 'Справочники';
    protected static ?int $navigationSort = 9;
    protected static ?string $label = 'Стелька';
    protected static ?string $pluralLabel = 'Стельки';
    protected static ?string $navigationLabel = 'Стельки';

    public static function form(Schema $schema): Schema
    {
        return ShoeInsoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoeInsolesTable::configure($table);
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
            'index' => ListShoeInsoles::route('/'),
            'create' => CreateShoeInsole::route('/create'),
            'edit' => EditShoeInsole::route('/{record}/edit'),
        ];
    }
}
