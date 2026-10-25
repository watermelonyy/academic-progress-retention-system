<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentStatusHistory;

class HomeController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | GET ALL STUDENTS WITH THEIR LATEST ACADEMIC STATUS
        |--------------------------------------------------------------------------
        */

        $students = Student::with([
            'latestStatus.semester',
            'statusHistory'
        ])
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE STUDENT STATUS
        |--------------------------------------------------------------------------
        |
        | Students without an evaluation are considered Below Treshold.
        | Legacy academic status values are normalized where appropriate.
        | Residency Risk is kept separate and is NOT converted to
        | Below Treshold.
        |
        */

        $students = $students->map(function ($student) {

            $status = trim(
                (string) (
                    optional($student->latestStatus)->academic_status
                    ?? 'Below Treshold'
                )
            );

            if (in_array($status, [
                'Regular',
                'Below Threshold'
            ], true)) {
                $status = 'Below Treshold';
            }

            if ($status === 'For Shifting Out') {
                $status = 'Shifting Out';
            }

            $student->dashboard_status = $status;

            return $student;
        });


        /*
        |--------------------------------------------------------------------------
        | RESIDENCY RISK PROJECTION
        |--------------------------------------------------------------------------
        |
        | Residency Risk is calculated separately from academic status.
        |
        */

        $studentController = app(StudentController::class);

        $students->each(function ($student) use ($studentController) {

            $projection =
                $studentController->getResidencyProjection($student);

            $student->residency_risk =
                $projection['residency_risk'];

            $student->residency_exceeded =
                $projection['residency_exceeded'];

            $student->projected_residency_years =
                $projection['projected_residency_years'];

            $student->residency_delayed_subjects =
                $projection['delayed_subjects'];

        });


        /*
        |--------------------------------------------------------------------------
        | TOTAL STUDENTS
        |--------------------------------------------------------------------------
        */

        $totalStudents = $students->count();


        /*
        |--------------------------------------------------------------------------
        | STUDENTS BY CURRENT ACADEMIC STATUS
        |--------------------------------------------------------------------------
        */

        $regularStudents = $students
            ->filter(function ($student) {
                return $student->dashboard_status === 'Below Treshold';
            })
            ->values();

        $warningStudents = $students
            ->filter(function ($student) {
                return $student->dashboard_status === 'Academic Warning';
            })
            ->values();

        $probationStudents = $students
            ->filter(function ($student) {
                return $student->dashboard_status === 'Probation';
            })
            ->values();

        $shiftingStudents = $students
            ->filter(function ($student) {
                return $student->dashboard_status === 'Shifting Out';
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | RESIDENCY RISK STUDENTS
        |--------------------------------------------------------------------------
        |
        | This collection is based on the separate residency projection,
        | not on the student's academic status.
        |
        */

        $residencyRiskStudents = $students
            ->filter(function ($student) {
                return !empty($student->residency_risk);
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | STATUS COUNTS
        |--------------------------------------------------------------------------
        */

        $regular = $regularStudents->count();

        $warning = $warningStudents->count();

        $probation = $probationStudents->count();

        $shifting = $shiftingStudents->count();

        $residencyRisk = $residencyRiskStudents->count();


        /*
        |--------------------------------------------------------------------------
        | PRIORITY ALERTS
        |--------------------------------------------------------------------------
        |
        | Current failure < Previous failure = Improvement
        | Current failure = Previous failure = Priority Alert
        | Current failure > Previous failure = Priority Alert
        |
        | A student with only one evaluation cannot be a Priority Alert.
        |
        */

        $priorityStudents = collect();

        foreach ($students as $student) {

            $evaluations = $student->statusHistory
                ->sortByDesc(function ($history) {

                    return [
                        (int) $history->semester_id,
                        (int) $history->status_id
                    ];

                })
                ->values();


            $latestEvaluation =
                $evaluations->first();

            $previousEvaluation =
                $evaluations->skip(1)->first();


            /*
            |--------------------------------------------------------------------------
            | STUDENT MUST HAVE AT LEAST TWO EVALUATIONS
            |--------------------------------------------------------------------------
            */

            if (
                !$latestEvaluation ||
                !$previousEvaluation
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | COMPARE FAILURE PERCENTAGES
            |--------------------------------------------------------------------------
            */

            $currentFailure =
                (float) $latestEvaluation->failure_percentage;

            $previousFailure =
                (float) $previousEvaluation->failure_percentage;


            /*
            |--------------------------------------------------------------------------
            | IMPROVEMENT = NO PRIORITY ALERT
            |--------------------------------------------------------------------------
            */

            if (
                $currentFailure <
                $previousFailure
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | NO IMPROVEMENT OR WORSE = PRIORITY ALERT
            |--------------------------------------------------------------------------
            */

            $priorityStatus = trim(
                (string) $latestEvaluation->academic_status
            );


            /*
            |--------------------------------------------------------------------------
            | NORMALIZE ACADEMIC STATUS FOR PRIORITY ALERT DISPLAY
            |--------------------------------------------------------------------------
            |
            | Do not convert Residency Risk into Below Treshold.
            |
            */

            if (in_array($priorityStatus, [
                'Regular',
                'Below Threshold'
            ], true)) {
                $priorityStatus = 'Below Treshold';
            }

            if ($priorityStatus === 'For Shifting Out') {
                $priorityStatus = 'Shifting Out';
            }

            $student->dashboard_status = $priorityStatus;

            $student->priority_alert = true;

            $priorityStudents->push($student);

        }


        $priorityAlerts =
            $priorityStudents->count();


        /*
        |--------------------------------------------------------------------------
        | ALL EVALUATIONS
        |--------------------------------------------------------------------------
        |
        | Used for Recently Evaluated Students and School Year Performance.
        |
        */

        $allEvaluations = StudentStatusHistory::with([
            'student',
            'semester'
        ])
        ->get();


        /*
        |--------------------------------------------------------------------------
        | RECENTLY EVALUATED STUDENTS
        |--------------------------------------------------------------------------
        |
        | Show the three most recent unique students.
        |
        */

        $recentlyEvaluated = $allEvaluations
            ->filter(function ($history) {

                return $history->student !== null;

            })
            ->sort(function ($a, $b) {

                $aTimestamp =
                    optional($a->created_at)->timestamp ?? 0;

                $bTimestamp =
                    optional($b->created_at)->timestamp ?? 0;


                if ($aTimestamp === $bTimestamp) {

                    return
                        (int) $b->status_id
                        <=>
                        (int) $a->status_id;

                }


                return
                    $bTimestamp
                    <=>
                    $aTimestamp;

            })
            ->groupBy('student_id')
            ->map(function ($studentEvaluations) {

                return $studentEvaluations->first();

            })
            ->sort(function ($a, $b) {

                $aTimestamp =
                    optional($a->created_at)->timestamp ?? 0;

                $bTimestamp =
                    optional($b->created_at)->timestamp ?? 0;


                if ($aTimestamp === $bTimestamp) {

                    return
                        (int) $b->status_id
                        <=>
                        (int) $a->status_id;

                }


                return
                    $bTimestamp
                    <=>
                    $aTimestamp;

            })
            ->take(3)
            ->values();


        /*
        |--------------------------------------------------------------------------
        | SCHOOL YEAR PERFORMANCE
        |--------------------------------------------------------------------------
        |
        | Counts unique students flagged with Academic Warning, Probation,
        | or Shifting Out during each school year.
        |
        */

        $flaggedStatuses = [
            'Academic Warning',
            'Probation',
            'Shifting Out',
            'For Shifting Out'
        ];


        $schoolYearPerformance = $allEvaluations
            ->filter(function ($history) use ($flaggedStatuses) {

                return
                    $history->semester &&
                    $history->student &&
                    in_array(
                        $history->academic_status,
                        $flaggedStatuses,
                        true
                    );

            })
            ->groupBy(function ($history) {

                return $history->semester->school_year;

            })
            ->map(function ($schoolYearEvaluations) {

                return $schoolYearEvaluations
                    ->unique('student_id')
                    ->count();

            })
            ->sortKeys()
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | SCHOOL YEAR CHART DATA
        |--------------------------------------------------------------------------
        */

        $schoolYearLabels =
            array_keys($schoolYearPerformance);

        $schoolYearCounts =
            array_values($schoolYearPerformance);


        /*
        |--------------------------------------------------------------------------
        | SEND DATA TO DASHBOARD
        |--------------------------------------------------------------------------
        */

        return view(
            'home',
            compact(
                'students',
                'totalStudents',

                'regular',
                'warning',
                'probation',
                'shifting',
                'residencyRisk',

                'priorityAlerts',

                'regularStudents',
                'warningStudents',
                'probationStudents',
                'shiftingStudents',
                'residencyRiskStudents',

                'priorityStudents',

                'recentlyEvaluated',

                'schoolYearLabels',
                'schoolYearCounts'
            )
        );
    }
}