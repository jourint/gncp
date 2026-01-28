<?php

namespace App\Filament\Resources\ShoeSoleItems;

use App\Filament\Resources\ShoeSoleItems\Pages\CreateShoeSoleItem;
use App\Filament\Resources\ShoeSoleItems\Pages\EditShoeSoleItem;
use App\Filament\Resources\ShoeSoleItems\Pages\ListShoeSoleItems;
use App\Filament\Resources\ShoeSoleItems\Schemas\ShoeSoleItemForm;
use App\Filament\Resources\ShoeSoleItems\Tables\ShoeSoleItemsTable;
use App\Models\ShoeSoleItem;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoeSoleItemResource extends Resource
{
    protected static ?string $model = ShoeSoleItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Склад';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Подошва';
    protected static ?string $pluralLabel = 'Подошвы';
    protected static ?string $navigationLabel = 'Подошва на складе';

    public static function form(Schema $schema): Schema
    {
        return ShoeSoleItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoeSoleItemsTable::configure($table);
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
            'index' => ListShoeSoleItems::route('/'),
            'create' => CreateShoeSoleItem::route('/create'),
            'edit' => EditShoeSoleItem::route('/{record}/edit'),
        ];
    }
}
