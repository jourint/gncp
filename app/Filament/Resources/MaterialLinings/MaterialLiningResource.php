<?php

namespace App\Filament\Resources\MaterialLinings;

use App\Filament\Resources\MaterialLinings\Pages\CreateMaterialLining;
use App\Filament\Resources\MaterialLinings\Pages\EditMaterialLining;
use App\Filament\Resources\MaterialLinings\Pages\ListMaterialLinings;
use App\Filament\Resources\MaterialLinings\Schemas\MaterialLiningForm;
use App\Filament\Resources\MaterialLinings\Tables\MaterialLiningsTable;
use App\Models\MaterialLining;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MaterialLiningResource extends Resource
{
    protected static ?string $model = MaterialLining::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Справочники';
    protected static ?int $navigationSort = 12;
    protected static ?string $label = 'Подкладочный материал';
    protected static ?string $pluralLabel = 'Подкладочные материалы';
    protected static ?string $navigationLabel = 'Подкладочный материал';

    public static function form(Schema $schema): Schema
    {
        return MaterialLiningForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaterialLiningsTable::configure($table);
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
            'index' => ListMaterialLinings::route('/'),
            'create' => CreateMaterialLining::route('/create'),
            'edit' => EditMaterialLining::route('/{record}/edit'),
        ];
    }
}
