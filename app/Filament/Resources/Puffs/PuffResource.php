<?php

namespace App\Filament\Resources\Puffs;

use App\Filament\Resources\Puffs\Pages\CreatePuff;
use App\Filament\Resources\Puffs\Pages\EditPuff;
use App\Filament\Resources\Puffs\Pages\ListPuffs;
use App\Filament\Resources\Puffs\Schemas\PuffForm;
use App\Filament\Resources\Puffs\Tables\PuffsTable;
use App\Models\Puff;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PuffResource extends Resource
{
    protected static ?string $model = Puff::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Справочники';
    protected static ?int $navigationSort = 11;
    protected static ?string $label = 'Подноска';
    protected static ?string $pluralLabel = 'Подноски';
    protected static ?string $navigationLabel = 'Подноски';

    public static function form(Schema $schema): Schema
    {
        return PuffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PuffsTable::configure($table);
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
            'index' => ListPuffs::route('/'),
            'create' => CreatePuff::route('/create'),
            'edit' => EditPuff::route('/{record}/edit'),
        ];
    }
}
