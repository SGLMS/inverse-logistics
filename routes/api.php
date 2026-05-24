<?php

use Illuminate\Support\Facades\Route;
use Sglms\InverseLogistics\Http\Controllers\ILReturnController;

Route::prefix('inverse-logistics')
    ->as('inverse-logistics.')
    ->middleware(['api'])
    ->group(function () {
        Route::post('/returns', [ILReturnController::class, 'store'])->name('returns.store');
        Route::post('/returns/{returnId}/approve', [ILReturnController::class, 'approve'])->name('returns.approve');
        Route::post('/returns/{returnId}/reject', [ILReturnController::class, 'reject'])->name('returns.reject');
    });
