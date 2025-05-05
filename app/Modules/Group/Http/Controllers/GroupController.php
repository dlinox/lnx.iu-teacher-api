<?php

namespace App\Modules\Group\Http\Controllers;

use App\Helpers\Utilities;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Group\Models\Group;
use App\Http\Responses\ApiResponse;
use App\Models\AcademicRecord;
use App\Models\EnrollmentGroupAttendance;
use App\Models\GradeDeadline;
use App\Modules\EnrollmentGrade\Models\EnrollmentGrade;
use App\Modules\Group\Http\Resources\GroupDataTableItemResource;
use App\Modules\Schedule\Models\Schedule;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Luecano\NumeroALetras\NumeroALetras as NumberToWords; // Importa la clase

class GroupController extends Controller
{

    public function loadDataTable(Request $request)
    {

        $user = Auth::user();

        try {
            $documentTypes = Group::select(
                'groups.id as id',
                'groups.name as name',
                'courses.name as course',
                'periods.id as period_id',
                DB::raw('CONCAT(periods.year, " ", UPPER(months.name)) as period'),
                'groups.modality as modality',
                'curriculums.name as curriculum',
            )->join('courses', 'courses.id', '=', 'groups.course_id')
                ->join('curriculums', 'courses.curriculum_id', '=', 'curriculums.id')
                ->join('periods', 'groups.period_id', '=', 'periods.id')
                ->join('months', 'periods.month', '=', 'months.id')
                ->join('teachers', 'teachers.id', '=', 'groups.teacher_id')
                ->where('teachers.id', $user->model_id)
                ->orderBy('periods.year', 'desc')
                ->orderBy('periods.month', 'desc')
                ->dataTable($request);
            GroupDataTableItemResource::collection($documentTypes);
            return ApiResponse::success($documentTypes);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar los registros');
        }
    }

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

    public function getGradeDeadline()
    {
        try {
            $item = GradeDeadline::activeGradePeriod();
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

            if (!$request->grades || count($request->grades) == 0) {
                return ApiResponse::error(null, 'No se encontraron notas para guardar');
            }

            $group = Group::select('groups.id as groupId', 'groups.period_id as periodId')
                ->join('enrollment_groups', 'enrollment_groups.group_id', '=', 'groups.id')
                ->where('enrollment_groups.id', $request->grades[0]['enrollmentGroupId'])
                ->first();

            if (!$group) {
                return ApiResponse::error(null, 'No se encontró el grupo');
            }

            $gradeDeadline = GradeDeadline::getGradeDeadlineByPeriod($group->periodId);
            if (!$gradeDeadline) {
                return ApiResponse::error(null, 'No hay un período activo para guardar las notas');
            }

            DB::beginTransaction();
            foreach ($request->grades as $grade) {
                $enrollmentGrade =  EnrollmentGrade::updateOrCreate(
                    ['enrollment_group_id' => $grade['enrollmentGroupId']],
                    [
                        'grade' => $grade['finalGrade'],
                    ]
                );
                foreach ($grade['gradeUnits'] as $gradeUnit) {
                    if ($gradeUnit['grade'] !== null) {
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

    public function saveAttendanceStudents(Request $request)
    {
        try {
            $date = $request->date ? $request->date : date('Y-m-d');
            DB::beginTransaction();
            foreach ($request->students as $student) {
                EnrollmentGroupAttendance::updateOrCreate(
                    [
                        'enrollment_group_id' => $student['enrollmentGroupId'],
                        'date' => $student['date'],
                    ],
                    [
                        'status' => $student['status'] ?? 'FALTA',
                        'date' => $student['date'],
                        'time' => date('H:i:s'),
                    ]
                );
                $date = $student['date'];
            }

            $group = Group::select('groups.id')
                ->join('enrollment_groups', 'enrollment_groups.group_id', '=', 'groups.id')
                ->where('enrollment_groups.id', $request->students[0]['enrollmentGroupId'])
                ->first();

            if (!$group) {
                return ApiResponse::error(null, 'No se encontró el grupo');
            }

            $items = Group::getAttendanceStudents($group->id, $date);
            DB::commit();
            return ApiResponse::success($items, 'Asistencia guardada correctamente');
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

    public function getGradeStudentsByUnit(Request $request)
    {
        try {
            $items = Group::getGradeStudentsByUnit($request->groupId, $request->unitOrder);
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'No se pudo obtener las notas');
        }
    }

    public function getAttendanceStudents(Request $request)
    {
        try {
            $date = $request->date ? $request->date : date('Y-m-d');
            $items = Group::getAttendanceStudents($request->id, $date);
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    //academicRecordPdf
    public function academicRecordPdf(Request $request)
    {
        try {
            $groupId = $request->id;
            $user = Auth::user();

            $userInitials = Utilities::InitialsString($user->name);

            $group = Group::select(
                'groups.id as id',
                'groups.name as name',
                'modules.level as level',
                'months.name as month',
                'periods.year as year',
                'periods.id as periodId',
                'courses.name as course',
                'curriculums.grading_model as gradingModel',
                DB::raw('CONCAT_WS(" ", teachers.last_name_father, teachers.last_name_mother, "," , teachers.name) as teacher'),
            )
                ->where('groups.id', $groupId)
                ->join('periods', 'periods.id', '=', 'groups.period_id')
                ->join('months', 'periods.month', '=', 'months.id')
                ->join('courses', 'courses.id', '=', 'groups.course_id')
                ->join('modules', 'modules.id', '=', 'courses.module_id')
                ->join('teachers', 'teachers.id', '=', 'groups.teacher_id')
                ->join('curriculums', 'curriculums.id', '=', 'courses.curriculum_id')
                ->where('teacher_id', $user->model_id)
                ->first();

            if (!$group) {
                $errorPdf = $this->generateErrorPdf('No se encontró el grupo');
                return response($errorPdf->Output('', 'S'), 200)
                    ->header('Content-Type', 'application/pdf');
            }

            $group->level = Utilities::NumberToOrdinal($group->level);

            $formatter = new NumberToWords();

            $students = Group::getGradeStudents($groupId)->map(function ($student) use ($formatter) {
                return (object) [
                    'code' => $student->code,
                    'name' => $student->lastNameFather . ' ' . $student->lastNameMother . ', ' . $student->name,
                    'finalGrade' => $student->finalGrade,
                    'gradeUnits' => $student->gradeUnits,
                    'finalGradeText' => $formatter->toWords((float)$student->finalGrade, 2)
                ];
            });

            $dataPDF = (object)[
                'group' => $group,
                'students' => $students,
                'userInitials' => $userInitials,
                'watermark' => true,
            ];

            $pdf = $this->generateRecordPdf($dataPDF);

            DB::beginTransaction();

            $gradeDeadline = GradeDeadline::getGradeDeadlineByPeriod($group->periodId);

            if (!$gradeDeadline) {
                $errorPdf = $this->generateErrorPdf('No ha un período activo para generar el acta de notas');
                return response($errorPdf->Output('', 'S'), 200)
                    ->header('Content-Type', 'application/pdf');
            }

            AcademicRecord::create([
                'group_id' => $groupId,
                'created_by' => $user->id,
                'grade_deadline_id' => $gradeDeadline['id'],
                'payload' => json_encode($dataPDF),
                'observations' => $request->observations,
            ]);

            Group::where('id', $groupId)->update(['status' => 'FINALIZADO']);

            DB::commit();

            return response($pdf->Output('', 'S'), 200)
                ->header('Content-Type', 'application/pdf');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al generar el acta de notas');
        }
    }

    public function lastAcademicRecordPdfByGroup(Request $request)
    {
        try {
            $groupId = $request->id;

            $academicRecord = AcademicRecord::where('group_id', $groupId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$academicRecord) {
                return ApiResponse::error(null, 'No se encontró el acta de notas');
            }

            $dataPDF = json_decode($academicRecord->payload);

            $pdf = $this->generateRecordPdf($dataPDF);

            return response($pdf->Output('', 'S'), 200)
                ->header('Content-Type', 'application/pdf');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el acta de notas');
        }
    }

    public function generateRecordPdf($data)
    {

        $group = $data->group;
        $students = $data->students;
        $userInitials = $data->userInitials;
        $watermark = $data->watermark;

        $mpdf = new \Mpdf\Mpdf(
            [
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 5,
                'margin_footer' => 5,
                'showWatermarkText' => $watermark,
            ]
        );

        $htmlContent =  view('pdf.AcademicRecord.index', compact('students', 'group'))->render();
        $htmlHeader =  view('pdf.AcademicRecord._header')->render();
        $htmlFooter =  view('pdf.AcademicRecord._footer', compact('userInitials'))->render();
        $mpdf->SetWatermarkText('VISTA PREVIA',  0.1);

        $mpdf->SetHTMLHeader($htmlHeader);
        $mpdf->SetHTMLFooter($htmlFooter);
        $mpdf->SetTitle('Acta de Notas [' . $group->name . ' - ' . $group->year . ' ' . $group->month . ']');

        $mpdf->WriteHTML($htmlContent);

        return $mpdf;
    }

    public function generateErrorPdf($message)
    {
        $mpdf = new \Mpdf\Mpdf(
            [
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 5,
                'margin_footer' => 5,
            ]
        );
        $mpdf->SetTitle('Error al generar el PDF');
        $mpdf->WriteHTML('<h1>Error</h1><p>' . $message . '</p>');
        return $mpdf;
    }
}
