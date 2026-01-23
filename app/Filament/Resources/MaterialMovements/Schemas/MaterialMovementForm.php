<?php

namespace App\Filament\Resources\MaterialMovements\Schemas;

use App\Enums\MovementType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\MorphToSelect;
use Filament\Schemas\Schema;
use App\Models\Material;
use App\Models\ShoeSoleItem;

class MaterialMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                MorphToSelect::make('movable')
                    ->label('Объект склада')
                    ->types([
                        // Материалы
                        MorphToSelect\Type::make(Material::class)
                            ->titleAttribute('name') // Обязательный атрибут для поиска
                            ->label('Материал')
                            ->getOptionLabelFromRecordUsing(
                                fn(Material $record) => $record->fullName
                            ),

                        // Подошвы
                        MorphToSelect\Type::make(ShoeSoleItem::class)
                            ->titleAttribute('name') // Обязательный атрибут для поиска
                            ->label('Подошва')
                            ->getOptionLabelFromRecordUsing(
                                fn(ShoeSoleItem $record) => "{$record->shoeSole?->fullName} [{$record->size?->name}]"
                            ),
                    ])
                    ->required()
                    ->searchable()
                    ->preload()
                    ->dehydrated(true),

                Select::make('type')
                    ->label('Тип операции')
                    ->options(
                        collect(MovementType::cases())
                            ->mapWithKeys(fn(MovementType $type) => [$type->value => $type->label()])
                            ->toArray()
                    )
                    ->required()
                    ->native(false),

                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->required(),

                Textarea::make('description')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
