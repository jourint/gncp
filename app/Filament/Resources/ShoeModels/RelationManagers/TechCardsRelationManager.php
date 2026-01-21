<?php

namespace App\Filament\Resources\ShoeModels\RelationManagers;

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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use App\Filament\Actions\ReplicateTechCardAction;
use Filament\Actions\Action;
use App\Models\ShoeTechCard;


class TechCardsRelationManager extends RelationManager
{
    protected static string $relationship = 'techCards';
    protected static ?string $title = 'Тех-карты модели';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('color_id')
                    ->label('Цвет модели')
                    ->relationship('color', 'name')
                    ->required()
                    ->preload(),

                Select::make('material_texture_id')
                    ->label('Текстура')
                    ->relationship('texture', 'name')
                    ->preload(),

                Select::make('shoe_sole_id')
                    ->label('Подошва')
                    ->relationship('shoeSole', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} (Цвет: {$record->color?->name})")
                    ->required()
                    ->preload(),

                Select::make('shoe_insole_id')
                    ->label('Стелька')
                    ->relationship('shoeInsole', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->getDisplayNameAttribute())
                    ->required()
                    ->preload(),

                FileUpload::make('image_path')
                    ->label('Фото этого исполнения')
                    ->image()
                    ->directory('tech-cards')
                    ->columnSpanFull(),

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
                ImageColumn::make('image_path')
                    ->label('Фото')
                    ->square(),

                TextColumn::make('name')
                    ->label('Спецификация')
                    ->description(fn($record) => "Подошва: {$record->shoeSole?->name}"),

                TextColumn::make('color.name')
                    ->label('Цвет')
                    ->badge(),

                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                //    AssociateAction::make(),
            ])
            ->recordActions([
                ReplicateTechCardAction::make(),
                // Кнопка для перехода в "полный" интерфейс техкарты
                Action::make('edit_full')
                    ->label('Состав')
                    ->icon('heroicon-m-beaker') // Иконка колбы или настроек
                    ->color('warning')
                    ->url(
                        fn(ShoeTechCard $record): string =>
                        \App\Filament\Resources\ShoeTechCards\ShoeTechCardResource::getUrl('edit', ['record' => $record])
                    ),
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
