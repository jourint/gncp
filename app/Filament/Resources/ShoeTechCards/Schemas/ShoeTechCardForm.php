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
                        Grid::make(3)
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

                                Select::make('material_texture_id')
                                    ->label('Текстура материала')
                                    ->relationship('texture', 'name') // используем имя связи из вашей модели
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

                Section::make('Комплектующие')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('shoe_sole_id')
                                    ->label('Подошва')
                                    ->relationship('shoeSole', 'name')
                                    ->getOptionLabelFromRecordUsing(
                                        fn($record) =>
                                        "{$record->name} (Цвет: {$record->color?->name})"
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('shoe_insole_id')
                                    ->label('Стелька')
                                    ->relationship('shoeInsole', 'name')
                                    ->getOptionLabelFromRecordUsing(
                                        fn($record) => $record->getDisplayNameAttribute()
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
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
            ]);
    }
}
