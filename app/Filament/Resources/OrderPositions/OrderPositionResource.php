<?php

namespace App\Filament\Resources\OrderPositions;

use App\Filament\Resources\OrderPositions\Pages\CreateOrderPosition;
use App\Filament\Resources\OrderPositions\Pages\EditOrderPosition;
use App\Filament\Resources\OrderPositions\Pages\ListOrderPositions;
use App\Filament\Resources\OrderPositions\Schemas\OrderPositionForm;
use App\Filament\Resources\OrderPositions\Tables\OrderPositionsTable;
use App\Models\OrderPosition;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderPositionResource extends Resource
{
    protected static ?string $model = OrderPosition::class;

    // hidden from navigation
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Производство';
    protected static ?int $navigationSort = 4;
    protected static ?string $label = 'Позиция заказа';
    protected static ?string $pluralLabel = 'Позиции заказов';
    protected static ?string $navigationLabel = 'Позиции заказов';

    public static function form(Schema $schema): Schema
    {
        return OrderPositionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderPositionsTable::configure($table);
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
            'index' => ListOrderPositions::route('/'),
            'create' => CreateOrderPosition::route('/create'),
            'edit' => EditOrderPosition::route('/{record}/edit'),
        ];
    }
}
