<?php

namespace App\Filament\Resources\ShoeModelPatterns;

use App\Filament\Resources\ShoeModelPatterns\Pages\CreateShoeModelPattern;
use App\Filament\Resources\ShoeModelPatterns\Pages\EditShoeModelPattern;
use App\Filament\Resources\ShoeModelPatterns\Pages\ListShoeModelPatterns;
use App\Filament\Resources\ShoeModelPatterns\Schemas\ShoeModelPatternForm;
use App\Filament\Resources\ShoeModelPatterns\Tables\ShoeModelPatternsTable;
use App\Models\ShoeModelPattern;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoeModelPatternResource extends Resource
{
    protected static ?string $model = ShoeModelPattern::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Производство';
    protected static ?int $navigationSort = 7;
    protected static ?string $label = 'Лекала обуви';
    protected static ?string $pluralLabel = 'Лекала обуви';
    protected static ?string $navigationLabel = 'Лекала обуви';

    public static function form(Schema $schema): Schema
    {
        return ShoeModelPatternForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoeModelPatternsTable::configure($table);
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
            'index' => ListShoeModelPatterns::route('/'),
            'create' => CreateShoeModelPattern::route('/create'),
            'edit' => EditShoeModelPattern::route('/{record}/edit'),
        ];
    }
}
