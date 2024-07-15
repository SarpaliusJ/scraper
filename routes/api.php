<?php

use App\Http\Controllers\Api\JobController;
use Illuminate\Support\Facades\Route;

Route::controller(JobController::class)->group(function () {
    Route::prefix('jobs')->group(function () {
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::delete('/{id}', 'delete');
    });
});
