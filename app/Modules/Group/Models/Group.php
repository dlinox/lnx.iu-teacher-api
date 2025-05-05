<?php

namespace App\Modules\Group\Models;

use App\Modules\Schedule\Models\Schedule;
use App\Traits\HasDataTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Group extends Model
{
    use HasDataTable;

    static $searchColumns = [
        'groups.name',
        'courses.name',
        'periods.year',
        'months.name',
        'curriculums.name',
    ];

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
                $group->schedules  = Schedule::byGroup($group->id);
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
            'courses.units',
            'areas.name as area',
            'periods.id as periodId',
            'periods.year as period',
            'periods.month as month',
            'groups.modality as modality',
            'curriculums.id as curriculumId',
            'curriculums.name as curriculum',
            'curriculums.grading_model as gradingModel',
            'courses.units',
            'academic_records.id as hasAcademicRecord',
        )
            ->join('courses', 'courses.id', '=', 'groups.course_id')
            ->join('curriculums', 'courses.curriculum_id', '=', 'curriculums.id')
            ->join('modules', 'courses.module_id', '=', 'modules.id')
            ->join('areas', 'courses.area_id', '=', 'areas.id')
            ->join('periods', 'groups.period_id', '=', 'periods.id')
            ->leftJoin('academic_records', 'groups.id', '=', 'academic_records.group_id')
            ->where('groups.id', $id)
            ->first();

        $group->schedules  = Schedule::byGroup($group->id);

        return $group;
    }

    public static function getGroupStudents($id)
    {
        $students = DB::table('enrollment_groups')
            ->select(
                'students.id',
                'enrollment_groups.id as enrollmentGroupId',
                'students.name',
                'students.last_name_father as lastNameFather',
                'students.last_name_mother as lastNameMother',
                'students.document_number as documentNumber',
                'students.phone',
                'student_types.name as studentType',

            )
            ->join('students', 'enrollment_groups.student_id', '=', 'students.id')
            ->join('student_types', 'students.student_type_id', '=', 'student_types.id')
            ->where('enrollment_groups.group_id', $id)
            ->orderBy('students.name')
            ->orderBy('students.last_name_father')
            ->orderBy('students.last_name_mother')
            ->get();
        return $students;
    }

    public static function getGradeStudents($id)
    {
        $grades = DB::table('enrollment_groups')
            ->select(
                'students.code',
                'students.name',
                'students.last_name_father as lastNameFather',
                'students.last_name_mother as lastNameMother',
                'students.document_number as documentNumber',
                'enrollment_groups.id',
                'enrollment_groups.student_id as studentId',
                'enrollment_grades.id as gradeId',
                'enrollment_grades.grade as finalGrade',
                'enrollment_grades.is_locked as isLocked',
                'courses.units',
            )
            ->join('groups', 'enrollment_groups.group_id', '=', 'groups.id')
            ->join('students', 'enrollment_groups.student_id', '=', 'students.id')
            ->join('courses', 'groups.course_id', '=', 'courses.id')
            ->leftJoin('enrollment_grades', 'enrollment_groups.id', '=', 'enrollment_grades.enrollment_group_id')
            ->where('enrollment_groups.group_id', $id)
            ->orderBy('students.name')
            ->orderBy('students.last_name_father')
            ->orderBy('students.last_name_mother')
            ->get()->map(function ($grade) {
                $grade->finalGrade = $grade->finalGrade ? (float)$grade->finalGrade : 0;
                $grade->isLocked = $grade->isLocked ? $grade->isLocked : 0;

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
                        //grade a  float
                        $existingGrades[$i]->grade = $existingGrades[$i]->grade ? (float)$existingGrades[$i]->grade : 0;
                        $gradeUnits[] = $existingGrades[$i];
                    } else {
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

    public static function getAttendanceStudents($groupId, $date)
    {
        $students = DB::table('enrollment_groups')
            ->select(
                'enrollment_groups.id as enrollmentGroupId',
                'students.id',
                'students.name',
                'students.last_name_father as lastNameFather',
                'students.last_name_mother as lastNameMother',
                'students.document_number as documentNumber',
                'enrollment_group_attendances.status as status',
            )
            ->join('students', 'enrollment_groups.student_id', '=', 'students.id')
            ->join('student_types', 'students.student_type_id', '=', 'student_types.id')
            ->leftJoin('enrollment_group_attendances', function ($join) use ($date) {
                $join->on('enrollment_groups.id', '=', 'enrollment_group_attendances.enrollment_group_id')
                    ->where('enrollment_group_attendances.date', $date);
            })
            ->where('enrollment_groups.group_id', $groupId)
            ->orderBy('students.name')
            ->orderBy('students.last_name_father')
            ->orderBy('students.last_name_mother')
            ->get();
        return $students;
    }

    public static function getGradeStudentsByUnit($id, $unitOrder)
    {
        $grades = DB::table('enrollment_groups')
            ->select(
                'students.name',
                'students.last_name_father as lastNameFather',
                'students.last_name_mother as lastNameMother',
                'students.document_number as documentNumber',
                'enrollment_groups.id',
                'enrollment_groups.student_id as studentId',
                'enrollment_grades.id as gradeId',
                'enrollment_grades.grade as finalGrade',
                'enrollment_grades.is_locked',
                'courses.units',
            )
            ->join('groups', 'enrollment_groups.group_id', '=', 'groups.id')
            ->join('students', 'enrollment_groups.student_id', '=', 'students.id')
            ->join('courses', 'groups.course_id', '=', 'courses.id')
            ->leftJoin('enrollment_grades', 'enrollment_groups.id', '=', 'enrollment_grades.enrollment_group_id')
            ->where('enrollment_groups.group_id', $id)
            ->get()->map(function ($grade) use ($unitOrder) {
                $grade->finalGrade = $grade->finalGrade ? $grade->finalGrade : 0;

                $existingGrade = DB::table('enrollment_unit_grades')
                    ->select('id', 'order', 'grade')
                    ->where('enrollment_grade_id', $grade->gradeId)
                    ->where('order', $unitOrder)
                    ->first();

                // Crear la lista con solo la unidad especificada
                $gradeUnit = $existingGrade ? $existingGrade : (object)[
                    'id' => null,   // No existe en la BD
                    'order' => $unitOrder,  // Número de la unidad
                    'grade' => null // Sin calificación
                ];

                $grade->gradeUnit = $gradeUnit;
                return $grade;
            });
        return $grades;
    }
}
