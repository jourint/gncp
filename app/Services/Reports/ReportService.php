<?php

namespace App\Services\Reports;

use App\Enums\JobPosition;
use App\Filament\Pages\Reports\{
    ProductionReport,
    MiscellaneousReport,
    ExpeditionReport,
    StockRequirementsReport,
    SalaryReport,
    ReportContract
};

class ReportService
{
    /**
     * Фабрика отчетов
     */
    public function getModule(string $type): ?ReportContract
    {
        return match ($type) {
            'cutting'            => new ProductionReport(),
            'sewing'             => new ProductionReport(JobPosition::Sewing->value),
            'shoemaker'          => new ProductionReport(JobPosition::Shoemaker->value),
            'miscellaneous'      => new MiscellaneousReport(),
            'expedition'         => new ExpeditionReport(),
            'stock_requirements' => new StockRequirementsReport(),
            'salary'             => new SalaryReport(),
            default              => null,
        };
    }

    /**
     * Получение данных для конкретного отчета
     */
    public function getData(string $type, string $date)
    {
        $module = $this->getModule($type);
        return $module ? $module->execute($date) : collect();
    }
}
