<?php

namespace App\Filament\Actions;

use App\Models\Material;
use App\Models\Color;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ReplicateMaterialToNextColorAction extends Action
{
    public static function make(?string $name = 'replicate_material_to_next_color'): static
    {
        return parent::make($name)
            ->label('В след. цвет')
            ->icon('heroicon-m-arrow-right-circle')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Копирование в следующий цвет')
            ->action(function (Material $record) {
                // 1. Находим все цвета, которые уже используются для данного материала с таким же типом
                $existingColorIds = Material::where('name', $record->name)
                    ->where('material_type_id', $record->material_type_id)
                    ->where('texture_id', $record->texture_id)
                    ->pluck('color_id')
                    ->toArray();

                // 2. Ищем следующий цвет после текущего
                $nextColor = Color::where('id', '>', $record->color_id)
                    ->whereNotIn('id', $existingColorIds)
                    ->orderBy('id', 'asc')
                    ->first();

                if (!$nextColor) {
                    Notification::make()
                        ->title('Нет доступных цветов')
                        ->body('Все последующие цвета уже использованы для этого материала.')
                        ->warning()
                        ->send();
                    return;
                }

                try {
                    // 3. Дублируем материал
                    $duplicatedMaterial = DB::transaction(function () use ($record, $nextColor) {
                        $duplicated = $record->replicate();
                        $duplicated->color_id = $nextColor->id;

                        // Если в названии встречается старый цвет, заменяем на новый
                        $oldColorName = $record->color?->name;
                        if ($oldColorName && str_contains($record->name, $oldColorName)) {
                            $duplicated->name = str_replace($oldColorName, $nextColor->name, $record->name);
                        } else {
                            // Или можно сгенерировать новое имя
                            $duplicated->name = $record->name; // или добавить суффикс
                        }

                        $duplicated->save();

                        return $duplicated;
                    });

                    Notification::make()
                        ->title('Успешно')
                        ->body("Создана копия для цвета: {$nextColor->name}")
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
