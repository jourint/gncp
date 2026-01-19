<?php

namespace App\Filament\Resources\ShoeTechCards;

use App\Filament\Resources\ShoeTechCards\Pages\CreateShoeTechCard;
use App\Filament\Resources\ShoeTechCards\Pages\EditShoeTechCard;
use App\Filament\Resources\ShoeTechCards\Pages\ListShoeTechCards;
use App\Filament\Resources\ShoeTechCards\Schemas\ShoeTechCardForm;
use App\Filament\Resources\ShoeTechCards\Tables\ShoeTechCardsTable;
use App\Models\ShoeTechCard;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoeTechCardResource extends Resource
{
    protected static ?string $model = ShoeTechCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Производство';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Тех-карта модели';
    protected static ?string $pluralLabel = 'Тех-карты моделей';
    protected static ?string $navigationLabel = 'Тех-карты моделей';

    public static function form(Schema $schema): Schema
    {
        return ShoeTechCardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoeTechCardsTable::configure($table);
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
            'index' => ListShoeTechCards::route('/'),
            'create' => CreateShoeTechCard::route('/create'),
            'edit' => EditShoeTechCard::route('/{record}/edit'),
        ];
    }
}
