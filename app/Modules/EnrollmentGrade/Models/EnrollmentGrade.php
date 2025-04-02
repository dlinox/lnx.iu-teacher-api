<?php

namespace App\Modules\EnrollmentGrade\Models;

use App\Traits\HasDataTable;
use Illuminate\Database\Eloquent\Model;

class EnrollmentGrade extends Model
{
    use HasDataTable;
    protected $fillable = [
        'grade',
        'is_locked',
    ];
}
