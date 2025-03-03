<?php

namespace App\Modules\Course\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Modules\Course\Http\Requests\CourseStoreRequest;
use App\Modules\Course\Http\Requests\CourseUpdateRequest;
use App\Modules\Course\Models\Course;
use App\Modules\Course\Http\Resources\CourseDataTableItemsResource;
// use App\Modules\CurriculumModule\Models\CurriculumModule;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function getCurriculumCourses(Request $request)
    {
        try {
            $courses = Course::geCurriculumCourses($request->curriculumId, $request->moduleId);
            return ApiResponse::success($courses);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
}
