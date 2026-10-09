<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';

    protected $primaryKey = 'student_id';

    public $timestamps = false;

    protected $fillable = [
        'student_no',
        'first_name',
        'middle_name',
        'last_name',
        'admission_year',
        'current_year_level',
    ];


    /*
    |--------------------------------------------------------------------------
    | ACADEMIC GRADES
    |--------------------------------------------------------------------------
    */

    public function academicGrades()
    {
        return $this->hasMany(
            AcademicGrade::class,
            'student_id',
            'student_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS HISTORY
    |--------------------------------------------------------------------------
    */

    public function statusHistory()
    {
        return $this->hasMany(
            StudentStatusHistory::class,
            'student_id',
            'student_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LATEST STATUS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | The latest academic status is based on the latest semester,
    | not created_at.
    |
    | semester_id is expected to increase as new semesters are added.
    |
    |--------------------------------------------------------------------------
    */

    public function latestStatus()
    {
        return $this->hasOne(
            StudentStatusHistory::class,
            'student_id',
            'student_id'
        )->latestOfMany('semester_id');
    }
}