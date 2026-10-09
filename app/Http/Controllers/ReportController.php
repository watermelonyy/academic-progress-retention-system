<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Semester;
use App\Models\StudentStatusHistory;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    /**
     * ============================================================
     * REPORT SELECTION PAGE
     * ============================================================
     */
    public function index()
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | GET ALL SEMESTERS
        |--------------------------------------------------------------------------
        | PostgreSQL sorting:
        | 2025-2026
        | 2024-2025
        | 2023-2024
        |--------------------------------------------------------------------------
        */

        $semesters = Semester::orderByRaw("
            CAST(split_part(school_year, '-', 1) AS INTEGER) DESC
        ")
        ->orderByRaw("
            CASE
                WHEN LOWER(semester_name) LIKE '%1st%' THEN 1
                WHEN LOWER(semester_name) LIKE '%first%' THEN 1
                WHEN LOWER(semester_name) LIKE '%2nd%' THEN 2
                WHEN LOWER(semester_name) LIKE '%second%' THEN 2
                WHEN LOWER(semester_name) LIKE '%summer%' THEN 3
                ELSE 4
            END ASC
        ")
        ->get();

        /*
        |--------------------------------------------------------------------------
        | UNIQUE SCHOOL YEARS
        |--------------------------------------------------------------------------
        */

        $schoolYears = $semesters
            ->pluck('school_year')
            ->filter()
            ->unique()
            ->values();

        return view('reports.index', compact(
            'semesters',
            'schoolYears'
        ));
    }


    /**
     * ============================================================
     * GENERATE REPORT
     * ============================================================
     */
    public function generate(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'report_type' => [
                'required',
                'in:status,no_improvement,serious_concerns'
            ],

            'school_year' => [
                'nullable',
                'string',
                'max:50'
            ],

            'semester_id' => [
                'nullable',
                'integer'
            ],

            'academic_status' => [
                'nullable',
                'string',
                'max:100'
            ],
        ]);


        $reportType = $validated['report_type'];
        $schoolYear = $validated['school_year'] ?? null;
        $semesterId = $validated['semester_id'] ?? null;
        $academicStatus = $validated['academic_status'] ?? null;


        /*
        |--------------------------------------------------------------------------
        | FIND SELECTED SEMESTER
        |--------------------------------------------------------------------------
        */

        $selectedSemester = null;

        if ($semesterId) {

            $selectedSemester = Semester::where(
                'semester_id',
                $semesterId
            )->first();

            if (!$selectedSemester) {
                return back()
                    ->withInput()
                    ->with('error', 'The selected semester could not be found.');
            }

            /*
            |--------------------------------------------------------------------------
            | IF BOTH SCHOOL YEAR AND SEMESTER WERE SELECTED,
            | MAKE SURE THEY MATCH.
            |--------------------------------------------------------------------------
            */

            if (
                $schoolYear &&
                $selectedSemester->school_year !== $schoolYear
            ) {
                return back()
                    ->withInput()
                    ->with('error', 'The selected school year and semester do not match.');
            }

            $schoolYear = $selectedSemester->school_year;
        }


        /*
        |--------------------------------------------------------------------------
        | BASE QUERY
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | We query STUDENT STATUS HISTORY, not latestStatus().
        |
        | This is what allows the report to show historical records.
        |--------------------------------------------------------------------------
        */

        $statusQuery = StudentStatusHistory::query()
            ->with([
                'student',
                'semester'
            ])
            ->whereHas('student');


        /*
        |--------------------------------------------------------------------------
        | FILTER BY SCHOOL YEAR
        |--------------------------------------------------------------------------
        */

        if ($schoolYear) {

            $statusQuery->whereHas('semester', function ($query) use ($schoolYear) {

                $query->where(
                    'school_year',
                    $schoolYear
                );

            });
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER BY SEMESTER
        |--------------------------------------------------------------------------
        */

        if ($semesterId) {

            $statusQuery->where(
                'semester_id',
                $semesterId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC STATUS REPORT
        |--------------------------------------------------------------------------
        */

        if ($reportType === 'status') {

            if ($academicStatus) {

                $statusQuery->where(
                    'academic_status',
                    $academicStatus
                );
            }

            $records = $statusQuery
                ->orderBy('student_id')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | REPORT TITLE
            |--------------------------------------------------------------------------
            */

            $reportTitle = 'Students by Academic Status';

            if ($academicStatus) {
                $reportTitle .= ': ' . $academicStatus;
            }


            $reportSubtitle = $this->buildPeriodText(
                $schoolYear,
                $selectedSemester
            );
        }


        /*
        |--------------------------------------------------------------------------
        | NO ACADEMIC IMPROVEMENT
        |--------------------------------------------------------------------------
        */

        elseif ($reportType === 'no_improvement') {

            $records = $statusQuery
                ->orderBy('student_id')
                ->get();


            /*
            |--------------------------------------------------------------------------
            | DETERMINE WHETHER THE STUDENT DID NOT IMPROVE
            |--------------------------------------------------------------------------
            |
            | The comparison follows the same basic logic used by the
            | StudentController:
            |
            | current failure percentage >= previous failure percentage
            |
            |--------------------------------------------------------------------------
            */

            $records = $records->filter(function ($currentRecord) {

                $previousRecord = StudentStatusHistory::where(
                    'student_id',
                    $currentRecord->student_id
                )
                ->where('status_id', '<>', $currentRecord->status_id)
                ->where('created_at', '<', $currentRecord->created_at)
                ->orderByDesc('created_at')
                ->first();

                if (!$previousRecord) {
                    return false;
                }

                $currentPercentage = (float) $currentRecord->failure_percentage;
                $previousPercentage = (float) $previousRecord->failure_percentage;

                return $currentPercentage >= $previousPercentage;
            })
            ->values();


            $reportTitle = 'Students With No Academic Improvement';

            $reportSubtitle = $this->buildPeriodText(
                $schoolYear,
                $selectedSemester
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SERIOUS ACADEMIC CONCERNS
        |--------------------------------------------------------------------------
        */

        else {

            /*
            |--------------------------------------------------------------------------
            | Existing priority_alert field is used.
            |--------------------------------------------------------------------------
            |
            | We do NOT create another evaluation rule.
            | The report uses the existing priority alert recorded
            | in student_status_history.
            |--------------------------------------------------------------------------
            */

            $statusQuery->where(
                'priority_alert',
                true
            );

            $records = $statusQuery
                ->orderBy('student_id')
                ->get();


            $reportTitle = 'Students With Serious Academic Concerns';

            $reportSubtitle = $this->buildPeriodText(
                $schoolYear,
                $selectedSemester
            );
        }


        /*
        |--------------------------------------------------------------------------
        | REMOVE DUPLICATE STUDENT RECORDS
        |--------------------------------------------------------------------------
        |
        | If the query is not restricted to one semester, a student may
        | have more than one historical evaluation.
        |
        | For the report we keep one record per student.
        |--------------------------------------------------------------------------
        */

        if (!$semesterId) {

            $records = $records
                ->sortByDesc(function ($record) {

                    return $record->created_at
                        ? $record->created_at->timestamp
                        : 0;
                })
                ->unique('student_id')
                ->values();
        }


        /*
        |--------------------------------------------------------------------------
        | SORT STUDENTS ALPHABETICALLY
        |--------------------------------------------------------------------------
        */

        $records = $records
            ->sortBy(function ($record) {

                if (!$record->student) {
                    return '';
                }

                return strtolower(
                    trim(
                        ($record->student->last_name ?? '') .
                        ' ' .
                        ($record->student->first_name ?? '') .
                        ' ' .
                        ($record->student->middle_name ?? '')
                    )
                );
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | REPORT DATE
        |--------------------------------------------------------------------------
        */

        $generatedAt = now();


        /*
        |--------------------------------------------------------------------------
        | GENERATE PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'reports.pdf',
            compact(
                'records',
                'reportTitle',
                'reportSubtitle',
                'generatedAt',
                'reportType',
                'academicStatus'
            )
        );


        /*
        |--------------------------------------------------------------------------
        | LANDSCAPE
        |--------------------------------------------------------------------------
        |
        | Landscape is used so the report has enough room for:
        | Complete Name
        | Year Level
        | Academic Status
        | Failure Percentage
        |--------------------------------------------------------------------------
        */

        $pdf->setPaper('A4', 'landscape');


        /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

        $fileName = 'Academic_Report';

        if ($schoolYear) {

            $safeSchoolYear = preg_replace(
                '/[^A-Za-z0-9\-]/',
                '_',
                $schoolYear
            );

            $fileName .= '_' . $safeSchoolYear;
        }

        if ($academicStatus) {

            $safeStatus = preg_replace(
                '/[^A-Za-z0-9\-]/',
                '_',
                $academicStatus
            );

            $fileName .= '_' . $safeStatus;
        }

        $fileName .= '.pdf';


        /*
        |--------------------------------------------------------------------------
        | RETURN PDF
        |--------------------------------------------------------------------------
        */

        return $pdf->stream($fileName);
    }


    /**
     * ============================================================
     * BUILD REPORT PERIOD TEXT
     * ============================================================
     */
    private function buildPeriodText(
        ?string $schoolYear,
        $selectedSemester = null
    ): string {

        if ($selectedSemester) {

            return 'School Year: ' .
                $selectedSemester->school_year .
                ' | ' .
                $selectedSemester->semester_name;
        }

        if ($schoolYear) {

            return 'School Year: ' . $schoolYear;
        }

        return 'All Recorded School Years and Semesters';
    }
}