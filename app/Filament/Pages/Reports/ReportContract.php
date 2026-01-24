<?php

namespace App\Filament\Pages\Reports;

use Illuminate\Support\Collection;

interface ReportContract
{
    // Метод для получения структурированных данных (для экрана и PDF)
    public function execute(string $date): Collection|array;

    // Метод для превращения данных в плоский список (для Excel)
    public function toExcel(string $date): Collection;
}
