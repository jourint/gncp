<?php

namespace App\Filament\Resources\ShoeTechCards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Actions\ReplicateTechCardAction;

class ShoeTechCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Фото')
                    ->square()
                    ->width(50),

                TextColumn::make('name')
                    ->label('Название тех-карты')
                    ->description(fn($record) => "Цвет: " . ($record->color?->name ?? 'н/д'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shoeSole.name')
                    ->label('Подошва')
                    ->description(fn($record) => "Цвет: " . ($record->shoeSole?->color?->name ?? 'н/д'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('shoe_model_id')
                    ->label('Фильтр по модели')
                    ->relationship('shoeModel', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('color_id')
                    ->label('Цвет')
                    ->relationship('color', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateTechCardAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
