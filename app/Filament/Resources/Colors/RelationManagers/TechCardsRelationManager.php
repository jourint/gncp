<?php

namespace App\Filament\Resources\Colors\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\ShoeInsole;
use App\Models\ShoeModel;
use App\Models\ShoeSole;
use App\Models\MaterialTexture;

class TechCardsRelationManager extends RelationManager
{
    protected static string $relationship = 'shoeTechCards';
    protected static ?string $title = 'Технические карты для цвета';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->disabled()
                    ->maxLength(150),

                Select::make('shoe_model_id')
                    ->label('Модель')
                    ->options(ShoeModel::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('material_texture_id')
                    ->label('Текстура')
                    ->options(MaterialTexture::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Select::make('shoe_sole_id')
                    ->label('Подошва')
                    ->options(ShoeSole::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('shoe_insole_id')
                    ->label('Стелька')
                    ->options(ShoeInsole::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Toggle::make('is_active')
                    ->label('Активна')
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

                TextColumn::make('shoeModel.name')
                    ->label('Модель'),

                TextColumn::make('materialTexture.name')
                    ->label('Текстура')
                    ->state(function ($record) {
                        return MaterialTexture::find($record->material_texture_id)?->name ?? 'Не указано';
                    }),

                TextColumn::make('shoeSole.name')
                    ->label('Подошва'),

                TextColumn::make('shoeInsole.name')
                    ->label('Стелька'),

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
