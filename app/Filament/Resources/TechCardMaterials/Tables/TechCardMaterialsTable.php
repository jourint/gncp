<?php

namespace App\Filament\Resources\TechCardMaterials\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TechCardMaterialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shoeTechCard.name')
                    ->label('Техкарта')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('material.name')
                    ->label('Материал')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Расход')
                    ->alignCenter()
                    ->formatStateUsing(function ($state, $record) {
                        // Отладочный хак: если всё равно 'ед.', расскоментируй строку ниже, 
                        // чтобы увидеть, где именно пусто в логах или на экране
                        // dd($record->material?->materialType?->unit); 

                        $unitName = $record->material?->materialType?->unit?->name;

                        return $state . ' ' . ($unitName ?? 'ед.');
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
