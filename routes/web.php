<?php

use Illuminate\Support\Facades\Route;
use Sglms\InverseLogistics\Http\Controllers\ReturnController;
use Sglms\InverseLogistics\Models\ILReturn;

Route::group([
    'prefix' => 'inverse-logistics',
    'as' => 'inverse-logistics.',
    'middleware' => ['web', 'auth'],
], function () {
    Route::get('/', [ReturnController::class, 'index'])->name('index');
});
