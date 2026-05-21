<?php

use Illuminate\Support\Facades\Route;
use Sglms\InverseLogistics\Http\Controllers\ReturnController;

Route::prefix('inverse-logistics')
    ->as('inverse-logistics.')
    ->middleware(['api'])
    ->group(function () {
        Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
        Route::post('/returns/{returnId}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
        Route::post('/returns/{returnId}/reject', [ReturnController::class, 'reject'])->name('returns.reject');
    });
