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
        | Students without an evaluation are considered Regular.
        | Old database values such as "For Shifting Out" are converted
        | to the current display value "Shifting Out".
        |
        */

        $students = $students->map(function ($student) {

            $status = optional(
                $student->latestStatus
            )->academic_status ?? 'Regular';


            if ($status === 'For Shifting Out') {

                $status = 'Shifting Out';

            }


            /*
            | Old records may contain Residency Risk as an academic status.
            | Residency Risk is now a warning only, so keep the actual
            | academic status as Regular for dashboard purposes.
            */
            if ($status === 'Residency Risk') {

                $status = 'Regular';

            }


            $student->dashboard_status = $status;

            return $student;

        });


        /*
        |--------------------------------------------------------------------------
        | RESIDENCY RISK PROJECTION
        |--------------------------------------------------------------------------
        |
        | Residency Risk is a warning only. It is NOT an academic status.
        |
        | Use the exact same residency calculation used by StudentController.
        | This keeps the Student List and Home dashboard synchronized.
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

                return $student->dashboard_status === 'Regular';

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
        | IMPORTANT:
        | Residency Risk is NOT checked through dashboard_status.
        | It is calculated separately by StudentController and stored as
        | the temporary residency_risk property on each student model.
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
        | Priority Alert is based ONLY on academic performance comparison.
        |
        | Current failure < Previous failure
        |     = Improvement
        |     = NO Priority Alert
        |
        | Current failure = Previous failure
        |     = No improvement
        |     = Priority Alert
        |
        | Current failure > Previous failure
        |     = Worse
        |     = Priority Alert
        |
        | A student with only one evaluation can NEVER be a Priority Alert.
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

            $student->dashboard_status =
                $latestEvaluation->academic_status;


            if (
                $student->dashboard_status ===
                'For Shifting Out'
            ) {

                $student->dashboard_status =
                    'Shifting Out';

            }


            if (
                $student->dashboard_status ===
                'Residency Risk'
            ) {

                $student->dashboard_status =
                    'Regular';

            }


            $student->priority_alert = true;


            $priorityStudents->push(
                $student
            );

        }


        $priorityAlerts =
            $priorityStudents->count();


        /*
        |--------------------------------------------------------------------------
        | ALL EVALUATIONS
        |--------------------------------------------------------------------------
        |
        | This collection is used for:
        |
        | 1. Recently Evaluated Students
        | 2. School Year Performance
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
        | Show exactly 3 UNIQUE students.
        |
        | If a student has multiple evaluations, only the student's
        | most recent evaluation is displayed.
        |
        | created_at is used first to determine the newest evaluation.
        | status_id is used as a secondary tie-breaker.
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
        | Counts UNIQUE students who were flagged with:
        |
        | - Academic Warning
        | - Probation
        | - Shifting Out
        |
        | A student is counted only once per school year even if
        | the student has multiple flagged evaluations in that year.
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
            array_keys(
                $schoolYearPerformance
            );


        $schoolYearCounts =
            array_values(
                $schoolYearPerformance
            );


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
