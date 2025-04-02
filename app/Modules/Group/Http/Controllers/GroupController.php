<?php

namespace App\Modules\Group\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Group\Models\Group;
use App\Http\Responses\ApiResponse;
use App\Modules\EnrollmentGrade\Models\EnrollmentGrade;
use App\Modules\Schedule\Models\Schedule;
use Carbon\Carbon;
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
                $enrollmentGrade =  EnrollmentGrade::updateOrCreate(
                    ['enrollment_group_id' => $grade['enrollmentGroupId']],
                    [
                        'grade' => $grade['finalGrade'],
                        'is_locked' => false
                    ]
                );
                foreach ($grade['gradeUnits'] as $gradeUnit) {
                    if ($gradeUnit['grade'] != null) {
                        $enrollmentUnitGrade = DB::table('enrollment_unit_grades')->updateOrInsert(
                            ['enrollment_grade_id' => $enrollmentGrade->id, 'order' => $gradeUnit['order']],
                            [
                                'grade' => $gradeUnit['grade'],
                                'order' => $gradeUnit['order']
                            ]
                        );
                    }
                }
            }
            DB::commit();
            return ApiResponse::success(null, 'Notas guardadas correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage());
        }
    }

    public function getActiveGroupsForTeacher(Request $request)
    {
        try {
            $user = Auth::user();
            $teacher = DB::table('teachers')->select('id')->where('id', $user->model_id)->first();

            $items = Group::select(
                'groups.id as id',
                'groups.name as name',
                'courses.name as course',
                'modules.name as module',
                'areas.name as area',
                'periods.year as period',
                'periods.month as month',
                'groups.modality as modality',
                'curriculums.name as curriculum',
            )
                ->join('courses', 'courses.id', '=', 'groups.course_id')
                ->join('curriculums', 'courses.curriculum_id', '=', 'curriculums.id')
                ->join('modules', 'courses.module_id', '=', 'modules.id')
                ->join('areas', 'courses.area_id', '=', 'areas.id')
                ->join('periods', 'groups.period_id', '=', 'periods.id')
                ->where('groups.teacher_id', $teacher->id)
                ->whereIn('groups.status', ['ABIERTO', 'CERRADO'])
                ->get()->map(function ($group) {
                    $group->schedules  = Schedule::byGroup($group->id);
                    return $group;
                });
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
    //getGradeStudentsByUnit
    public function getGradeStudentsByUnit(Request $request)
    {
        try {
            $items = Group::getGradeStudentsByUnit($request->groupId, $request->unitOrder);
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'No se pudo obtener las notas');
        }
    }
}
