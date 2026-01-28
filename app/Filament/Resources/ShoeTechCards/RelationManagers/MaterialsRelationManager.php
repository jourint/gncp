<?php

namespace App\Filament\Resources\ShoeTechCards\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Material;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'techCardMaterials';
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
                        modifyQueryUsing: fn($query) => $query->with(['color', 'materialType'])
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                    ->searchable(['name'])
                    ->preload()
                    ->required()
                    ->live(), // Оставляем live, чтобы суффикс обновлялся при выборе материала

                TextInput::make('quantity')
                    ->label('Расход на 1 пару')
                    ->numeric()
                    ->step(0.5)
                    ->required()
                    ->suffix(function (callable $get) {
                        $materialId = $get('material_id');
                        if (!$materialId) return '';
                        $material = Material::find($materialId);
                        return $material?->materialType?->unit_id?->getLabel() ?? 'ед.';
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['material.color', 'material.materialType']))
            ->recordTitleAttribute('material.name')
            ->columns([
                TextColumn::make('material.name')
                    ->label('Материал')
                    ->formatStateUsing(fn($record) => $record->material->fullName),

                TextColumn::make('quantity')
                    ->label('Расход')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $state . ' ' . ($record->material?->materialType?->unit_id?->getLabel() ?? 'ед.')
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
