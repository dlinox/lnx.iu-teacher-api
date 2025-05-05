<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GradeDeadline extends Model
{

    protected $fillable = [
        'start_date',
        'end_date',
        'type',
        'reference_id',
        'observations',
        'period_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public static function activeGradePeriod()
    {
        $period = self::select(
            'grade_deadlines.period_id as periodId',
            'grade_deadlines.type',
            'grade_deadlines.start_date as startDate',
            'grade_deadlines.end_date as endDate',
            DB::raw('CONCAT(periods.year, "-", upper(months.name)) as period')
        )
            ->join('periods', 'grade_deadlines.period_id', '=', 'periods.id')
            ->join('months', 'periods.month', '=', 'months.id')
            ->where('grade_deadlines.start_date', '<=', now())  // Verifica que el período ya inició
            ->where('grade_deadlines.end_date', '>=', now())   // Verifica que el período no haya terminado
            ->first();

        return $period ? $period->toArray() : null;
    }
    public static function getGradeDeadlineByPeriod($periodId)
    {
        $period = self::select('grade_deadlines.id')
            ->where('grade_deadlines.period_id', $periodId)
            ->where('grade_deadlines.start_date', '<=', now())  // Verifica que el período ya inició
            ->where('grade_deadlines.end_date', '>=', now())   // Verifica que el período no haya terminado
            ->first();

        return $period ? $period->toArray() : null;
    }
}
