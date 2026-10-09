<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentStatusHistory extends Model
{
    protected $table = 'student_status_history';

    protected $primaryKey = 'status_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'semester_id',
        'total_subjects',
        'academic_status',
        'failure_percentage',
        'priority_alert',
        'remarks',
        'created_at',
    ];

    protected $casts = [
        'total_subjects' => 'integer',
        'failure_percentage' => 'decimal:2',
        'priority_alert' => 'boolean',
        'created_at' => 'datetime',
    ];

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

    public function notices()
    {
        return $this->hasMany(
            AcademicNotice::class,
            'status_id',
            'status_id'
        );
    }
}