<?php

namespace App\Filament\Resources\MaterialMovements;

use App\Filament\Resources\MaterialMovements\Pages\CreateMaterialMovement;
use App\Filament\Resources\MaterialMovements\Pages\EditMaterialMovement;
use App\Filament\Resources\MaterialMovements\Pages\ListMaterialMovements;
use App\Filament\Resources\MaterialMovements\Schemas\MaterialMovementForm;
use App\Filament\Resources\MaterialMovements\Tables\MaterialMovementsTable;
use App\Models\MaterialMovement;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MaterialMovementResource extends Resource
{
    protected static ?string $model = MaterialMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string |UnitEnum|null $navigationGroup = 'Склад';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Движение материала';
    protected static ?string $pluralLabel = 'Движения материала';
    protected static ?string $navigationLabel = 'Движения материала';

    public static function form(Schema $schema): Schema
    {
        return MaterialMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaterialMovementsTable::configure($table);
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
            'index' => ListMaterialMovements::route('/'),
            'create' => CreateMaterialMovement::route('/create'),
            'edit' => EditMaterialMovement::route('/{record}/edit'),
        ];
    }
}
