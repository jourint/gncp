<?php

namespace App\Filament\Resources\OrderEmployees;

use App\Filament\Resources\OrderEmployees\Pages\CreateOrderEmployee;
use App\Filament\Resources\OrderEmployees\Pages\EditOrderEmployee;
use App\Filament\Resources\OrderEmployees\Pages\ListOrderEmployees;
use App\Filament\Resources\OrderEmployees\Schemas\OrderEmployeeForm;
use App\Filament\Resources\OrderEmployees\Tables\OrderEmployeesTable;
use App\Models\OrderEmployee;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderEmployeeResource extends Resource
{
    protected static ?string $model = OrderEmployee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Производство';
    protected static ?int $navigationSort = 5;
    protected static ?string $label = 'Задача сотрудника';
    protected static ?string $pluralLabel = 'Задачи сотрудников';
    protected static ?string $navigationLabel = 'Задачи сотрудников';

    public static function form(Schema $schema): Schema
    {
        return OrderEmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderEmployeesTable::configure($table);
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
            'index' => ListOrderEmployees::route('/'),
            'create' => CreateOrderEmployee::route('/create'),
            'edit' => EditOrderEmployee::route('/{record}/edit'),
        ];
    }
}
