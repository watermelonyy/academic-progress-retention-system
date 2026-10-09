<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicGrade extends Model
{
    protected $table = 'academic_grades';

    protected $primaryKey = 'grade_id';

    protected $fillable = [

        'student_id',
        'semester_id',
        'subject_id',
        'encoded_by',
        'grade',
        'attempt_no',
        'date_encoded'

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(
            Student::class,
            'student_id',
            'student_id'
        );
    }

    public function semester()
    {
        return $this->belongsTo(
            Semester::class,
            'semester_id',
            'semester_id'
        );
    }

    public function subject()
    {
        return $this->belongsTo(
            Prospectus::class,
            'subject_id',
            'subject_id'
        );
    }

    public function encoder()
    {
        return $this->belongsTo(
            User::class,
            'encoded_by',
            'user_id'
        );
    }
}