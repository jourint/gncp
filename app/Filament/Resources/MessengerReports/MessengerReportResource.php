<?php

namespace App\Filament\Resources\MessengerReports;

use App\Filament\Resources\MessengerReports\Pages\CreateMessengerReport;
use App\Filament\Resources\MessengerReports\Pages\EditMessengerReport;
use App\Filament\Resources\MessengerReports\Pages\ListMessengerReports;
use App\Filament\Resources\MessengerReports\Schemas\MessengerReportForm;
use App\Filament\Resources\MessengerReports\Tables\MessengerReportsTable;
use App\Models\MessengerReport;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessengerReportResource extends Resource
{
    protected static ?string $model = MessengerReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Мессенджер';
    protected static ?int $navigationSort = 4;
    protected static ?string $label = 'Рассылка отчетов';
    protected static ?string $pluralLabel = 'Рассылки отчетов';
    protected static ?string $navigationLabel = 'Рассылки отчетов';

    public static function form(Schema $schema): Schema
    {
        return MessengerReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessengerReportsTable::configure($table);
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
            'index' => ListMessengerReports::route('/'),
            'create' => CreateMessengerReport::route('/create'),
            'edit' => EditMessengerReport::route('/{record}/edit'),
        ];
    }
}
