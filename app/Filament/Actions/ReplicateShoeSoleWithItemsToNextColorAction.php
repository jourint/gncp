<?php

namespace App\Filament\Actions;

use App\Models\ShoeSole;
use App\Models\ShoeSoleItem;
use App\Models\Color;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ReplicateShoeSoleWithItemsToNextColorAction extends Action
{
    public static function make(?string $name = 'replicate_shoe_sole_to_next_color'): static
    {
        return parent::make($name)
            ->label('Копировать в след. цвет')
            ->icon('heroicon-m-document-duplicate')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Копирование подошвы в следующий цвет')
            ->modalDescription('Будет создана новая подошва с тем же кодом, но другим цветом и всей размерной сеткой.')
            ->action(function (ShoeSole $record) {
                // 1. Находим следующий цвет
                $existingColorIds = ShoeSole::where('name', $record->name)
                    ->pluck('color_id')
                    ->toArray();

                $nextColor = Color::where('id', '>', $record->color_id)
                    ->whereNotIn('id', $existingColorIds)
                    ->orderBy('id', 'asc')
                    ->first();

                if (!$nextColor) {
                    Notification::make()
                        ->title('Нет доступных цветов')
                        ->body('Все последующие цвета уже заняты для этого кода подошвы.')
                        ->warning()
                        ->send();
                    return;
                }

                try {
                    DB::transaction(function () use ($record, $nextColor) {
                        // 2. Копируем подошву
                        $newSole = $record->replicate();
                        $newSole->color_id = $nextColor->id;
                        $newSole->save();

                        // 3. Копируем все размеры
                        foreach ($record->items as $item) {
                            $newItem = $item->replicate();
                            $newItem->shoe_sole_id = $newSole->id;
                            $newItem->save();
                        }
                    });

                    Notification::make()
                        ->title('Успешно')
                        ->body("Создана подошва для цвета: {$nextColor->name}, с размерами")
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Ошибка при копировании')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
