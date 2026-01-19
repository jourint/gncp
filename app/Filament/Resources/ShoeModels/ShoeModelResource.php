<?php

namespace App\Filament\Resources\ShoeModels;

use App\Filament\Resources\ShoeModels\Pages\CreateShoeModel;
use App\Filament\Resources\ShoeModels\Pages\EditShoeModel;
use App\Filament\Resources\ShoeModels\Pages\ListShoeModels;
use App\Filament\Resources\ShoeModels\Schemas\ShoeModelForm;
use App\Filament\Resources\ShoeModels\Tables\ShoeModelsTable;
use App\Models\ShoeModel;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoeModelResource extends Resource
{
    protected static ?string $model = ShoeModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string |UnitEnum|null $navigationGroup = 'Производство';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Модель обуви';
    protected static ?string $pluralLabel = 'Модели обуви';
    protected static ?string $navigationLabel = 'Модели обуви';

    public static function form(Schema $schema): Schema
    {
        return ShoeModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoeModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TechCardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShoeModels::route('/'),
            'create' => CreateShoeModel::route('/create'),
            'edit' => EditShoeModel::route('/{record}/edit'),
        ];
    }
}
