<?php

use App\Modules\Period\Http\Controllers\PeriodController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/period')->middleware('auth:sanctum')->group(function () {
    Route::get('current', [PeriodController::class, 'getCurrent']);
    Route::get('enrollment-period', [PeriodController::class, 'getEnrollmentPeriod']);
    //getAll
    Route::get('all', [PeriodController::class, 'getAll']);
    Route::get('by-teacher', [PeriodController::class, 'getPeriodsByTeacher']);
});
