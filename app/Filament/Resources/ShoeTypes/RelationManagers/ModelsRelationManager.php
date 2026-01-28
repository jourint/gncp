<?php

namespace App\Filament\Resources\ShoeTypes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Puff;
use App\Models\Counter;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'shoeModels';
    protected static ?string $title = 'Модели обуви';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(50),

                Textarea::make('description')
                    ->label('Описание')
                    ->maxLength(2000)
                    ->columnSpanFull(),

                Select::make('puff_id')
                    ->label('Подносок')
                    ->options(Puff::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Select::make('counter_id')
                    ->label('Задник')
                    ->options(Counter::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50),

                TextColumn::make('puff.name')
                    ->label('Подносок'),

                TextColumn::make('counter.name')
                    ->label('Задник'),

                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Активен'),
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
            ->paginationPageOptions([10, 25, 50, 'all']);
    }
}
