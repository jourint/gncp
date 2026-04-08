<?php

namespace App\Filament\Pages\ModelBuilder\Schemes;

use App\Models\Size;
use App\Models\Workflow;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ShoeModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Блок основной информации
                Section::make('Общая информация')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Название модели')
                                    ->required()
                                    ->maxLength(50),

                                Select::make('shoe_type_id')
                                    ->label('Тип обуви')
                                    ->relationship('shoeType', 'name')
                                    ->required()
                                    ->preload(),

                                Toggle::make('is_active')
                                    ->label('Модель активна')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),

                // Блок коэффициентов
                Section::make('Коэффициенты сложности (1.00 = 100%)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('price_coeff_cutting')
                                    ->label('Закройка')
                                    ->numeric()
                                    ->default(1.00)
                                    ->prefix('x'),

                                TextInput::make('price_coeff_sewing')
                                    ->label('Пошив')
                                    ->numeric()
                                    ->default(1.00)
                                    ->prefix('x'),

                                TextInput::make('price_coeff_shoemaker')
                                    ->label('Сапожник')
                                    ->numeric()
                                    ->default(1.00)
                                    ->prefix('x'),
                            ]),
                    ]),

                Textarea::make('description')
                    ->label('Комментарий')
                    ->maxLength(255)
                    ->columnSpanFull(),

                // Технические параметры
                Grid::make(3)
                    ->schema([
                        Select::make('shoe_insole_id')
                            ->label('Тип стельки')
                            ->relationship('shoeInsole', 'name')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->fullName)
                            ->placeholder('Без стельки')
                            ->searchable()
                            ->preload(),

                        Select::make('counter_id')
                            ->label('Тип задника')
                            ->relationship('counter', 'name')
                            ->placeholder('Без задника')
                            ->searchable()
                            ->preload(),

                        Select::make('puff_id')
                            ->label('Тип подноска')
                            ->relationship('puff', 'name')
                            ->placeholder('Без подноска')
                            ->searchable()
                            ->preload(),
                    ]),

                // Сетки и процессы
                Grid::make(2)
                    ->schema([
                        Select::make('available_sizes')
                            ->label('Доступные размеры')
                            ->multiple()
                            ->options(Size::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('workflows')
                            ->label('Дополнительные процессы')
                            ->multiple()
                            ->options(Workflow::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
