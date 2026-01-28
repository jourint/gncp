<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Enums\JobPosition;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('ФИО сотрудника')
                    ->required()
                    ->maxLength(100),

                Select::make('job_position_id')
                    ->label('Цех / Должность')
                    ->options(JobPosition::class)
                    ->default(0)
                    ->selectablePlaceholder(false)
                    ->required(),

                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->required()
                    ->unique('employees', 'phone', ignoreRecord: true)
                    ->mask('+38 (999) 999-99-99')
                    ->stripCharacters([' ', '(', ')', '-', '+'])
                    ->placeholder('+38 (___) ___-__-__'),

                TextInput::make('skill_level')
                    ->label('Коэффициент навыков')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->maxValue(1.99)
                    ->default(1.00)
                    ->helperText('Используется для распределения пар из заказа'),

                Toggle::make('is_active')
                    ->label('Работает в штате')
                    ->default(true),
            ]);
    }
}
