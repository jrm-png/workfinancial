<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPlan extends Model
{
    protected $table = 'financialplans';

    protected $fillable = [
        'user_id',
        'funds',
        'programs',
        'projects',
        'activity',
        'description',
        'expense_class',
        'account_title',
        'year',
        'amount',
        'q1',
        'q2',
        'q3',
        'q4',
        'total',
        'r_center',
        'form_id',
        'department',
        'workplan_id',
    ];

public function workPlan()
{
    return $this->belongsTo(WorkPlan::class, 'workplan_id');
}
public function form()
    {
        return $this->belongsTo(Form::class, 'form_id'); 
    }
}
