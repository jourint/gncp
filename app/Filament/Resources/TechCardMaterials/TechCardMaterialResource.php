<?php

namespace App\Filament\Resources\TechCardMaterials;

use App\Filament\Resources\TechCardMaterials\Pages\CreateTechCardMaterial;
use App\Filament\Resources\TechCardMaterials\Pages\EditTechCardMaterial;
use App\Filament\Resources\TechCardMaterials\Pages\ListTechCardMaterials;
use App\Filament\Resources\TechCardMaterials\Schemas\TechCardMaterialForm;
use App\Filament\Resources\TechCardMaterials\Tables\TechCardMaterialsTable;
use App\Models\TechCardMaterial;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TechCardMaterialResource extends Resource
{
    protected static ?string $model = TechCardMaterial::class;

    // hidden from navigation
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Производство';
    protected static ?int $navigationSort = 6;
    protected static ?string $label = 'Состав тех-карты';
    protected static ?string $pluralLabel = 'Состав тех-карт';
    protected static ?string $navigationLabel = 'Состав тех-карт';

    public static function form(Schema $schema): Schema
    {
        return TechCardMaterialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TechCardMaterialsTable::configure($table);
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
            'index' => ListTechCardMaterials::route('/'),
            'create' => CreateTechCardMaterial::route('/create'),
            'edit' => EditTechCardMaterial::route('/{record}/edit'),
        ];
    }
}
