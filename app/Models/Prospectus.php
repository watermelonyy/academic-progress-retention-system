<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prospectus extends Model
{
    protected $table = 'prospectus';

    protected $primaryKey = 'subject_id';

    public $timestamps = false;

    protected $fillable = [
        'subject_code',
        'subject_title',
        'units',
        'year_level',
        'semester',
        'prerequisite'
    ];
}