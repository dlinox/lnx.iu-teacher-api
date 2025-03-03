<?php

namespace App\Modules\EnrollmentGrade\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentGradeDataTableItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'isActive' => $this->is_active,
        ];
    }
}
