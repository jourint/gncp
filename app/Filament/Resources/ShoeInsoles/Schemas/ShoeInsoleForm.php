<?php

namespace App\Filament\Resources\ShoeInsoles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use App\Enums\InsolesType;
use Dom\Text;

class ShoeInsoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Код резака')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Например: Астра, 513, 168, Люси'),

                Select::make('type')
                    ->label('Тип стельки')
                    ->options(InsolesType::class)
                    ->required()
                    ->native(false),

                Grid::make(3)
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_soft_texon')
                            ->label('Мягкий тексон')
                            ->default(false)
                            ->helperText('Да - мягкий, нет - жёсткий'),

                        Toggle::make('has_egg')
                            ->label('Накладка на пятку')
                            ->default(false)
                            ->helperText('Нужны ли яичка?'),

                        Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                    ]),

                Html::make('instructions')
                    ->content('<div class="text-sm text-gray-500 p-2">*Код резака + тип стельки + вид тексона = уникальная еденица стельки. Дисплей: Астра Вкладная (мягкий)</div>')
                    ->columnSpanFull(), // на всю ширину
            ]);
    }
}
