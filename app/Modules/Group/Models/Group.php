<?php

namespace App\Modules\Group\Models;

use App\Modules\Period\Models\Period;
use App\Traits\HasDataTable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Group extends Model
{
    use HasDataTable;


    public static function getGroupsForTeacher($teacherId, $periodId)
    {

        $groups = self::select(
            'groups.id as id',
            'groups.name as name',
            'courses.name as course',
            'modules.name as module',
            'areas.name as area',
            'periods.year as period',
            'periods.month as month',
            'groups.modality as modality',
        )
            ->join('curriculum_courses', 'groups.curriculum_course_id', '=', 'curriculum_courses.id')
            ->join('courses', 'curriculum_courses.course_id', '=', 'courses.id')
            ->join('modules', 'curriculum_courses.module_id', '=', 'modules.id')
            ->join('areas', 'curriculum_courses.area_id', '=', 'areas.id')
            ->join('periods', 'groups.period_id', '=', 'periods.id')
            ->where('groups.teacher_id', $teacherId)
            ->where('groups.period_id', $periodId)
            ->get()->map(function ($group) {
                $group->schedules = DB::table('schedules')
                    ->select('schedules.day', 'schedules.start_hour', 'schedules.end_hour')
                    ->where('group_id', $group->id)
                    ->get()->map(function ($shedule) {
                        $shedule->day = $shedule->day;
                        $shedule->start_hour = Carbon::parse($shedule->start_hour)->format('h:i A');
                        $shedule->end_hour = Carbon::parse($shedule->end_hour)->format('h:i A');
                        return $shedule;
                    });
                return $group;
            });
        return $groups;
    }

    public static function getGroup($id)
    {
        $group = self::select(
            'groups.id as id',
            'groups.name as name',
            'courses.name as course',
            'modules.name as module',
            'areas.name as area',
            'periods.year as period',
            'periods.month as month',
            'groups.modality as modality',
        )
            ->join('curriculum_courses', 'groups.curriculum_course_id', '=', 'curriculum_courses.id')
            ->join('courses', 'curriculum_courses.course_id', '=', 'courses.id')
            ->join('modules', 'curriculum_courses.module_id', '=', 'modules.id')
            ->join('areas', 'curriculum_courses.area_id', '=', 'areas.id')
            ->join('periods', 'groups.period_id', '=', 'periods.id')
            ->where('groups.id', $id)
            ->first();

        $group->schedules = DB::table('schedules')
            ->select('schedules.day', 'schedules.start_hour', 'schedules.end_hour')
            ->where('group_id', $group->id)
            ->get()->map(function ($shedule) {
                $shedule->day = $shedule->day;
                $shedule->start_hour = Carbon::parse($shedule->start_hour)->format('h:i A');
                $shedule->end_hour = Carbon::parse($shedule->end_hour)->format('h:i A');
                return $shedule;
            });

        return $group;
    }

    public static function getGroupStudents($id)
    {
        $students = DB::table('enrollment_groups')
            ->join('students', 'enrollment_groups.student_id', '=', 'students.id')
            ->join('people', 'students.person_id', '=', 'people.id')
            ->join('student_types', 'students.student_type_id', '=', 'student_types.id')
            ->select(
                'students.id',
                'enrollment_groups.id as enrollmentGroupId',
                'people.name',
                'people.last_name_father as lastNameFather',
                'people.last_name_mother as lastNameMother',
                'people.document_number as documentNumber',
                'people.email',
                'people.phone',
                'student_types.name as studentType',
            )
            ->where('enrollment_groups.group_id', $id)
            ->orderBy('people.name')
            ->orderBy('people.last_name_father')
            ->orderBy('people.last_name_mother')
            ->get();
        return $students;
    }

    public static function getGradeStudents($id)
    {
        $grades = DB::table('enrollment_groups')
            ->leftJoin('enrollment_grades', 'enrollment_groups.id', '=', 'enrollment_grades.enrollment_group_id')
            ->select(
                'enrollment_groups.id',
                'enrollment_groups.student_id as studentId',
                'enrollment_grades.final_grade as finalGrade',
                'enrollment_grades.capacity_average as capacityAverage',
                'enrollment_grades.attitude_grade as attitudeGrade',
            )
            ->where('enrollment_groups.group_id', $id)
            ->get()->map(function ($grade) {
                $grade->finalGrade = $grade->finalGrade ? $grade->finalGrade : 0;
                $grade->capacityAverage = $grade->capacityAverage ? $grade->capacityAverage : 0;
                $grade->attitudeGrade = $grade->attitudeGrade ? $grade->attitudeGrade : 'A';
                return $grade;
            });

        return $grades;
    }
}
