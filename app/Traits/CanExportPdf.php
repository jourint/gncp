<?php

namespace App\Traits;

use Barryvdh\DomPDF\Facade\Pdf;

trait CanExportPdf
{
    /**
     * Экспорт в PDF с использованием динамического шаблона
     */
    public function streamReportPdf(string $activeReport, string $selectedDate, mixed $data, ?string $filename = null)
    {
        $filename = $filename ?? "report-{$activeReport}-{$selectedDate}.pdf";

        return response()->streamDownload(fn() => print(
            Pdf::loadView('filament.pages.reports.pdf-export', [
                'active_report' => $activeReport,
                'selected_date' => $selectedDate,
                'data'          => $data
            ])
            ->setPaper('a4', 'portrait')
            ->output()
        ), $filename);
    }
}
