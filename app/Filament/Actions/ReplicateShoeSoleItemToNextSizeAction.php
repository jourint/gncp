<?php

namespace App\Filament\Actions;

use App\Models\ShoeSoleItem;
use App\Models\Size;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ReplicateShoeSoleItemToNextSizeAction extends Action
{
    public static function make(?string $name = 'replicate_item_to_next_size'): static
    {
        return parent::make($name)
            ->label('В след. размер')
            ->icon('heroicon-m-arrow-up-circle')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Копирование в следующий размер')
            ->action(function (ShoeSoleItem $record) {
                // 1. Находим следующий размер из Sushi-модели Size
                $currentSize = Size::find($record->size_id);

                if (!$currentSize) {
                    Notification::make()
                        ->title('Ошибка')
                        ->body('Текущий размер не найден.')
                        ->danger()
                        ->send();
                    return;
                }

                // 2. Получаем ID всех размеров, отсортированных по возрастанию
                $allSizeIds = Size::orderBy('id', 'asc')->pluck('id')->toArray();
                $currentIndex = array_search($record->size_id, $allSizeIds);

                if ($currentIndex === false || !isset($allSizeIds[$currentIndex + 1])) {
                    Notification::make()
                        ->title('Нет следующего размера')
                        ->body('Это максимальный размер.')
                        ->warning()
                        ->send();
                    return;
                }

                $nextSizeId = $allSizeIds[$currentIndex + 1];
                $nextSize = Size::find($nextSizeId);

                // 3. Проверяем, что такой размер еще не существует для этой подошвы
                $exists = ShoeSoleItem::where('shoe_sole_id', $record->shoe_sole_id)
                    ->where('size_id', $nextSizeId)
                    ->exists();

                if ($exists) {
                    Notification::make()
                        ->title('Размер уже существует')
                        ->body("Размер {$nextSize->name} уже добавлен для этой подошвы.")
                        ->warning()
                        ->send();
                    return;
                }

                try {
                    // 4. Создаем копию с новым размером
                    DB::transaction(function () use ($record, $nextSizeId) {
                        $duplicated = $record->replicate();
                        $duplicated->stock_quantity = 0;    // обнуляем количество на складе для нового размера
                        $duplicated->size_id = $nextSizeId;
                        $duplicated->save();
                    });

                    Notification::make()
                        ->title('Успешно')
                        ->body("Создан размер {$nextSize->name}")
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
