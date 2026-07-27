<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkPlan extends Model
{
    protected $table = 'workplan';

    protected $fillable = [
    'user_id',
    'strategic_perspective',
    'strategic_objective',
    'major_program',
    'strategic_measure',
    'strategic_initiatives',
    'success_indicator',
    'year',
    'q1',
    'q2',
    'q3',
    'q4',
    'total',
    'remarks',
    'r_center',
    'form_id',
    'status',
    'department',
    'attachments',
    'comments',
    'sort_order',
];

protected $casts = [
    'attachments' => 'array',
];

public function financialPlans()
{
   return $this->hasMany(FinancialPlan::class, 'workplan_id');
}

public function form()
    {
        return $this->belongsTo(Form::class, 'form_id'); 
    }
}
