<?php

use App\Modules\Group\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/groups')->middleware('auth:sanctum')->group(function () {
    Route::get('teacher', [GroupController::class, 'getGroupsForTeacher']);
    //getGroup
    Route::get('item/{id}', [GroupController::class, 'getGroup']);
    //_groupStudents
    Route::get('students/{id}', [GroupController::class, 'getGroupStudents']);
    //getGradeStudents
    Route::get('grade-students/{id}', [GroupController::class, 'getGradeStudents']);
    //saveGradeStudents
    Route::post('grade-students/save', [GroupController::class, 'saveGradeStudents']);
});
