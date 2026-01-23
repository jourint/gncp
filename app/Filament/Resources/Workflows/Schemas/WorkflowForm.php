<?php

namespace App\Filament\Resources\Workflows\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkflowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название процесса')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                TextInput::make('price')
                    ->label('Стоимость процесса')
                    ->numeric()
                    ->prefix('₴')
                    ->default(1.00),

                Textarea::make('description')
                    ->label('Описание')
                    ->maxLength(255)
                    ->columnSpanFull(), // Растягиваем на всю ширину

                Toggle::make('is_active')
                    ->label('Доступен для моделей')
                    ->default(true),
            ]);
    }
}
