<?php

use Illuminate\Support\Facades\Route;
use Webkul\Reports\Http\Controllers\ReportController;

Route::controller(ReportController::class)->prefix('reports')->group(function () {
    Route::get('', 'index')->name('admin.reports.index');
    Route::get('data', 'getData')->name('admin.reports.data');
    Route::get('export', 'exportCsv')->name('admin.reports.export');
});
