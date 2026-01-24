<?php

namespace App\Filament\Pages\Reports;

use App\Models\MaterialLining;
use Illuminate\Support\Collection;

abstract class BaseReport implements ReportContract
{
    /**
     * Общий хелпер для получения названий подкладок, 
     * чтобы не писать этот запрос в каждом классе.
     */
    protected function getLiningNames(Collection $data): Collection
    {
        $ids = $data->pluck('lining_id')->filter()->unique();

        return MaterialLining::with('color')
            ->whereIn('id', $ids)
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => $item->full_name]);
    }
}
