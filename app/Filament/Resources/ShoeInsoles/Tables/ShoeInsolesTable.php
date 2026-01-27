<?php

namespace App\Filament\Resources\ShoeInsoles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Enums\InsolesType;

class ShoeInsolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Название стельки')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Тип стельки')
                    ->badge()
                    ->color(fn(InsolesType $state): string => match ($state) {
                        InsolesType::Inset => 'gray',
                        InsolesType::Fitting => 'warning',
                        InsolesType::HalfInsole => 'success',
                    }),

                TextColumn::make('is_soft_texon')
                    ->label('Тип тексона')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Мягкий' : 'Жесткий')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'gray' : 'warning'),

                IconColumn::make('has_egg')
                    ->label('Требуется накладка')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean(),

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
