<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait CanExportCsv
{
    public function streamCsv(array|Collection $data, string $filename)
    {
        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            $items = collect($data);
            if ($items->isNotEmpty()) {
                fputcsv($handle, array_keys((array)$items->first()), ';', '"', '\\');
                foreach ($items as $row) {
                    fputcsv($handle, (array)$row, ';', '"', '\\');
                }
            }
            fclose($handle);
        }, $filename);
    }
}
