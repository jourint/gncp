<?php

namespace App\Filament\Resources\ShoeSoles\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Size;
use App\Filament\Actions\ReplicateShoeSoleItemToNextSizeAction;
use App\Filament\Actions\CreateAllSizesForShoeSoleAction;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'shoeSoleItems';
    protected static ?string $title = 'Размерная сетка';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('size_id')
                    ->label('Размер')
                    ->options(Size::all()->pluck('name', 'id'))
                    ->required()
                    // Важно: distinct() здесь работает в контексте текущей подошвы
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->searchable(),

                TextInput::make('stock_quantity')
                    ->label('Количество на складе')
                    ->numeric()
                    ->default(0)
                    ->prefix('пар'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('size_id')
            ->columns([
                TextColumn::make('size_id')
                    ->label('Размер')
                    ->formatStateUsing(fn($state) => Size::find($state)?->name ?? $state)
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Остаток')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->sortable(),
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
            ->headerActions([
                CreateAction::make(),
                CreateAllSizesForShoeSoleAction::make()->record($this->getOwnerRecord()),
                //    AssociateAction::make(),
            ])
            ->recordActions([
                ReplicateShoeSoleItemToNextSizeAction::make(),
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
