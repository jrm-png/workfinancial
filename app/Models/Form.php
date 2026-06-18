<?php

namespace App\Models; // Check if this matches your folder structure

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Form extends Model // The class name MUST match the filename Form.php
{
    use HasFactory;

    protected $fillable = [
        'form_ref', 'year', 'department_id', 'status', 'created_by', 'approved_at', 'approved_by' , 'attachments',
    ];

    public function workPlans() {
    return $this->hasMany(WorkPlan::class);
}

public function financialPlans() {
    return $this->hasMany(FinancialPlan::class);
}

public function form()
    {
        return $this->belongsTo(Form::class, 'form_id'); 
    }
}