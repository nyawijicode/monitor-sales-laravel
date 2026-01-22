<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\BOQPdfController;

Route::get('/', [LandingPageController::class, 'index']);

// BOQ PDF Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/boq/{id}/pdf/preview', [BOQPdfController::class, 'preview'])
        ->name('boq.pdf.preview');
    Route::get('/boq/{id}/pdf/download/{type}', [BOQPdfController::class, 'download'])
        ->name('boq.pdf.download');
});
