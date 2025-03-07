<?php

namespace App\Modules\Group\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Group\Models\Group;
use App\Http\Responses\ApiResponse;
use App\Modules\EnrollmentGrade\Models\EnrollmentGrade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function getGroupsForTeacher(Request $request)
    {
        try {
            $user = Auth::user();
            $teacher = DB::table('teachers')->select('id')->where('id', $user->model_id)->first();
            $items = Group::getGroupsForTeacher($teacher->id, $request->periodId);
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function getGroup(Request $request)
    {
        try {
            $item = Group::getGroup($request->id);
            return ApiResponse::success($item);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
    //getGroupStudents
    public function getGroupStudents(Request $request)
    {
        try {
            $items = Group::getGroupStudents($request->id);
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
    //getGradeStudents
    public function getGradeStudents(Request $request)
    {
        try {
            $items = Group::getGradeStudents($request->id);
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'No se pudo obtener las notas');
        }
    }


    public function saveGradeStudents(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach ($request->grades as $grade) {
                EnrollmentGrade::updateOrCreate(
                    ['enrollment_group_id' => $grade['enrollmentGroupId']],
                    [
                        'final_grade' => $grade['finalGrade'],
                        'capacity_average' => $grade['capacityAverage'],
                        'attitude_grade' => $grade['attitudeGrade'],
                    ]
                );
            }
            DB::commit();
            return ApiResponse::success(null, 'Notas guardadas correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage());
        }
    }
}
