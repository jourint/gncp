<?php

namespace App\Filament\Actions;

use App\Models\ShoeSole;
use App\Models\ShoeSoleItem;
use App\Models\Size;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CreateAllSizesForShoeSoleAction extends Action
{
    public static function make(?string $name = 'create_all_sizes'): static
    {
        return parent::make($name)
            ->label('Создать все размеры')
            ->icon('heroicon-m-plus-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Создание всех размеров')
            ->modalDescription('Будут созданы все возможные размеры для этой подошвы, если они еще не существуют.')
            ->action(function (ShoeSole $record) { // ← теперь принимаем ShoeSole
                // 1. Получаем все размеры из Sushi
                $allSizes = Size::all();

                // 2. Получаем уже существующие размеры для этой подошвы
                $existingSizeIds = $record->shoeSoleItems()->pluck('size_id')->toArray();

                // 3. Находим отсутствующие размеры
                $missingSizes = $allSizes->whereNotIn('id', $existingSizeIds);

                if ($missingSizes->isEmpty()) {
                    Notification::make()
                        ->title('Все размеры уже существуют')
                        ->body('Для этой подошвы уже добавлены все возможные размеры.')
                        ->info()
                        ->send();
                    return;
                }

                try {
                    DB::transaction(function () use ($record, $missingSizes) {
                        foreach ($missingSizes as $size) {
                            ShoeSoleItem::create([
                                'shoe_sole_id' => $record->id,
                                'size_id' => $size->id,
                                'stock_quantity' => 0,
                            ]);
                        }
                    });

                    $createdCount = $missingSizes->count();

                    Notification::make()
                        ->title('Успешно')
                        ->body("Создано {$createdCount} размеров")
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Ошибка при создании')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
