<?php

namespace App\Filament\Resources\MaterialMovements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Material;
use App\Enums\MovementType;
use App\Models\ShoeSoleItem;

class MaterialMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('movable')
                    ->label('Объект склада')
                    ->formatStateUsing(function ($record) {
                        $movable = $record->movable;
                        if ($movable instanceof Material) {
                            return "📦 " . $movable->fullName;
                        }
                        if ($movable instanceof ShoeSoleItem) {
                            return "👟 {$movable->shoeSole->fullName} | {$movable->size?->name}";
                        }
                        return $movable?->name ?? 'Неизвестный объект';
                    }),

                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    // Убираем типизацию string или меняем на MovementType
                    ->color(fn($state): string => match ($state) {
                        MovementType::Income, 'income' => 'success',
                        MovementType::Outcome, 'outcome' => 'info',
                        MovementType::WriteOff, 'write-off' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state): string => match ($state) {
                        MovementType::Income, 'income' => 'Приход',
                        MovementType::Outcome, 'outcome' => 'Расход',
                        MovementType::WriteOff, 'write-off' => 'Списание',
                        default => $state instanceof MovementType ? $state->name : $state,
                    }),

                TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('description')
                    ->label('Комментарий')
                    ->limit(50),

                TextColumn::make('user.name')
                    ->label('Оператор')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('сreated_at')
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
