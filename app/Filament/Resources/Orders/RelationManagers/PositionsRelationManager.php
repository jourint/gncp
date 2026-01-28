<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ColorColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use App\Models\Size;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use App\Models\ShoeTechCard;
use App\Filament\Actions\ReplicateOrderPositionAction;

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

                Select::make('material_lining_id')
                    ->label('Подкладка')
                    ->relationship(
                        name: 'materialLining',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn($query) => $query->with('color')
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn($record) => $record->fullName
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('size_id')
                    ->label('Размер')
                    ->placeholder('Выберите техкарту')
                    ->options(function (callable $get) {
                        $techCardId = $get('shoe_tech_card_id');
                        if (!$techCardId) return [];
                        $techCard = ShoeTechCard::with('shoeModel')->find($techCardId);
                        $availableSizes = $techCard?->shoeModel?->available_sizes ?? [];
                        return Size::whereIn('id', $availableSizes)->pluck('name', 'id');
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
            ->modifyQueryUsing(fn($query) => $query->with([
                'shoeTechCard.color',
                'materialLining.color',
                'size' // Теперь подгружаем размер как связь
            ]))
            ->recordTitleAttribute('shoe_tech_card_id')
            ->columns([
                ColorColumn::make('shoeTechCard.color.hex')
                    ->label('Цвет'),

                TextColumn::make('shoeTechCard.name')
                    ->label('Спецификация'),

                TextColumn::make('materialLining.name')
                    ->label('Подкладка')
                    ->formatStateUsing(fn($record) => $record->materialLining?->fullName ?? 'н/д'),

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
                //    AssociateAction::make(),
            ])
            ->recordActions([
                ReplicateOrderPositionAction::make(),
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
