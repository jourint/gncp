<?php

namespace App\Filament\Resources\MaterialTypes;

use App\Filament\Resources\MaterialTypes\Pages\CreateMaterialType;
use App\Filament\Resources\MaterialTypes\Pages\EditMaterialType;
use App\Filament\Resources\MaterialTypes\Pages\ListMaterialTypes;
use App\Filament\Resources\MaterialTypes\Schemas\MaterialTypeForm;
use App\Filament\Resources\MaterialTypes\Tables\MaterialTypesTable;
use App\Models\MaterialType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MaterialTypeResource extends Resource
{
    protected static ?string $model = MaterialType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static string |UnitEnum|null $navigationGroup = 'Справочники';
    protected static ?int $navigationSort = 6;
    protected static ?string $label = 'Категория материала';
    protected static ?string $pluralLabel = 'Категории материалов';
    protected static ?string $navigationLabel = 'Категории материалов';

    public static function form(Schema $schema): Schema
    {
        return MaterialTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaterialTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MaterialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMaterialTypes::route('/'),
            'create' => CreateMaterialType::route('/create'),
            'edit' => EditMaterialType::route('/{record}/edit'),
        ];
    }
}
