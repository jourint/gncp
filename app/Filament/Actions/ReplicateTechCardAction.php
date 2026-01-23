<?php

namespace App\Filament\Actions;

use App\Models\ShoeTechCard;
use App\Models\Color;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ReplicateTechCardAction extends Action
{
    public static function make(?string $name = 'replicate_to_next_color'): static
    {
        return parent::make($name)
            ->label('В след. цвет')
            ->icon('heroicon-m-arrow-right-circle')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Копирование в следующий цвет')
            ->action(function (ShoeTechCard $record) {
                // 1. Поиск следующего цвета
                $existingColorIds = ShoeTechCard::where('shoe_model_id', $record->shoe_model_id)
                    ->pluck('color_id')
                    ->toArray();

                $nextColor = Color::where('id', '>', $record->color_id)
                    ->whereNotIn('id', $existingColorIds)
                    ->orderBy('id', 'asc')
                    ->first();

                if (!$nextColor) {
                    Notification::make()
                        ->title('Нет доступных цветов')
                        ->body('Все последующие цвета уже использованы.')
                        ->warning()
                        ->send();
                    return;
                }

                try {
                    // Используем return, чтобы транзакция вернула созданный объект наружу
                    $newCard = DB::transaction(function () use ($record, $nextColor) {
                        $duplicatedCard = $record->replicate();
                        $duplicatedCard->color_id = $nextColor->id;

                        $oldColorName = $record->color?->name;
                        if ($oldColorName && str_contains($record->name, $oldColorName)) {
                            $duplicatedCard->name = str_replace($oldColorName, $nextColor->name, $record->name);
                        } else {
                            $duplicatedCard->name = ($record->shoeModel?->name ?? 'Модель') . ' / ' . $nextColor->name;
                        }

                        $duplicatedCard->save();

                        // Копируем материалы (проверь, что связь в модели называется techCardMaterials)
                        if (method_exists($record, 'techCardMaterials')) {
                            foreach ($record->techCardMaterials as $material) {
                                $newMaterial = $material->replicate();
                                $newMaterial->shoe_tech_card_id = $duplicatedCard->id;
                                $newMaterial->save();
                            }
                        }

                        return $duplicatedCard;
                    });

                    Notification::make()
                        ->title('Успешно')
                        ->body("Создана копия для цвета: {$nextColor->name}")
                        ->success()
                        ->send();

                    // Теперь $newCard определена, и мы можем делать редирект
                    return redirect()->route('filament.cp.resources.shoe-tech-cards.edit', [
                        'record' => $newCard
                    ]);
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
