<?php

namespace App\Filament\Resources\ShoeModels\RelationManagers;

use App\Models\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;

class ShoeModelPatternsRelationManager extends RelationManager
{
    protected static string $relationship = 'shoeModelPatterns';
    protected static ?string $title = 'Лекала модели';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('size_id')
                    ->label('Размер')
                    ->options(fn() => Size::all()->pluck('name', 'id'))
                    ->placeholder('Общее лекало (на все размеры)')
                    ->searchable(),

                TextInput::make('file_name')
                    ->label('Название детали')
                    ->placeholder('Например: Боковина внешняя')
                    ->required(),

                FileUpload::make('file_path')
                    ->label('Файл (PDF или Фото)')
                    ->disk('public')
                    ->directory('shoe-model-patterns')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->preserveFilenames()
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('note')
                    ->label('Заметка')
                    ->placeholder('Особенности кроя...')
                    ->helperText('Подсказка: Печатайте лекала в масштабе 100% (Actual Size)')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                TextColumn::make('size_id')
                    ->label('Размер')
                    ->formatStateUsing(fn($state) => $state ? "Размер: {$state}" : 'Общее')
                    ->badge()
                    ->color(fn($state) => $state ? 'primary' : 'gray'),

                TextColumn::make('file_name')
                    ->label('Наименование детали')
                    ->description(fn($record) => $record->note)
                    ->searchable(),

                IconColumn::make('file_path')
                    ->label('PDF')
                    ->icon(fn($state) => str_contains($state, '.pdf') ? 'heroicon-o-document-text' : 'heroicon-o-photo')
                    ->color(fn($state) => str_contains($state, '.pdf') ? 'danger' : 'success'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                // Быстрый просмотр файла
                Action::make('open_file')
                    ->label('Просмотр')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->url(fn($record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
