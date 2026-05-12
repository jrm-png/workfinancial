<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dropdown extends Model
{
    protected $table = 'dropdown_settings';

    protected $fillable = [
    'type',
    'value',
];

}
