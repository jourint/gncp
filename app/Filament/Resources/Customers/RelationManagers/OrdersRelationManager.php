<?php

namespace App\Filament\Resources\Customers\RelationManagers;

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
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Enums\OrderStatus;


class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'Заказы клиента';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('started_at')
                    ->label('Дата начала производства')
                    ->required()
                    ->default(now()),

                Select::make('status')
                    ->label('Статус заказа')
                    ->options(OrderStatus::class)
                    ->default(OrderStatus::Pending->value)
                    ->required(),

                Textarea::make('comment')
                    ->label('Комментарий')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('started_at')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Дата начала')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),

                TextColumn::make('comment')
                    ->limit(50)
                    ->label('Комментарий'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),
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
            ->defaultSort('started_at', 'desc');
    }
}
