<?php

namespace App\Filament\Resources\ShoeTechCards\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';

    protected static ?string $title = 'Состав (Материалы)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('material_id')
                    ->label('Материал')
                    ->relationship(
                        name: 'material',
                        titleAttribute: 'name',
                        // Добавляем жадную загрузку цвета, чтобы не было 100500 запросов к БД
                        modifyQueryUsing: fn($query) => $query->with('color')
                    )
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        // Формируем строку: Название (Цвет)
                        $colorName = $record->color?->name;
                        return $colorName ? "{$record->name} ({$colorName})" : $record->name;
                    })
                    ->searchable(['name']) // Можно добавить и 'color.name' в массив, если версия Filament позволяет
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn($set) => $set('quantity', null)),

                TextInput::make('quantity')
                    ->label('Расход на 1 пару')
                    ->numeric()
                    ->step(1)
                    ->required()
                    ->suffix(function (callable $get) {
                        $materialId = $get('material_id');
                        if (!$materialId) return '';

                        $material = \App\Models\Material::with('materialType.unit')->find($materialId);
                        return $material?->materialType?->unit?->name ?? 'ед.';
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('material.name')
            ->columns([
                TextColumn::make('material.name')
                    ->label('Материал'),

                TextColumn::make('quantity')
                    ->label('Расход')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $state . ' ' . ($record->material?->materialType?->unit?->name ?? 'ед.')
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                //    AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                //    DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
