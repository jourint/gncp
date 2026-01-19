<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\JobPosition;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Сотрудник')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('job_position_id')
                    ->label('Цех')
                    ->formatStateUsing(fn($state) => JobPosition::find($state)?->value ?? 'Не указан')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        1 => 'info',
                        2 => 'warning',
                        3 => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->formatStateUsing(
                        fn(string $state): string =>
                        preg_replace('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1 ($2) $3-$4-$5', $state)
                    ),

                TextColumn::make('skill_level')
                    ->label('Квалиф.')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean(),

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
