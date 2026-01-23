<?php

namespace App\Filament\Resources\ShoeTechCards\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\ShoeModel;
use App\Models\ShoeSole;
use App\Models\ShoeInsole;

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
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->shoeType?->name})")
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('color_id')
                                    ->label('Цвет')
                                    ->relationship('color', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        // Поле name теперь только для чтения, оно обновится после сохранения
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
                    ->columns(3) // 3 колонки в строке
                    ->columnSpanFull() // Занимает всю ширину
                    ->schema([
                        Select::make('shoe_sole_id')
                            ->label('Подошва')
                            ->relationship('shoeSole', 'name')
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->fullName
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('material_id')
                            ->label('Материал основной')
                            ->relationship('material', 'name')
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
