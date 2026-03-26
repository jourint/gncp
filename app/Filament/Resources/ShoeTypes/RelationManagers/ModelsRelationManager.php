<?php

namespace App\Filament\Resources\ShoeTypes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Puff;
use App\Models\Counter;
use App\Models\Workflow;
use Filament\Schemas\Components\Grid;
use App\Models\Size;
use Filament\Actions\Action;
use App\Models\ShoeModel;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'shoeModels';
    protected static ?string $title = 'Модели обуви';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Блок основной информации
                Section::make('Общая информация')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Название модели')
                                    ->required()
                                    ->maxLength(50),

                                Toggle::make('is_active')
                                    ->label('Модель активна')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),

                // Блок коэффициентов (сложность модели)
                Section::make('Коэффициенты сложности (1.00 = 100%, 1.12 = 112%)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('price_coeff_cutting')
                                    ->label('Закройка')
                                    ->numeric()
                                    ->default(1.00)
                                    ->step(0.01)
                                    ->prefix('x'),

                                TextInput::make('price_coeff_sewing')
                                    ->label('Пошив')
                                    ->numeric()
                                    ->default(1.00)
                                    ->step(0.01)
                                    ->prefix('x'),

                                TextInput::make('price_coeff_shoemaker')
                                    ->label('Сапожник')
                                    ->numeric()
                                    ->default(1.00)
                                    ->step(0.01)
                                    ->prefix('x'),
                            ]),
                    ]),
                Textarea::make('description')
                    ->label('Комментарий (255 символов)')
                    ->maxLength(255)
                    ->columnSpanFull(),
                // Блок типов подноски и задника
                Grid::make(3)
                    ->schema([
                        Select::make('shoe_insole_id')
                            ->label('Тип стельки')
                            ->relationship('shoeInsole', 'name')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->fullName)
                            ->placeholder('Без стельки') // 
                            ->searchable()
                            ->preload(),

                        Select::make('counter_id')
                            ->label('Тип задника')
                            ->relationship('counter', 'name')
                            ->placeholder('Без задника') // Для шлепок
                            ->searchable()
                            ->preload(),

                        Select::make('puff_id')
                            ->label('Тип подноска')
                            ->relationship('puff', 'name')
                            ->placeholder('Без подноска')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columnSpanFull(),
                // Параметры и процессы на всю ширину экрана
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
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50),

                TextColumn::make('puff.name')
                    ->label('Подносок'),

                TextColumn::make('counter.name')
                    ->label('Задник'),

                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Активен'),
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('7xl'),
                //    AssociateAction::make(),
            ])
            ->recordActions([
                Action::make('edit_full')
                    ->label('Открыть модель')
                    ->color('warning')
                    ->url(
                        fn(ShoeModel $record): string =>
                        \App\Filament\Resources\ShoeModels\ShoeModelResource::getUrl('edit', ['record' => $record])
                    ),
                EditAction::make()->modalWidth('7xl'),
                //    DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginationPageOptions([10, 25, 50, 'all']);
    }
}
