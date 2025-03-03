<?php

namespace App\Modules\Course\Models;

use App\Traits\HasDataTable;
use App\Traits\HasEnabledState;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasDataTable, HasEnabledState;

    protected $fillable = [
        'name',
        'description',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public static function geCurriculumCourses($curriculum_id, $module_id)
    {
        $courses = self::select(
            'courses.id as id',
            'curriculum_courses.id as curriculumCourseId',
            'courses.name as name',
            'courses.description as description',
            'courses.is_enabled as isEnabled',
            'curriculum_courses.code as code',
            'curriculum_courses.credits as credits',
            'curriculum_courses.hours_practice as hoursPractice',
            'curriculum_courses.hours_theory as hoursTheory',
            'areas.name as area',
        )
            ->distinct()
            ->join('curriculum_courses', 'courses.id', '=', 'curriculum_courses.course_id')
            ->join('areas', 'curriculum_courses.area_id', '=', 'areas.id')
            ->where('curriculum_courses.curriculum_id', $curriculum_id)
            ->where('curriculum_courses.module_id', $module_id)
            ->get();

        return $courses;
    }
}
