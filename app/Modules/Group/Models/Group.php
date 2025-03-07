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
            'curriculums.name as curriculum',
        )
            ->join('courses', 'courses.id', '=', 'groups.course_id')
            ->join('curriculums', 'courses.curriculum_id', '=', 'curriculums.id')
            ->join('modules', 'courses.module_id', '=', 'modules.id')
            ->join('areas', 'courses.area_id', '=', 'areas.id')
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
            'curriculums.id as curriculumId',
            'curriculums.name as curriculum',
            'courses.units',
        )
            ->join('courses', 'courses.id', '=', 'groups.course_id')
            ->join('curriculums', 'courses.curriculum_id', '=', 'curriculums.id')
            ->join('modules', 'courses.module_id', '=', 'modules.id')
            ->join('areas', 'courses.area_id', '=', 'areas.id')
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
            ->join('students', 'enrollment_groups.student_id', '=', 'students.id')
            ->join('student_types', 'students.student_type_id', '=', 'student_types.id')
            ->join('people', 'students.person_id', '=', 'people.id')
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
            ->select(
                'people.name',
                'people.last_name_father as lastNameFather',
                'people.last_name_mother as lastNameMother',
                'people.document_number as documentNumber',
                'enrollment_groups.id',
                'enrollment_groups.student_id as studentId',
                'enrollment_grades.id as gradeId',
                'enrollment_grades.grade as finalGrade',
                'courses.units',
            )
            ->join('groups', 'enrollment_groups.group_id', '=', 'groups.id')
            ->join('students', 'enrollment_groups.student_id', '=', 'students.id')
            ->join('people', 'students.person_id', '=', 'people.id')
            ->join('courses', 'groups.course_id', '=', 'courses.id')
            ->leftJoin('enrollment_grades', 'enrollment_groups.id', '=', 'enrollment_grades.enrollment_group_id')
            ->where('enrollment_groups.group_id', $id)
            ->get()->map(function ($grade) {
                $grade->finalGrade = $grade->finalGrade ? $grade->finalGrade : 0;

                $existingGrades = DB::table('enrollment_unit_grades')
                    ->select('id', 'order', 'grade')
                    ->where('enrollment_grade_id', $grade->gradeId)
                    ->orderBy('order')
                    ->get()
                    ->keyBy('order'); // Indexar por 'order' para acceso rápido

                // Crear la lista completa asegurando todas las unidades
                $gradeUnits = [];
                for ($i = 1; $i <= $grade->units; $i++) {
                    if (isset($existingGrades[$i])) {
                        // Si existe, usar el valor real
                        $gradeUnits[] = $existingGrades[$i];
                    } else {
                        // Si falta, agregar un objeto vacío
                        $gradeUnits[] = (object)[
                            'id' => null,   // No existe en la BD
                            'order' => $i,  // Número de la unidad
                            'grade' => null // Sin calificación
                        ];
                    }
                }

                $grade->gradeUnits = $gradeUnits;
                return $grade;
            });

        return $grades;
    }
}
