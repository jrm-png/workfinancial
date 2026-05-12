<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
    'submission_start',
    'submission_end',
    'is_viewing_open',
    ''
];

}
