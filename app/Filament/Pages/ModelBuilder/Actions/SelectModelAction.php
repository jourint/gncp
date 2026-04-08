<?php

namespace App\Filament\Pages\ModelBuilder\Actions;

use App\Models\ShoeModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class SelectModelAction
{
    public static function make(): Action
    {
        return Action::make('selectModel')
            ->label('Найти модель')
            ->icon('heroicon-m-magnifying-glass')
            ->color('gray')
            ->modalWidth('md')
            ->schema([
                Select::make('model_id')
                    ->label('Выберите модель для редактирования')
                    ->options(ShoeModel::pluck('name', 'id'))
                    ->searchable()
                    ->required()
            ])
            ->action(function (array $data, $livewire) {
                // dd($data['model_id']);
                $livewire->loadModel($data['model_id']);
                $livewire->activeModelId = $data['model_id'];
            });
    }
}
