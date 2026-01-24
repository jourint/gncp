<?php

use Illuminate\Support\Facades\Route;

use App\Filament\Pages\AdvancedReports;


Route::get('/', function () {
    return view('welcome');
})->name('home');



Route::get('/reports/export/pdf/{type}/{date}', function (string $type, string $date) {
    $page = new AdvancedReports();
    $page->selected_date = $date;
    return $page->exportToPdf($type);
})->name('reports.pdf');

Route::get('/reports/export/excel/{type}/{date}', function (string $type, string $date) {
    $page = new AdvancedReports();
    $page->selected_date = $date;
    return $page->exportToExcel($type);
})->name('reports.excel');
