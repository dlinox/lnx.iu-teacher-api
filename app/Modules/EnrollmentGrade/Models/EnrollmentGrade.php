<?php

namespace App\Modules\EnrollmentGrade\Models;

use App\Traits\HasDataTable;
use Illuminate\Database\Eloquent\Model;

class EnrollmentGrade extends Model
{
    use HasDataTable;

    protected $fillable = [
        'grade',
        'enrollment_group_id',
        'is_locked',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'is_locked' => 'boolean',
    ];
}
