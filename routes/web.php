<?php

use Illuminate\Support\Facades\Route;
use Sglms\InverseLogistics\Http\Controllers\ILReturnController;

Route::group([
    'prefix' => 'inverse-logistics',
    'as' => 'inverse-logistics.',
    'middleware' => ['web', 'auth'],
], function () {
    Route::get('/', [ILReturnController::class, 'index'])->name('index');
});
