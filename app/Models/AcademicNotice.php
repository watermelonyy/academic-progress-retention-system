<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicNotice extends Model
{
    protected $table = 'academic_notices';

    protected $primaryKey = 'notice_id';

    protected $fillable = [
        'student_id',
        'semester_id',
        'status_id',
        'notice_type',
        'pdf_path',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
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

    public function status()
    {
        return $this->belongsTo(
            StudentStatusHistory::class,
            'status_id',
            'status_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NOTICE ACTION STATUS
    |--------------------------------------------------------------------------
    |
    | Print/download actions are stored in:
    |
    | storage/app/notice_actions.json
    |
    | This allows every Print/Download action to be preserved with its
    | exact date and time without changing the academic_notices table.
    |
    */

    public function getActionStatusAttribute(): array
    {
        $file = storage_path('app/notice_actions.json');

        $default = [
            'printed' => false,
            'printed_at' => null,
            'downloaded' => false,
            'downloaded_at' => null,
        ];

        if (!file_exists($file)) {
            return $default;
        }

        $contents = file_get_contents($file);

        if (!$contents) {
            return $default;
        }

        $data = json_decode($contents, true);

        if (!is_array($data)) {
            return $default;
        }

        $noticeData = $data[(string) $this->notice_id] ?? [];

        return array_merge(
            $default,
            is_array($noticeData) ? $noticeData : []
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NOTICE ACTION HISTORY
    |--------------------------------------------------------------------------
    */

    public function getActionHistoryAttribute(): array
    {
        $file = storage_path('app/notice_actions.json');

        if (!file_exists($file)) {
            return [];
        }

        $contents = file_get_contents($file);

        if (!$contents) {
            return [];
        }

        $data = json_decode($contents, true);

        if (!is_array($data)) {
            return [];
        }

        $noticeData = $data[(string) $this->notice_id] ?? [];

        if (
            isset($noticeData['actions']) &&
            is_array($noticeData['actions'])
        ) {
            return $noticeData['actions'];
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | SAVED NOTICE SNAPSHOT
    |--------------------------------------------------------------------------
    |
    | For Probation notices, the actual PDF already contains the five
    | priority subjects.
    |
    | A JSON snapshot is also kept beside the notice PDF so the system
    | has a machine-readable historical copy without adding database
    | columns.
    |
    */

    public function getSnapshotAttribute(): array
    {
        $file = storage_path(
            'app/public/notices/records/' .
            $this->notice_id .
            '.json'
        );

        if (!file_exists($file)) {
            return [];
        }

        $contents = file_get_contents($file);

        if (!$contents) {
            return [];
        }

        $data = json_decode($contents, true);

        return is_array($data) ? $data : [];
    }
}