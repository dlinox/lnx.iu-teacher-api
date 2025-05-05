<?php

namespace App\Modules\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupDataTableItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'course' => $this->course,
            'period' => $this->period,
            'periodId' => $this->period_id,
            'modality' => $this->modality,
            'curriculum' => $this->curriculum,
        ];
    }
}
