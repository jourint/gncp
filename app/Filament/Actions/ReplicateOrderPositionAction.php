<?php

namespace App\Filament\Actions;

use App\Models\OrderPosition;
use App\Models\ShoeModel;
use App\Models\Size;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ReplicateOrderPositionAction
{
    public static function make(?string $name = 'replicate_to_next_size'): Action
    {
        return Action::make($name)
            ->label('В след. размер')
            ->icon('heroicon-m-arrow-right-circle')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Копирование в следующий размер')
            ->action(function (OrderPosition $record) {
                // 1. Получаем техкарту и модель
                $techCard = $record->shoeTechCard;
                $model = $techCard?->shoeModel;

                if (!$model) {
                    Notification::make()
                        ->title('Ошибка')
                        ->body('Не удалось получить модель обуви.')
                        ->danger()
                        ->send();
                    return;
                }

                // 2. Получаем доступные размеры модели
                $availableSizes = $model->available_sizes ?? [];
                if (empty($availableSizes)) {
                    Notification::make()
                        ->title('Нет доступных размеров')
                        ->body('Модель не имеет доступных размеров.')
                        ->warning()
                        ->send();
                    return;
                }

                // 3. Получаем используемые размеры **для этой же техкарты** в заказе
                $usedSizesForThisTechCard = $record->order->positions
                    ->where('shoe_tech_card_id', $record->shoe_tech_card_id)
                    ->pluck('size_id')
                    ->toArray();

                // 4. Ищем текущий размер в доступных
                $currentIndex = array_search($record->size_id, $availableSizes);

                if ($currentIndex === false) {
                    Notification::make()
                        ->title('Размер не найден')
                        ->body('Текущий размер не входит в доступные размеры модели.')
                        ->warning()
                        ->send();
                    return;
                }

                // 5. Ищем **следующий свободный размер** (циклически)
                $nextSizeId = null;
                $total = count($availableSizes);

                for ($i = 1; $i < $total; $i++) { // начинаем с 1, т.к. 0 — текущий размер
                    $index = ($currentIndex + $i) % $total; // циклический поиск
                    $candidateSizeId = $availableSizes[$index];

                    if (!in_array($candidateSizeId, $usedSizesForThisTechCard)) {
                        $nextSizeId = $candidateSizeId;
                        break;
                    }
                }

                if (!$nextSizeId) {
                    Notification::make()
                        ->title('Нет свободных размеров')
                        ->body('Все возможные размеры уже используются для этой техкарты в заказе.')
                        ->warning()
                        ->send();
                    return;
                }

                $nextSize = Size::find($nextSizeId);
                if (!$nextSize) {
                    Notification::make()
                        ->title('Размер не найден')
                        ->body("Размер с ID {$nextSizeId} не существует.")
                        ->danger()
                        ->send();
                    return;
                }

                try {
                    $newPosition = DB::transaction(function () use ($record, $nextSizeId) {
                        $duplicatedPosition = $record->replicate();
                        $duplicatedPosition->size_id = $nextSizeId;
                        $duplicatedPosition->save();

                        return $duplicatedPosition;
                    });

                    Notification::make()
                        ->title('Успешно')
                        ->body("Создана копия для размера: {$nextSize->name}")
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
