<?php

namespace App\Filament\Resources\Materials\RelationManagers;

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
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Enums\MovementType;
use App\Models\User;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';
    protected static ?string $title = 'Движения материала';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Тип')
                    ->options(collect(MovementType::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()]))
                    ->required(),

                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                /* Select::make('user_id')
                    ->label('Пользователь')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),*/

                Textarea::make('description')
                    ->label('Описание')
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn(MovementType $state): string => match ($state) {
                        MovementType::Income => 'success',
                        MovementType::Outcome => 'danger',
                        MovementType::WriteOff => 'warning',
                    })
                    ->formatStateUsing(fn(MovementType $state): string => $state->label()),

                TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Пользователь'),

                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50),

                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(collect(MovementType::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])),
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
            ])
            ->paginationPageOptions([10, 25, 50, 'all'])
            ->defaultSort('created_at', 'desc');
    }
}
