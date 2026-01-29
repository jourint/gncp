<?php

namespace App\Filament\Resources\ShoeSoles;

use App\Filament\Resources\ShoeSoles\Pages\CreateShoeSole;
use App\Filament\Resources\ShoeSoles\Pages\EditShoeSole;
use App\Filament\Resources\ShoeSoles\Pages\ListShoeSoles;
use App\Filament\Resources\ShoeSoles\Schemas\ShoeSoleForm;
use App\Filament\Resources\ShoeSoles\Tables\ShoeSolesTable;
use App\Models\ShoeSole;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoeSoleResource extends Resource
{
    protected static ?string $model = ShoeSole::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static string |UnitEnum|null $navigationGroup = 'Справочники';
    protected static ?int $navigationSort = 8;
    protected static ?string $label = 'Подошва';
    protected static ?string $pluralLabel = 'Подошвы';
    protected static ?string $navigationLabel = 'Подошвa';

    public static function form(Schema $schema): Schema
    {
        return ShoeSoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoeSolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShoeSoles::route('/'),
            'create' => CreateShoeSole::route('/create'),
            'edit' => EditShoeSole::route('/{record}/edit'),
        ];
    }
}
