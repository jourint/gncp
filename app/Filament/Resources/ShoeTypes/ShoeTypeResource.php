<?php

namespace App\Filament\Resources\ShoeTypes;

use App\Filament\Resources\ShoeTypes\Pages\CreateShoeType;
use App\Filament\Resources\ShoeTypes\Pages\EditShoeType;
use App\Filament\Resources\ShoeTypes\Pages\ListShoeTypes;
use App\Filament\Resources\ShoeTypes\Schemas\ShoeTypeForm;
use App\Filament\Resources\ShoeTypes\Tables\ShoeTypesTable;
use App\Models\ShoeType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoeTypeResource extends Resource
{
    protected static ?string $model = ShoeType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string |UnitEnum|null $navigationGroup = 'Справочники';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Тип обуви';
    protected static ?string $pluralLabel = 'Типы обуви';
    protected static ?string $navigationLabel = 'Типы обуви';

    public static function form(Schema $schema): Schema
    {
        return ShoeTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoeTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ModelsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShoeTypes::route('/'),
            'create' => CreateShoeType::route('/create'),
            'edit' => EditShoeType::route('/{record}/edit'),
        ];
    }
}
