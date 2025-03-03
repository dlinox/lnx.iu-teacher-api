<?php

use App\Modules\EnrollmentGrade\Http\Controllers\EnrollmentGradeController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/enrollment_grades')->group(function () {
    Route::post('load-datatable', [EnrollmentGradeController::class, 'loadDataTable']);
    Route::get('get-form-item/{id}', [EnrollmentGradeController::class, 'getFormItem']);
    Route::post('save-item', [EnrollmentGradeController::class, 'saveItem']);
    Route::delete('delete-item/{id}', [EnrollmentGradeController::class, 'deleteItem']);
});
