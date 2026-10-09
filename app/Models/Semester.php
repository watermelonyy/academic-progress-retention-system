<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semesters';

    protected $primaryKey = 'semester_id';

    protected $fillable = [

        'school_year',
        'semester_name'

    ];
}