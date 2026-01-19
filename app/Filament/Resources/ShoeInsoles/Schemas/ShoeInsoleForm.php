<?php

namespace App\Filament\Resources\ShoeInsoles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Material;
use App\Models\Unit;

class ShoeInsoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Код резака / Название')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Например: IN-42-Standard'),

                Toggle::make('is_black')
                    ->label('Черная расцветка')
                    ->onIcon('heroicon-m-moon')
                    ->offIcon('heroicon-m-sun')
                    ->default(true)
                    ->helperText('Если выключено — стелька считается светлой/бежевой'),

                Toggle::make('is_active')
                    ->label('Активна')
                    ->default(true),

                Repeater::make('tech_card')
                    ->label('Технологическая карта (Материалы)')
                    ->schema([
                        Select::make('material_id')
                            ->label('Материал')
                            ->options(Material::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if (blank($state)) {
                                    $set('material_unit', null);
                                    return;
                                }

                                // Используем вашу новую связь unit() через тип материала
                                $material = Material::find($state);
                                $unitId = $material?->materialType?->unit_id;

                                $set('material_unit', $unitId);
                            })
                            ->columnSpan(2),

                        Select::make('material_unit')
                            ->label('Ед. изм.')
                            ->options(Unit::all()->pluck('name', 'id'))
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),

                        TextInput::make('count')
                            ->label('Расход')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->itemLabel(
                        fn(array $state): ?string =>
                        Material::find($state['material_id'] ?? null)?->name ?? 'Новый компонент'
                    )
                    ->collapsible()
                    ->defaultItems(0)
                    ->columnSpanFull(),
            ]);
    }
}
