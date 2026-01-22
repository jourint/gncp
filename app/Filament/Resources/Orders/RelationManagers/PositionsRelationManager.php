<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use App\Models\Size;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use App\Models\ShoeTechCard;

class PositionsRelationManager extends RelationManager
{
    protected static string $relationship = 'positions';
    protected static ?string $title = 'Позиции заказа';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('shoe_tech_card_id')
                    ->label('Техкарта (Модель/Цвет)')
                    ->relationship('shoeTechCard', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->columnSpan(2),

                Select::make('size_id')
                    ->label('Размер')
                    ->placeholder('Выберите техкарту')
                    ->options(function (callable $get) {
                        $techCardId = $get('shoe_tech_card_id');

                        if (!$techCardId) return [];

                        $techCard = ShoeTechCard::with('shoeModel')->find($techCardId);
                        $availableSizes = $techCard?->shoeModel?->available_sizes ?? [];

                        return Size::whereIn('id', $availableSizes)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->columnSpan(1),

                TextInput::make('quantity')
                    ->label('Пар')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->columnSpan(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('shoe_tech_card_id')
            ->columns([
                TextColumn::make('shoeTechCard.name')
                    ->label('Спецификация')
                    ->description(fn($record) => "Модель: " . ($record->shoeTechCard?->shoeModel?->name ?? 'н/д')),

                TextColumn::make('size_id')
                    ->label('Размер')
                    ->formatStateUsing(fn($state) => Size::find($state)?->name ?? $state)
                    ->alignCenter(),

                TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить позицию заказа'),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
