<?php

namespace App\Filament\Resources\ShoeTechCards\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShoeTechCardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Визуальное исполнение')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('shoe_model_id')
                                    ->label('Модель обуви')
                                    ->relationship('shoeModel', 'name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->fullName)
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('color_id')
                                    ->label('Цвет')
                                    ->relationship(
                                        name: 'color',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn($query) => $query->active()
                                    )
                                    //    ->getOptionLabelFromRecordUsing(fn($record) => $record->fullName)
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        TextInput::make('name')
                            ->label('Название спецификации')
                            ->placeholder('Сформируется автоматически после сохранения')
                            ->disabled()
                            ->dehydrated(false) // не отправляем на сервер, так как модель сама его создаст
                            ->columnSpanFull(),
                    ]),

                Section::make('Медиа и Статус')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Фото модели')
                                    ->image()
                                    ->directory('tech-cards'),

                                Toggle::make('is_active')
                                    ->label('Карта активна')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),

                // Компоненты комплектующих на всю ширину
                Grid::make(3)
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('shoe_sole_id')
                            ->label('Подошва')
                            ->relationship(
                                name: 'shoeSole',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->with('color')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->fullName
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('material_id')
                            ->label('Материал основной')
                            ->relationship(
                                name: 'material',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->with('color')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->fullName
                            )
                            ->searchable()
                            //    ->preload()
                            ->required(),

                        Select::make('material_two_id')
                            ->label('Доп. материал')
                            ->relationship('materialTwo', 'name')
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->fullName
                            )
                            //    ->preload()
                            ->searchable(),
                    ]),

            ]);
    }
}
