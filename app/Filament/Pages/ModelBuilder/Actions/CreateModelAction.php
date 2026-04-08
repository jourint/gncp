<?php

namespace App\Filament\Pages\ModelBuilder\Actions;

use App\Models\ShoeModel;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class CreateModelAction
{
    public static function make(): CreateAction
    {
        return CreateAction::make('createModel')
            ->label('Новая модель')
            ->model(ShoeModel::class)
            ->modalWidth('md')
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
            ])
            ->after(function (ShoeModel $record, $livewire) {
                // Сразу открываем созданную модель для работы
                $livewire->activeModelId = $record->id;
            });
    }
}
