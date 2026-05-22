<?php

use Illuminate\Support\Facades\Route;
use Sglms\InverseLogistics\Http\Controllers\ReturnController;

Route::group([
    'prefix' => 'inverse-logistics',
    'as' => 'inverse-logistics.',
    'middleware' => ['web', 'auth'],
], function () {
    Route::get('/', [ReturnController::class, 'index'])->name('index');
});
