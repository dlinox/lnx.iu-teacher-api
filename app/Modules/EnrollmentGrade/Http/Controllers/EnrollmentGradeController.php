<?php

namespace App\Modules\EnrollmentGrade\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\EnrollmentGrade\Http\Resources\EnrollmentGradeDataTableItemResource;
use App\Modules\EnrollmentGrade\Models\EnrollmentGrade;
use App\Http\Responses\ApiResponse;
use App\Modules\EnrollmentGrade\Http\Requests\EnrollmentGradeSaveRequest;
use App\Modules\EnrollmentGrade\Http\Resources\EnrollmentGradeFormItemResource;

class EnrollmentGradeController extends Controller
{
    public function loadDataTable(Request $request)
    {
        try {
            $items = EnrollmentGrade::dataTable($request);
            EnrollmentGradeDataTableItemResource::collection($items);
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar los registros');
        }
    }

    public function getFormItem(Request $request)
    {
        try {
            $item = EnrollmentGrade::find($request->id);
            return ApiResponse::success(EnrollmentGradeFormItemResource::make($item));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar el registro');
        }
    }

    public function saveItem(EnrollmentGradeSaveRequest $request)
    {
        try {
            $data = $request->validated();
            EnrollmentGrade::updateOrCreate(['id' => $request->id], $data);
            return ApiResponse::success(null, 'Registro guardado correctamente');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al guardar el registro');
        }
    }

    public function deleteItem(Request $request)
    {
        try {
            EnrollmentGrade::destroy($request->id);
            return ApiResponse::success(null, 'Registro eliminado correctamente');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el registro');
        }
    }
}
