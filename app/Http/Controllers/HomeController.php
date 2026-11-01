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
        | GET ALL EVALUATIONS
        |--------------------------------------------------------------------------
        |
        | Use one consistent evaluation source for the dashboard.
        | Latest evaluation:
        | 1. Highest semester_id.
        | 2. Highest status_id if semester_id is the same.
        |
        */

        $allEvaluations = StudentStatusHistory::with([
            'student',
            'semester'
        ])
            ->orderByDesc('semester_id')
            ->orderByDesc('status_id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | GROUP EVALUATIONS BY STUDENT
        |--------------------------------------------------------------------------
        */

        $historiesByStudent = $allEvaluations
            ->groupBy('student_id');


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE ACADEMIC STATUS
        |--------------------------------------------------------------------------
        |
        | Regular, Below Treshold, and old Residency Risk records
        | are treated as Below Threshold.
        |
        | Residency prediction remains a separate feature.
        |
        */

        $normalizeStatus = function ($status) {

            $status = trim((string) $status);

            $normalized = strtolower($status);

            if (in_array($normalized, [
                'regular',
                'below threshold',
                'below treshold',
                'residency risk'
            ], true)) {

                return 'Below Threshold';
            }

            if ($normalized === 'for shifting out') {

                return 'Shifting Out';
            }

            return $status;
        };


        /*
        |--------------------------------------------------------------------------
        | GET ALL STUDENTS
        |--------------------------------------------------------------------------
        */

        $students = Student::orderBy('last_name')
            ->orderBy('first_name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | ASSIGN EACH STUDENT'S LATEST ACADEMIC STATUS
        |--------------------------------------------------------------------------
        */

        $students = $students->map(function ($student) use (
            $historiesByStudent,
            $normalizeStatus
        ) {

            $studentHistories = $historiesByStudent->get(
                $student->student_id,
                collect()
            );

            $latestEvaluation = $studentHistories->first();

            $status = $latestEvaluation
                ? $normalizeStatus($latestEvaluation->academic_status)
                : 'Below Threshold';

            $student->latest_dashboard_evaluation =
                $latestEvaluation;

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

            $projection = $studentController->getResidencyProjection(
                $student
            );

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
        | STUDENTS BY LATEST ACADEMIC STATUS
        |--------------------------------------------------------------------------
        |
        | Each student is included in only one academic status group.
        | Only the student's latest evaluation determines the group.
        |
        */

        $regularStudents = $students
            ->filter(function ($student) {

                return $student->dashboard_status ===
                    'Below Threshold';

            })
            ->values();


        $warningStudents = $students
            ->filter(function ($student) {

                return $student->dashboard_status ===
                    'Academic Warning';

            })
            ->values();


        $probationStudents = $students
            ->filter(function ($student) {

                return $student->dashboard_status ===
                    'Probation';

            })
            ->values();


        $shiftingStudents = $students
            ->filter(function ($student) {

                return $student->dashboard_status ===
                    'Shifting Out';

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
        |
        | The card counts and modal lists use the SAME collections.
        |
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

            $evaluations = $historiesByStudent->get(
                $student->student_id,
                collect()
            );


            $latestEvaluation = $evaluations->first();

            $previousEvaluation = $evaluations
                ->skip(1)
                ->first();


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

            if ($currentFailure < $previousFailure) {

                continue;

            }


            /*
            |--------------------------------------------------------------------------
            | NO IMPROVEMENT OR WORSE = PRIORITY ALERT
            |--------------------------------------------------------------------------
            */

            $student->dashboard_status = $normalizeStatus(
                $latestEvaluation->academic_status
            );

            $student->priority_alert = true;

            $priorityStudents->push($student);

        }


        $priorityAlerts = $priorityStudents->count();


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
        | Counts unique students flagged with Academic Warning,
        | Probation, or Shifting Out during each school year.
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
                        trim((string) $history->academic_status),
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
