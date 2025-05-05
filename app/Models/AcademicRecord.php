<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicRecord extends Model
{
    protected $fillable = [
        'group_id',
        'created_by',
        'grade_deadline_id',
        'payload',
        'observations',
    ];

    protected $hidden = [];

    protected $casts = [
        'payload' => 'array',
        'group_id' => 'integer',
        'created_by' => 'integer',
        'grade_deadline_id' => 'integer',
    ];

    
}
