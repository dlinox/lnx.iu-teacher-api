<?php

use App\Modules\Group\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/groups')->middleware('auth:sanctum')->group(function () {

    //loadDataTable
    Route::post('load-data', [GroupController::class, 'loadDataTable']);

    Route::get('teacher', [GroupController::class, 'getGroupsForTeacher']);
    //getGroup
    Route::get('item/{id}', [GroupController::class, 'getGroup']);
    //_groupStudents
    Route::get('students/{id}', [GroupController::class, 'getGroupStudents']);
    //getGradeStudents
    Route::get('grade-students/{id}', [GroupController::class, 'getGradeStudents']);
    //saveGradeStudents
    Route::post('grade-students/save', [GroupController::class, 'saveGradeStudents']);

    //saveAttendanceStudents
    Route::post('attendance-students/save', [GroupController::class, 'saveAttendanceStudents']);

    //getActiveGroupsForTeacher
    Route::get('active-groups-for-teacher', [GroupController::class, 'getActiveGroupsForTeacher']);

    //getGradeStudentsByUnit
    Route::post('unit-grade-students', [GroupController::class, 'getGradeStudentsByUnit']);

    //getAttendanceStudents
    Route::post('attendance-students', [GroupController::class, 'getAttendanceStudents']);

    //getGradeDeadline
    Route::get('grade-deadline', [GroupController::class, 'getGradeDeadline']);

    //academicRecordPdf
    Route::post('academic-record-pdf', [GroupController::class, 'academicRecordPdf']);

    //lastAcademicRecordPdfByGroup
    Route::get('last-academic-record-pdf/{id}', [GroupController::class, 'lastAcademicRecordPdfByGroup']);
});
