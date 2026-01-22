<?php

namespace App\Filament\Resources\Employees\RelationManagers;

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
use App\Models\OrderPosition;
use App\Models\Size;

class OrderEmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'orderEmployees';
    protected static ?string $title = 'Назначенные работы';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_position_id')
                    ->label('Позиция заказа')
                    ->options(
                        OrderPosition::with([
                            'shoeTechCard.shoeModel:id,name',
                            'size:id,name'
                        ])
                            ->get()
                            ->mapWithKeys(function ($op) {
                                $model = $op->shoeTechCard->shoeModel ?? null;
                                $size = $op->size ?? null;

                                $label = trim(($model?->name ?? 'Неизвестная модель') . ' - ' . ($size?->name ?? 'Размер: ' . $op->size_id));

                                return [$op->id => $label];
                            })
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->default(1)
                    ->required(),

                TextInput::make('price_per_pair')
                    ->label('Цена за пару')
                    ->numeric()
                    ->step(0.5)
                    ->default(0.00),

                Toggle::make('is_paid')
                    ->label('Оплачено')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('quantity')
            ->columns([
                TextColumn::make('order.id')
                    ->label('Заказ')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('order.started_at')
                    ->label('Дата заказа')
                    ->date()
                    ->sortable(),

                TextColumn::make('orderPosition.shoeTechCard.shoeModel.name')
                    ->label('Модель'),

                TextColumn::make('orderPosition.size_id')
                    ->label('Размер')
                    ->state(function ($record) {
                        return optional(Size::find($record->orderPosition->size_id))->name ?? $record->orderPosition->size_id;
                    }),

                TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('price_per_pair')
                    ->label('Цена за пару')
                    ->money('UAH')
                    ->sortable(),

                IconColumn::make('is_paid')
                    ->label('Оплачено')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_paid')
                    ->label('Оплачено'),
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
