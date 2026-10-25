<?php 
 
namespace App\Http\Controllers; 
 
use App\Models\Student; 
use App\Models\AcademicGrade; 
use App\Models\StudentStatusHistory; 
use App\Models\Semester; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Validation\Rule; 
 
class StudentController extends Controller 
{ 

public function index(Request $request)
{
    if (!$request->session()->has('user_id')) {
        return redirect('/')
            ->with('error', 'Please login first.');
    }

    $search = trim($request->search ?? '');

    $students = Student::with([
        'latestStatus',
        'statusHistory.semester'
    ])
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ILIKE', "%{$search}%")
                    ->orWhere('middle_name', 'ILIKE', "%{$search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$search}%")
                    ->orWhere('student_no', 'ILIKE', "%{$search}%");
            });
        })
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | CALCULATE PRIORITY ALERT FROM EVALUATION HISTORY
    |--------------------------------------------------------------------------
    | Use the latest submitted evaluation, not the largest semester_id.
    | Compare distinct semester evaluations.
    |
    | Equal or increased failure percentage = Priority Alert.
    | Decreased failure percentage = No Priority Alert.
    |--------------------------------------------------------------------------
    */

    $students->each(function ($student) {

        $evaluations = $student->statusHistory
            ->filter(function ($history) {
                return $history->semester_id !== null;
            })
            ->groupBy('semester_id')
            ->map(function ($semesterEvaluations) {

                return $semesterEvaluations
                    ->sort(function ($a, $b) {

                        $dateComparison = strcmp(
                            (string) $b->created_at,
                            (string) $a->created_at
                        );

                        if ($dateComparison !== 0) {
                            return $dateComparison;
                        }

                        return (int) $b->status_id
                            <=> (int) $a->status_id;
                    })
                    ->first();

            })
            ->sort(function ($a, $b) {

                $dateComparison = strcmp(
                    (string) $b->created_at,
                    (string) $a->created_at
                );

                if ($dateComparison !== 0) {
                    return $dateComparison;
                }

                return (int) $b->status_id
                    <=> (int) $a->status_id;
            })
            ->values();

        $latestEvaluation = $evaluations->first();

        $previousEvaluation = $evaluations->skip(1)->first();

        $priorityAlert = false;

        if ($latestEvaluation && $previousEvaluation) {

            $currentFailure = (float) $latestEvaluation->failure_percentage;

            $previousFailure = (float) $previousEvaluation->failure_percentage;

            if ($currentFailure >= $previousFailure) {
                $priorityAlert = true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | USE THE SAME EVALUATION FOR THE DISPLAY AND ALERT
        |--------------------------------------------------------------------------
        */

        if ($latestEvaluation) {

            $latestEvaluation->priority_alert = $priorityAlert;

            if (in_array(
                $latestEvaluation->academic_status,
                ['Regular', 'Residency Risk'],
                true
            )) {
                $latestEvaluation->academic_status = 'Below Treshold';
            }

            $student->setRelation('latestStatus', $latestEvaluation);
        }

        /*
        |--------------------------------------------------------------------------
        | RESIDENCY WARNING FOR STUDENT LIST
        |--------------------------------------------------------------------------
        */

        $projection = $this->getResidencyProjection($student);

        $student->residency_risk =
            $projection['residency_risk'] ?? false;

        $student->projected_residency_years =
            $projection['projected_residency_years'] ?? null;

        $student->residency_exceeded =
            $projection['residency_exceeded'] ?? false;
    });

    return view(
        'students.index',
        compact('students')
    );
}

 
 
    /* 
    |-------------------------------------------------------------------------- 
    | CREATE 
    |-------------------------------------------------------------------------- 
    */ 
    public function create() 
    { 
        if (!session()->has('user_id')) { 
            return redirect('/') 
                ->with('error', 'Please login first.'); 
        } 
 
        $semesters = Semester::orderBy( 
            'semester_id', 
            'desc' 
        )->get(); 
 
        return view( 
            'students.create', 
            compact('semesters') 
        ); 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | STORE NEW STUDENT 
    |-------------------------------------------------------------------------- 
    */ 
    public function store(Request $request) 
    { 
        if (!$request->session()->has('user_id')) { 
            return redirect('/') 
                ->with('error', 'Please login first.'); 
        } 
 
        $request->validate([ 
 
            'student_no' => [ 
                'required', 
                'string', 
                'max:50', 
                'unique:students,student_no' 
            ], 
 
            'first_name' => [ 
                'required', 
                'string', 
                'max:100' 
            ], 
 
            'middle_name' => [ 
                'nullable', 
                'string', 
                'max:100' 
            ], 
 
            'last_name' => [ 
                'required', 
                'string', 
                'max:100' 
            ], 
 
            'admission_year' => [ 
                'required', 
                'integer', 
                'min:1900', 
                'max:2100' 
            ], 
 
            'current_year_level' => [ 
                'required', 
                'string', 
                'max:50' 
            ], 
 
            'semester_id' => [ 
                'required', 
                'exists:semesters,semester_id' 
            ], 
 
            'total_subjects' => [ 
                'required', 
                'integer', 
                'min:1', 
                'max:100' 
            ], 
 
            'subject_code' => [ 
                'nullable', 
                'array' 
            ], 
 
            'subject_code.*' => [ 
                'nullable', 
                'string', 
                'max:50' 
            ], 
 
            'grade' => [ 
                'nullable', 
                'array' 
            ], 
 
            'grade.*' => [ 
                'nullable', 
                'numeric', 
                'min:0', 
                'max:100' 
            ], 
        ]); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GET SUBJECTS AND GRADES 
        |-------------------------------------------------------------------------- 
        */ 
 
        $subjectCodes = $request->input( 
            'subject_code', 
            [] 
        ); 
 
        $grades = $request->input( 
            'grade', 
            [] 
        ); 
 
        $hasAnySubject = false; 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | VALIDATE ALL SUBJECTS AND GRADES BEFORE SAVING 
        |-------------------------------------------------------------------------- 
        */ 
 
        foreach ($subjectCodes as $index => $code) { 
 
            $code = trim( 
                (string) ($code ?? '') 
            ); 
 
            $grade = $grades[$index] ?? null; 
 
 
            /* 
            | Empty row = ignore 
            */ 
            if ( 
                $code === '' && 
                ($grade === null || $grade === '') 
            ) { 
                continue; 
            } 
 
 
            $hasAnySubject = true; 
 
 
            /* 
            | Grade entered but no course code 
            */ 
            if ( 
                $code === '' && 
                $grade !== null && 
                $grade !== '' 
            ) { 
 
                return back() 
                    ->withInput() 
                    ->withErrors([ 
                        "subject_code.$index" => 
                            "Please enter the course code for grade '{$grade}'." 
                    ]); 
            } 
 
 
            /* 
            | Course code entered but no grade 
            */ 
            if ( 
                $code !== '' && 
                ($grade === null || $grade === '') 
            ) { 
 
                return back() 
                    ->withInput() 
                    ->withErrors([ 
                        "grade.$index" => 
                            "Please enter a grade for course code '{$code}'." 
                    ]); 
            } 
 
 
            /* 
            | Check course code against prospectus 
            */ 
            $cleanCode = strtoupper($code); 
 
            $subjectExists = DB::table('prospectus') 
                ->whereRaw( 
                    'UPPER(subject_code) = ?', 
                    [$cleanCode] 
                ) 
                ->exists(); 
 
 
            if (!$subjectExists) { 
 
                return back() 
                    ->withInput() 
                    ->withErrors([ 
                        "subject_code.$index" => 
                            "Course Code '{$code}' does not exist in the Prospectus. Please correct it before saving." 
                    ]); 
            } 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | REQUIRE AT LEAST ONE SUBJECT 
        |-------------------------------------------------------------------------- 
        */ 
 
        if (!$hasAnySubject) { 
 
            return back() 
                ->withInput() 
                ->withErrors([ 
                    'subject_code.0' => 
                        'Please enter at least one course code and grade.' 
                ]); 
        } 
 
 
        DB::beginTransaction(); 
 
 
        try { 
 
            /* 
            |-------------------------------------------------------------------------- 
            | GET SEMESTER 
            |-------------------------------------------------------------------------- 
            */ 
 
            $semester = Semester::findOrFail( 
                $request->semester_id 
            ); 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | SAVE STUDENT 
            |-------------------------------------------------------------------------- 
            */ 
 
            $student = Student::create([ 
 
                'student_no' => 
                    trim($request->student_no), 
 
                'first_name' => 
                    strtoupper( 
                        trim($request->first_name) 
                    ), 
 
                'middle_name' => 
                    $request->middle_name 
                        ? strtoupper( 
                            trim($request->middle_name) 
                        ) 
                        : null, 
 
                'last_name' => 
                    strtoupper( 
                        trim($request->last_name) 
                    ), 
 
                'admission_year' => 
                    $request->admission_year, 
 
                'current_year_level' => 
                    $request->current_year_level, 
            ]); 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | SAVE GRADES 
            |-------------------------------------------------------------------------- 
            */ 
 
            foreach ($subjectCodes as $index => $code) { 
 
                $code = trim( 
                    (string) ($code ?? '') 
                ); 
 
                $grade = $grades[$index] ?? null; 
 
 
                if ( 
                    $code === '' || 
                    $grade === null || 
                    $grade === '' 
                ) { 
                    continue; 
                } 
 
 
                $cleanCode = strtoupper($code); 
 
 
                /* 
                | Find prospectus subject case-insensitively 
                */ 
                $subject = DB::table('prospectus') 
                    ->whereRaw( 
                        'UPPER(subject_code) = ?', 
                        [$cleanCode] 
                    ) 
                    ->first(); 
 
 
                if (!$subject) { 
 
                    DB::rollBack(); 
 
                    return back() 
                        ->withInput() 
                        ->withErrors([ 
                            "subject_code.$index" => 
                                "Course Code '{$code}' does not exist in the Prospectus. Please correct it before saving." 
                        ]); 
                } 

                /*
                 |--------------------------------------------------------------------------
                 | PREREQUISITES DO NOT BLOCK FAILED-SUBJECT ENCODING
                 |--------------------------------------------------------------------------
                 |
                 | Only failed subjects are encoded in the evaluation. The system does not
                 | control or prevent enrollment based on prerequisites. Prerequisite
                 | effects are handled only by the residency projection.
                 |--------------------------------------------------------------------------
                 */
                AcademicGrade::create([ 
 
                    'student_id' => 
                        $student->student_id, 
 
                    'semester_id' => 
                        $semester->semester_id, 
 
                    'subject_id' => 
                        $subject->subject_id, 
 
                    'encoded_by' => 
                        session('user_id'), 
 
                    'grade' => 
                        (float) $grade, 
 
                    'attempt_no' => 
                        1, 
 
                    'date_encoded' => 
                        now(), 
                ]); 
            } 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | EVALUATE STUDENT 
            |-------------------------------------------------------------------------- 
            */ 
 
            $this->evaluateStudent( 
                $student, 
                (int) $semester->semester_id, 
                (int) $request->total_subjects 
            ); 
 
 
            DB::commit(); 
 
 
            return redirect() 
                ->route('students.index') 
                ->with( 
                    'success', 
                    'Student added and evaluated successfully.' 
                ); 
 
 
        } catch (\Throwable $e) { 
 
            DB::rollBack(); 
 
            return back() 
                ->withInput() 
                ->with( 
                    'error', 
                    'Unable to save student: ' . 
                    $e->getMessage() 
                ); 
        } 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | SHOW STUDENT 
    |-------------------------------------------------------------------------- 
    */ 
    public function show(int $id) 
    { 
        $student = Student::with([ 
            'academicGrades.subject', 
            'academicGrades.semester', 
            'statusHistory.semester' 
        ])->findOrFail($id); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GET LATEST STATUS 
        |-------------------------------------------------------------------------- 
        | 
        | semester_id is used instead of created_at because evaluations 
        | should be displayed according to academic semester order. 
        | 
        */ 
 
        $latestStatus = $this->getLatestAcademicEvaluation($student);

        /*
        |--------------------------------------------------------------------------
        | GET MOST RECENTLY SUBMITTED EVALUATION
        |--------------------------------------------------------------------------
        | Keep this separate from $latestStatus. The latest submitted evaluation
        | may belong to a semester whose ID is not the largest semester ID.
        */
        $recentEvaluationStatus = StudentStatusHistory::where(
            'student_id',
            $student->student_id
        )
            ->orderByDesc('created_at')
            ->orderByDesc('status_id')
            ->first();

        $recentEvaluationSemesterId = $recentEvaluationStatus
            ? (int) $recentEvaluationStatus->semester_id
            : null;

        /*
        | Legacy status compatibility
        |
        | Older evaluations may still store Regular. Display it as
        | Below Treshold without changing historical database records.
        */
        if (
            $latestStatus &&
            in_array(
                $latestStatus->academic_status,
                ['Regular', 'Residency Risk'],
                true
            )
        ) {
            $latestStatus->academic_status = 'Below Treshold';
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GET SEMESTERS 
        |-------------------------------------------------------------------------- 
        */ 
 
        $semesters = Semester::orderBy( 
            'semester_id', 
            'desc' 
        )->get();


        /*
        |--------------------------------------------------------------------------
        | RESIDENCY PROJECTION FOR DISPLAY
        |--------------------------------------------------------------------------
        | Residency Risk is a warning only. It is NOT an academic status.
        |--------------------------------------------------------------------------
        */

        $residencyRisk = false;
        $residencyExceeded = false;
        $projectedResidencyYears = null;
        $residencyDelayedSubjects = [];

        if ($latestStatus && $latestStatus->semester_id) {

            $evaluationSemester = $semesters->firstWhere(
                'semester_id',
                $latestStatus->semester_id
            );

            $evaluationYear = $this->getEvaluationYear(
                $evaluationSemester
            );

            $yearsSpent = max(
                0,
                $evaluationYear - (int) $student->admission_year
            );

            $prerequisiteResidency =
                $this->calculatePrerequisiteResidencyImpact(
                    $student,
                    (int) $latestStatus->semester_id,
                    $evaluationYear
                );

            $projectedResidencyYears = max(
                4,
                $yearsSpent,
                (int) $prerequisiteResidency['projected_years']
            );

            $residencyExceeded = $yearsSpent > 6;
            $residencyRisk = $projectedResidencyYears > 6;

            $residencyDelayedSubjects =
                $prerequisiteResidency['delayed_subjects'] ?? [];
        }
 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | ALL FAILED SUBJECTS 
        |-------------------------------------------------------------------------- 
        */ 
 
        $failedSubjects = AcademicGrade::with([ 
            'subject', 
            'semester' 
        ]) 
            ->where( 
                'student_id', 
                $student->student_id 
            ) 
            ->where( 
                'grade', 
                '<=', 
                74 
            ) 
            ->orderByDesc('semester_id') 
            ->orderBy('subject_id') 
            ->get();

        /*
        |--------------------------------------------------------------------------
        | FAILED-SUBJECT HISTORY
        |--------------------------------------------------------------------------
        | Failed subjects from the most recently submitted evaluation stay in
        | Recently Evaluated Failed Subjects. Failed records from other
        | semesters are shown in history. Do not assume semester_id values
        | are chronological.
        |
        | A failed attempt is marked Resolved when a later passing attempt
        | exists for the same student and subject.
        */
        $failedSubjectHistory = $failedSubjects
            ->filter(function ($failedGrade) use (
                $recentEvaluationSemesterId
            ) {
                if ($recentEvaluationSemesterId === null) {
                    return true;
                }

                return (int) $failedGrade->semester_id
                    !== $recentEvaluationSemesterId;
            })
            ->map(function ($failedGrade) use ($student) {
                $failedGrade->backlog_resolution = AcademicGrade::where(
                    'student_id',
                    $student->student_id
                )
                    ->where('subject_id', $failedGrade->subject_id)
                    ->where('attempt_no', '>', $failedGrade->attempt_no)
                    ->where('grade', '>=', 75)
                    ->exists() ? 'Resolved' : 'Unresolved';

                return $failedGrade;
            })
            ->sortByDesc(function ($grade) {
                $semester = $grade->semester;
                $schoolYear = (string) optional($semester)->school_year;
                $year = 0;

                if (preg_match('/(?:19|20)\d{2}\s*[-\/]\s*((?:19|20)\d{2})/', $schoolYear, $matches)) {
                    $year = (int) $matches[1];
                } elseif (preg_match('/((?:19|20)\d{2})/', $schoolYear, $matches)) {
                    $year = (int) $matches[1];
                }

                $semesterName = strtolower((string) optional($semester)->semester_name);
                $semesterNumber = (
                    str_contains($semesterName, '2') ||
                    str_contains($semesterName, 'second')
                ) ? 2 : 1;

                return ($year * 10) + $semesterNumber;
            })
            ->values();

        /* 
        |-------------------------------------------------------------------------- 
        | ACTIVE / RESOLVED BACKLOGS 
        |-------------------------------------------------------------------------- 
        | 
        | Backlog means a previously failed subject that remains unresolved. 
        | A currently failed subject is not treated as a backlog for the 
        | current evaluation. 
        | 
        */ 
 
        $activeBacklogs = []; 
 
        $resolvedBacklogs = []; 
 
 
        $subjects = AcademicGrade::with([ 
            'subject', 
            'semester' 
        ]) 
            ->where( 
                'student_id', 
                $student->student_id 
            ) 
            ->orderBy('subject_id') 
            ->orderBy('attempt_no') 
            ->get() 
            ->groupBy('subject_id'); 
 
 
        foreach ($subjects as $subjectGrades) { 
 
            /* 
            | Find the first failed attempt 
            */ 
            $firstFail = $subjectGrades 
                ->where('grade', '<=', 74) 
                ->sortBy('attempt_no') 
                ->first(); 
 
 
            if (!$firstFail) { 
                continue; 
            } 
 
 
            /* 
            | Get latest attempt 
            */ 
            $latestAttempt = $subjectGrades 
                ->sortByDesc('attempt_no') 
                ->first(); 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | RESOLVED BACKLOG 
            |-------------------------------------------------------------------------- 
            | 
            | The subject was failed before but eventually passed. 
            | 
            */ 
 
            if ((float) $latestAttempt->grade >= 75) { 
 
                $resolvedBacklogs[] = [ 
 
                    'subject_code' => 
                        optional( 
                            $firstFail->subject 
                        )->subject_code, 
 
                    'subject_title' => 
                        optional( 
                            $firstFail->subject 
                        )->subject_title, 
 
                    'failed_grade' => 
                        $firstFail->grade, 
 
                    'passed_grade' => 
                        $latestAttempt->grade, 
 
                    'attempt_no' => 
                        $latestAttempt->attempt_no 
                ]; 
 
                continue; 
            } 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | ACTIVE BACKLOG 
            |-------------------------------------------------------------------------- 
            */ 
 
            $activeBacklogs[] = [ 
 
                'subject_code' => 
                    optional( 
                        $firstFail->subject 
                    )->subject_code, 
 
                'subject_id' => 
                    $firstFail->subject_id, 

                'subject_title' => 
                    optional( 
                        $firstFail->subject 
                    )->subject_title, 
 
                'failed_semester' => 
                    optional( 
                        $firstFail->semester 
                    )->school_year . 
                    ' - ' . 
                    optional( 
                        $firstFail->semester 
                    )->semester_name, 
 
                'attempt_no' => 
                    $latestAttempt->attempt_no, 
 
                'latest_grade' => 
                    $latestAttempt->grade, 
 
                'status' => 
                    $subjectGrades->count() === 1 
                        ? 'Never Retaken' 
                        : 'Unresolved' 
            ]; 
        } 
 
 
        return view( 
            'students.show', 
            compact( 
                'student', 
                'latestStatus',
                'recentEvaluationStatus',
                'recentEvaluationSemesterId', 
                'semesters', 
                'failedSubjects', 
                'failedSubjectHistory',
                'activeBacklogs', 
                'resolvedBacklogs',
                'residencyRisk',
                'residencyExceeded',
                'projectedResidencyYears',
                'residencyDelayedSubjects'
            ) 
        ); 
    } 
 

    
/*
|--------------------------------------------------------------------------
| STUDENT RESIDENCY PREDICTION
|--------------------------------------------------------------------------
*/

public function residencyPrediction(int $id)
{
    if (!session()->has('user_id')) {
        return redirect('/')
            ->with('error', 'Please login first.');
    }

    $student = Student::findOrFail($id);

    /*
    |--------------------------------------------------------------------------
    | GET THE EXISTING RESIDENCY PROJECTION
    |--------------------------------------------------------------------------
    | Reuse the existing calculation to keep the prediction consistent
    | with the Student page and dashboard.
    */

    $projection = $this->getResidencyProjection($student);

    $residencyRisk = $projection['residency_risk'] ?? false;

    $residencyExceeded = $projection['residency_exceeded'] ?? false;

    $projectedResidencyYears =
        $projection['projected_residency_years'] ?? null;

    $residencyDelayedSubjects =
        $projection['delayed_subjects'] ?? [];

    /*
    |--------------------------------------------------------------------------
    | GET THE LATEST EVALUATION
    |--------------------------------------------------------------------------
    */

    $latestStatus = $this->getLatestAcademicEvaluation($student);

    $evaluationSemester = $latestStatus && $latestStatus->semester_id
        ? Semester::find($latestStatus->semester_id)
        : null;

    $yearsSpent = null;

    if ($evaluationSemester) {
        $evaluationYear = $this->getEvaluationYear(
            $evaluationSemester
        );

        $yearsSpent = max(
            0,
            $evaluationYear - (int) $student->admission_year
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXPLAIN THE PREDICTION
    |--------------------------------------------------------------------------
    */

    $residencyReasons = [];

    if (!$latestStatus || !$evaluationSemester) {
        $residencyReasons[] =
            'No evaluated semester is available to calculate the student\'s residency projection.';
    } else {
        if ($residencyExceeded) {
            $residencyReasons[] =
                'The student has already spent more than six school years based on the latest evaluated school year and admission year.';
        }

        if (!empty($residencyDelayedSubjects)) {
            $residencyReasons[] =
                'Unresolved failed subjects are delaying prerequisite-dependent subjects in the curriculum. These delays contribute to the projected residency.';
        }

        if (
            !$residencyRisk &&
            !$residencyExceeded
        ) {
            $residencyReasons[] =
                'Based on the latest evaluation and the existing prerequisite-based calculation, the student is not currently predicted to exceed the six-year residency limit.';
        }

        if (
            $residencyRisk &&
            !$residencyExceeded &&
            empty($residencyDelayedSubjects)
        ) {
            $residencyReasons[] =
                'The existing residency calculation projects more than six school years. Review the admission year, latest evaluation, and curriculum information for the contributing factors.';
        }
    }

    return view(
        'students.residency-prediction',
        compact(
            'student',
            'latestStatus',
            'evaluationSemester',
            'yearsSpent',
            'residencyRisk',
            'residencyExceeded',
            'projectedResidencyYears',
            'residencyDelayedSubjects',
            'residencyReasons'
        )
    );
}

 
    /* 
    |-------------------------------------------------------------------------- 
    | EDIT 
    |-------------------------------------------------------------------------- 
    */ 
    public function edit(string $id) 
    { 
        if (!session()->has('user_id')) { 
            return redirect('/') 
                ->with('error', 'Please login first.'); 
        } 
 
 
        $student = Student::with([ 
            'academicGrades.subject', 
            'academicGrades.semester' 
        ])->findOrFail($id); 
 
 
        $semesters = Semester::orderBy( 
            'semester_id', 
            'desc' 
        )->get(); 
 
 
        /* 
        | Get latest semester in which grades were recorded 
        */ 
 
        $currentSemesterId = AcademicGrade::where( 
            'student_id', 
            $student->student_id 
        ) 
            ->orderByDesc('semester_id') 
            ->value('semester_id'); 
 
 
        return view( 
            'students.edit', 
            compact( 
                'student', 
                'semesters', 
                'currentSemesterId' 
            ) 
        ); 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | UPDATE STUDENT 
    |-------------------------------------------------------------------------- 
    */ 
    public function update( 
        Request $request, 
        string $id 
    ) { 
        if (!$request->session()->has('user_id')) { 
            return redirect('/') 
                ->with('error', 'Please login first.'); 
        } 
 
 
        $student = Student::findOrFail($id); 
 
 
        $request->validate([ 
 
            'student_no' => [ 
                'required', 
                'string', 
                'max:50', 
                Rule::unique( 
                    'students', 
                    'student_no' 
                )->ignore( 
                    $student->student_id, 
                    'student_id' 
                ) 
            ], 
 
            'first_name' => 
                'required|string|max:100', 
 
            'middle_name' => 
                'nullable|string|max:100', 
 
            'last_name' => 
                'required|string|max:100', 
 
            'admission_year' => 
                'required|integer|min:1900|max:2100', 
 
            'current_year_level' => 
                'required|string|max:50', 
 
            'semester_id' => 
                'required|exists:semesters,semester_id', 
 
            'total_subjects' => 
                'required|integer|min:1|max:100', 
 
            'subject_code' => 
                'nullable|array', 
 
            'subject_code.*' => 
                'nullable|string|max:50', 
 
            'grade' => 
                'nullable|array', 
 
            'grade.*' => 
                'nullable|numeric|min:0|max:100', 
 
            'grade_id' => 
                'nullable|array', 
 
            'grade_id.*' => 
                'nullable|integer', 
 
            'deleted_grade_ids' => 
                'nullable|array', 
 
            'deleted_grade_ids.*' => 
                'nullable|integer', 
        ]); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GET INPUT ARRAYS 
        |-------------------------------------------------------------------------- 
        */ 
 
        $gradeIds = $request->input( 
            'grade_id', 
            [] 
        ); 
 
        $subjectCodes = $request->input( 
            'subject_code', 
            [] 
        ); 
 
        $grades = $request->input( 
            'grade', 
            [] 
        ); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | VALIDATE SUBJECTS BEFORE CHANGING STUDENT 
        |-------------------------------------------------------------------------- 
        */ 
 
        foreach ($subjectCodes as $index => $subjectCode) { 
 
            $subjectCode = trim( 
                (string) ( 
                    $subjectCode ?? '' 
                ) 
            ); 
 
            $grade = $grades[$index] ?? null; 
 
 
            /* 
            | Empty row 
            */ 
 
            if ( 
                $subjectCode === '' && 
                ($grade === null || $grade === '') 
            ) { 
                continue; 
            } 
 
 
            /* 
            | Grade without course code 
            */ 
 
            if ( 
                $subjectCode === '' && 
                $grade !== null && 
                $grade !== '' 
            ) { 
 
                return back() 
                    ->withInput() 
                    ->withErrors([ 
                        "subject_code.$index" => 
                            "Please enter the course code for grade '{$grade}'." 
                    ]); 
            } 
 
 
            /* 
            | Course code without grade 
            */ 
 
            if ( 
                $subjectCode !== '' && 
                ($grade === null || $grade === '') 
            ) { 
 
                return back() 
                    ->withInput() 
                    ->withErrors([ 
                        "grade.$index" => 
                            "Please enter a grade for course code '{$subjectCode}'." 
                    ]); 
            } 
 
 
            /* 
            | Validate course code against prospectus 
            */ 
 
            $cleanCode = strtoupper( 
                $subjectCode 
            ); 
 
            $subjectExists = DB::table('prospectus') 
                ->whereRaw( 
                    'UPPER(subject_code) = ?', 
                    [$cleanCode] 
                ) 
                ->exists(); 
 
 
            if (!$subjectExists) { 
 
                return back() 
                    ->withInput() 
                    ->withErrors([ 
                        "subject_code.$index" => 
                            "Course Code '{$subjectCode}' does not exist in the Prospectus. Please correct it before saving." 
                    ]); 
            } 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | UPDATE STUDENT INFORMATION FIRST 
        |-------------------------------------------------------------------------- 
        | 
        | Student information is saved separately from academic-grade 
        | processing. This prevents a problem in grade/evaluation processing 
        | from rolling back valid changes to the student's personal 
        | information. 
        |-------------------------------------------------------------------------- 
        */ 
 
        try { 
 
            $student->student_no = 
                trim($request->student_no); 
 
            $student->first_name = 
                strtoupper( 
                    trim($request->first_name) 
                ); 
 
            $student->middle_name = 
                $request->middle_name 
                    ? strtoupper( 
                        trim($request->middle_name) 
                    ) 
                    : null; 
 
            $student->last_name = 
                strtoupper( 
                    trim($request->last_name) 
                ); 
 
            $student->admission_year = 
                $request->admission_year; 
 
            $student->current_year_level = 
                $request->current_year_level; 
 
            $student->save(); 
 
            $student->refresh(); 
 
        } catch (\Throwable $e) { 
 
            return back() 
                ->withInput() 
                ->with( 
                    'error', 
                    'Unable to update student information: ' . 
                    $e->getMessage() 
                ); 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | PROCESS ACADEMIC CHANGES 
        |-------------------------------------------------------------------------- 
        */ 
 
        DB::beginTransaction(); 
 
 
        try { 
 
            /* 
            |-------------------------------------------------------------------------- 
            | PROCESS EXISTING AND NEW GRADES 
            |-------------------------------------------------------------------------- 
            | 
            | We use the subject-code array as the main loop so newly added 
            | rows without a grade_id are also processed. 
            | 
            */ 
 
            foreach ($subjectCodes as $index => $subjectCode) { 
 
                $subjectCode = trim( 
                    (string) ( 
                        $subjectCode ?? '' 
                    ) 
                ); 
 
                $grade = $grades[$index] ?? null; 
                $gradeId = $gradeIds[$index] ?? null; 
 
 
                /* 
                | Ignore empty rows 
                */ 
 
                if ( 
                    $subjectCode === '' || 
                    $grade === null || 
                    $grade === '' 
                ) { 
                    continue; 
                } 
 
 
                $cleanCode = strtoupper( 
                    $subjectCode 
                ); 
 
 
                /* 
                | Find subject case-insensitively 
                */ 
 
                $subject = DB::table('prospectus') 
                    ->whereRaw( 
                        'UPPER(subject_code) = ?', 
                        [$cleanCode] 
                    ) 
                    ->first(); 
 
 
                if (!$subject) { 
 
                    DB::rollBack(); 
 
                    return redirect() 
                        ->route( 
                            'students.show', 
                            $student->student_id 
                        ) 
                        ->with( 
                            'error', 
                            "Course Code '{$subjectCode}' does not exist in the Prospectus. Student information was updated, but academic changes were not saved." 
                        ); 
                } 
 
 
                /* 
                |-------------------------------------------------------------------------- 
                | NEW GRADE 
                |-------------------------------------------------------------------------- 
                */ 
 
                if (empty($gradeId)) { 
 
                    /*
                     |--------------------------------------------------------------------------
                     | PREREQUISITES DO NOT BLOCK FAILED-SUBJECT ENCODING
                     |--------------------------------------------------------------------------
                     |
                     | Only failed subjects are encoded in the evaluation. The system does not
                     | control or prevent enrollment based on prerequisites. Prerequisite
                     | effects are handled only by the residency projection.
                     |--------------------------------------------------------------------------
                     */

                    $lastAttempt = AcademicGrade::where( 
                        'student_id', 
                        $student->student_id 
                    ) 
                        ->where( 
                            'subject_id', 
                            $subject->subject_id 
                        ) 
                        ->max('attempt_no'); 
 
 
                    $attemptNo = 
                        ((int) $lastAttempt) + 1; 
 
 
                    AcademicGrade::create([ 
 
                        'student_id' => 
                            $student->student_id, 
 
                        'semester_id' => 
                            $request->semester_id, 
 
                        'subject_id' => 
                            $subject->subject_id, 
 
                        'encoded_by' => 
                            session('user_id'), 
 
                        'grade' => 
                            (float) $grade, 
 
                        'attempt_no' => 
                            $attemptNo, 
 
                        'date_encoded' => 
                            now(), 
                    ]); 
 
 
                    continue; 
                } 
 
 
                /* 
                |-------------------------------------------------------------------------- 
                | UPDATE EXISTING GRADE 
                |-------------------------------------------------------------------------- 
                */ 
 
                $gradeRecord = AcademicGrade::where( 
                    'grade_id', 
                    $gradeId 
                ) 
                    ->where( 
                        'student_id', 
                        $student->student_id 
                    ) 
                    ->first(); 
 
 
                if ($gradeRecord) { 
 
                    $gradeRecord->update([ 
 
                        'subject_id' => 
                            $subject->subject_id, 
 
                        'semester_id' => 
                            $gradeRecord->semester_id, 
 
                        'grade' => 
                            (float) $grade 
                    ]); 
                } 
            } 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | DELETE REMOVED GRADES 
            |-------------------------------------------------------------------------- 
            */ 
 
            $deletedGradeIds = $request->input( 
                'deleted_grade_ids', 
                [] 
            ); 
 
 
            if ( 
                is_array($deletedGradeIds) && 
                count($deletedGradeIds) > 0 
            ) { 
 
                AcademicGrade::where( 
                    'student_id', 
                    $student->student_id 
                ) 
                    ->whereIn( 
                        'grade_id', 
                        $deletedGradeIds 
                    ) 
                    ->delete(); 
            } 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | RE-EVALUATE SELECTED SEMESTER 
            |-------------------------------------------------------------------------- 
            */ 
 
            $this->evaluateStudent( 
                $student, 
                (int) $request->semester_id, 
                (int) $request->total_subjects 
            ); 
 
 
            DB::commit(); 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | SUCCESS - RETURN DIRECTLY TO STUDENT PAGE 
            |-------------------------------------------------------------------------- 
            */ 
 
            return redirect() 
                ->route( 
                    'students.show', 
                    $student->student_id 
                ) 
                ->with( 
                    'success', 
                    'Student updated and evaluated successfully.' 
                ); 
 
 
        } catch (\Throwable $e) { 
 
            DB::rollBack(); 
 
            return redirect() 
                ->route( 
                    'students.show', 
                    $student->student_id 
                ) 
                ->with( 
                    'error', 
                    'Student information was updated, but the academic changes were not saved: ' . 
                    $e->getMessage() 
                ); 
        } 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | ADD GRADES / ADD EVALUATION 
    |-------------------------------------------------------------------------- 
    */ 
    public function addGrade( 
        Request $request, 
        string $id 
    ) { 
        if (!$request->session()->has('user_id')) { 
            return redirect('/') 
                ->with( 
                    'error', 
                    'Your session has expired. Please login again.' 
                ); 
        } 
 
 
        $request->validate([ 
 
            'semester_id' => 
                'required|exists:semesters,semester_id', 
 
            'total_subjects' => 
                'required|integer|min:1|max:100', 
 
            'year_level' => 
                'nullable|string|max:50', 
        ]); 
 
 
        $student = Student::findOrFail($id); 
 
 
        $semester = Semester::findOrFail( 
            $request->semester_id 
        ); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | COLLECT DYNAMIC SUBJECT ROWS 
        |-------------------------------------------------------------------------- 
        | 
        | Instead of limiting the form to exactly 9 rows, this detects 
        | all subject_code_X fields submitted by the modal. 
        | 
        */ 
 
        $subjectRows = []; 
 
        foreach ($request->all() as $key => $value) { 
 
            if ( 
                preg_match( 
                    '/^subject_code_(\d+)$/', 
                    $key, 
                    $matches 
                ) 
            ) { 
 
                $number = (int) $matches[1]; 
 
                $subjectRows[] = $number; 
            } 
        } 
 
 
        sort($subjectRows); 
 
 
        DB::beginTransaction(); 
 
 
        try { 
 
            /* 
            |-------------------------------------------------------------------------- 
            | UPDATE YEAR LEVEL 
            |-------------------------------------------------------------------------- 
            */ 
 
            if ($request->filled('year_level')) { 
 
                $student->update([ 
 
                    'current_year_level' => 
                        $request->year_level 
                ]); 
            } 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | FAILED SUBJECTS ARE OPTIONAL 
            |-------------------------------------------------------------------------- 
            | 
            | The admin enters only the failed subjects for this semester. 
            | A student may have zero failed subjects, in which case the 
            | evaluation is still recorded as 0% failure and Below Treshold. 
            | 
            */ 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | SAVE SUBJECTS 
            |-------------------------------------------------------------------------- 
            */ 
 
            foreach ($subjectRows as $i) { 
 
                $subjectCode = trim( 
                    (string) $request->input( 
                        "subject_code_$i", 
                        '' 
                    ) 
                ); 
 
                $grade = $request->input( 
                    "grade_$i" 
                ); 
 
 
                /* 
                | Empty row = ignore 
                */ 
 
                if ( 
                    $subjectCode === '' && 
                    ($grade === null || $grade === '') 
                ) { 
                    continue; 
                } 
 
 
                /* 
                |-------------------------------------------------------------------------- 
                | COURSE CODE REQUIRED 
                |-------------------------------------------------------------------------- 
                */ 
 
                if ($subjectCode === '') { 
 
                    DB::rollBack(); 
 
                    return back() 
                        ->withInput() 
                        ->withErrors([ 
 
                            "subject_code_$i" => 
                                'Please enter a course code.' 
                        ]); 
                } 
 
 
                /* 
                |-------------------------------------------------------------------------- 
                | GRADE REQUIRED 
                |-------------------------------------------------------------------------- 
                */ 
 
                if ($grade === null || $grade === '') { 
 
                    DB::rollBack(); 
 
                    return back() 
                        ->withInput() 
                        ->withErrors([ 
 
                            "grade_$i" => 
                                "Please enter a grade for course code '{$subjectCode}'." 
                        ]); 
                } 
 
 
                /* 
                |-------------------------------------------------------------------------- 
                | VALIDATE GRADE 
                |-------------------------------------------------------------------------- 
                */ 
 
                if ( 
                    !is_numeric($grade) || 
                    (float) $grade < 0 || 
                    (float) $grade > 100 
                ) { 
 
                    DB::rollBack(); 
 
                    return back() 
                        ->withInput() 
                        ->withErrors([ 
 
                            "grade_$i" => 
                                'Grade must be between 0 and 100.' 
                        ]); 
                } 
 
 
                /* 
                |-------------------------------------------------------------------------- 
                | FIND SUBJECT IN PROSPECTUS 
                |-------------------------------------------------------------------------- 
                */ 
 
                $cleanCode = strtoupper( 
                    $subjectCode 
                ); 
 
 
                $subject = DB::table('prospectus') 
                    ->whereRaw( 
                        'UPPER(subject_code) = ?', 
                        [$cleanCode] 
                    ) 
                    ->first(); 
 
 
                if (!$subject) { 
 
                    DB::rollBack(); 
 
                    return back() 
                        ->withInput() 
                        ->withErrors([ 
 
                            "subject_code_$i" => 
                                "Course Code '{$subjectCode}' does not exist in the Prospectus. Please correct it before saving." 
                        ]); 
                } 
 
 
                /* 
                |-------------------------------------------------------------------------- 
                | FAILED SUBJECT ONLY 
                |-------------------------------------------------------------------------- 
                | 
                | This modal is specifically for encoding failed subjects. 
                | Grades of 75 and above must not be entered here. 
                | 
                */ 
 
                if ((float) $grade > 74) { 
 
                    DB::rollBack(); 
 
                    return back() 
                        ->withInput() 
                        ->withErrors([ 
                            "grade_$i" => 
                                'Only failed grades of 74 or below may be encoded in the evaluation form.' 
                        ]); 
                } 

                /*
                 |--------------------------------------------------------------------------
                 | PREREQUISITES DO NOT BLOCK FAILED-SUBJECT ENCODING
                 |--------------------------------------------------------------------------
                 |
                 | Only failed subjects are encoded in the evaluation. The system does not
                 | control or prevent enrollment based on prerequisites. Prerequisite
                 | effects are handled only by the residency projection.
                 |--------------------------------------------------------------------------
                 */
                /* 
                |-------------------------------------------------------------------------- 
                | GET NEXT ATTEMPT NUMBER 
                |-------------------------------------------------------------------------- 
                */ 
 
                $lastAttempt = AcademicGrade::where( 
                    'student_id', 
                    $student->student_id 
                ) 
                    ->where( 
                        'subject_id', 
                        $subject->subject_id 
                    ) 
                    ->max('attempt_no'); 
 
 
                $attemptNo = 
                    ((int) $lastAttempt) + 1; 
 
 
                /* 
                |-------------------------------------------------------------------------- 
                | SAVE GRADE 
                |-------------------------------------------------------------------------- 
                */ 
 
                AcademicGrade::create([ 
 
                    'student_id' => 
                        $student->student_id, 
 
                    'semester_id' => 
                        $semester->semester_id, 
 
                    'subject_id' => 
                        $subject->subject_id, 
 
                    'encoded_by' => 
                        session('user_id'), 
 
                    'grade' => 
                        (float) $grade, 
 
                    'attempt_no' => 
                        $attemptNo, 
 
                    'date_encoded' => 
                        now(), 
                ]); 
            } 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | EVALUATE STUDENT 
            |-------------------------------------------------------------------------- 
            */ 
 
            $this->evaluateStudent( 
                $student, 
                (int) $semester->semester_id, 
                (int) $request->total_subjects 
            ); 
 
 
            DB::commit(); 
 
 
            return redirect() 
                ->route( 
                    'students.show', 
                    $student->student_id 
                ) 
                ->with( 
                    'success', 
                    'Grades saved and academic status updated successfully.' 
                ); 
 
 
        } catch (\Throwable $e) { 
 
            DB::rollBack(); 
 
            return back() 
                ->withInput() 
                ->with( 
                    'error', 
                    'Unable to save grades: ' . 
                    $e->getMessage() 
                ); 
        } 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | EVALUATE STUDENT 
    |-------------------------------------------------------------------------- 
    | 
    | RULES 
    | 
    | 75 and above = PASS 
    | 74 and below = FAIL 
    | 
    | 100% failed in semester = Shifting Out 
    | Same subject failed 3 times = Shifting Out 
    | Beyond 6-year residency = Shifting Out 
    | 
    | 50% - 99.99% = Probation 
    | 25% - 49.99% = Academic Warning 
    | Below 25% = Below Treshold 
    | 
    | PRIORITY ALERT 
    | 
    | Priority Alert is ONLY TRUE when: 
    | 
    | 1. There is a previous evaluation; AND 
    | 2. Current failure percentage is equal to or higher than 
    |    the previous failure percentage. 
    | 
    | Lower failure percentage = improvement = NO Priority Alert. 
    | 
    |-------------------------------------------------------------------------- 
    */ 
    private function evaluateStudent( 
        Student $student, 
        int $semester_id, 
        int $totalSubjects 
    ) { 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GET SEMESTER 
        |-------------------------------------------------------------------------- 
        */ 
 
        $semester = Semester::find( 
            $semester_id 
        ); 
 
 
        if (!$semester) { 
            return; 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GET CURRENT SEMESTER GRADES ONLY 
        |-------------------------------------------------------------------------- 
        */ 
 
        $currentGrades = AcademicGrade::where( 
            'student_id', 
            $student->student_id 
        ) 
            ->where( 
                'semester_id', 
                $semester_id 
            ) 
            ->orderBy('grade_id') 
            ->get(); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GROUP GRADES BY SUBJECT 
        |-------------------------------------------------------------------------- 
        | 
        | The Add Academic Evaluation form records failed subjects only. 
        | Therefore an empty current-semester grade set is valid and means 
        | the student has zero failed subjects for this evaluation. 
        | 
        |-------------------------------------------------------------------------- 
        | 
        | If the same subject has multiple grade records in the same 
        | semester, only the latest grade record is evaluated. 
        | 
        */ 
 
        $subjectsForEvaluation = $currentGrades 
            ->groupBy('subject_id') 
            ->map(function ($subjectGrades) { 
 
                return $subjectGrades 
                    ->sortByDesc('grade_id') 
                    ->first(); 
 
            }) 
            ->values(); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | ADMIN-ENTERED TOTAL SUBJECTS 
        |-------------------------------------------------------------------------- 
        | 
        | The denominator is NOT the number of encoded grade records. 
        | The admin provides the student's total number of subjects for 
        | this semester. Only the failed subjects are encoded below. 
        | 
        */ 
 
        $totalSubjects = (int) $totalSubjects; 
 
 
        if ($totalSubjects <= 0) { 
            return; 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | COUNT FAILED SUBJECTS 
        |-------------------------------------------------------------------------- 
        */ 
 
        $failedSubjectsCount = 
            $subjectsForEvaluation 
                ->filter(function ($gradeRecord) { 
 
                    return (float) $gradeRecord->grade <= 74; 
 
                }) 
                ->count(); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | SAFETY CHECK 
        |-------------------------------------------------------------------------- 
        | 
        | The number of encoded failed subjects cannot be greater than 
        | the total number of subjects entered by the admin. 
        | 
        */ 
 
        if ($failedSubjectsCount > $totalSubjects) { 
            return; 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | CALCULATE FAILURE PERCENTAGE 
        |-------------------------------------------------------------------------- 
        */ 
 
        $failurePercentage = 
            round( 
                ( 
                    $failedSubjectsCount / 
                    $totalSubjects 
                ) * 100, 
                2 
            ); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | CHECK 100% FAILURE 
        |-------------------------------------------------------------------------- 
        | 
        | Example: 
        | 
        | 5 subjects 
        | 5 failed 
        | 
        | 5 / 5 * 100 = 100% 
        | 
        | Result = Shifting Out 
        | 
        */ 
 
        $allSubjectsFailed = 
            $totalSubjects > 0 && 
            $failedSubjectsCount === $totalSubjects; 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | BASIC ACADEMIC STATUS 
        |-------------------------------------------------------------------------- 
        */ 
 
        if ($failurePercentage >= 50) { 
 
            $status = 'Probation'; 
 
        } elseif ($failurePercentage >= 25) { 
 
            $status = 'Academic Warning'; 
 
        } else { 
 
            $status = 'Below Treshold'; 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | CHECK THIRD FAILED ATTEMPT 
        |-------------------------------------------------------------------------- 
        | 
        | If any subject has attempt number 3 or higher and the grade 
        | is failing, the student is Shifting Out. 
        | 
        */ 
 
        $thirdAttemptFailure = AcademicGrade::where( 
            'student_id', 
            $student->student_id 
        ) 
            ->where( 
                'attempt_no', 
                '>=', 
                3 
            ) 
            ->where( 
                'grade', 
                '<=', 
                74 
            ) 
            ->exists(); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | RESIDENCY CALCULATION 
        |-------------------------------------------------------------------------- 
        | 
        | Normal residency uses the six-year limit. In addition, an unresolved 
        | failed prerequisite delays dependent subjects because the failed 
        | prerequisite can only be retaken in the same semester of the 
        | following school year. The helper below follows those prerequisite 
        | chains and calculates a projected residency. 
        | 
        */ 
 
        $evaluationYear = $this->getEvaluationYear(
            $semester
        ); 
 
        $admissionYear = 
            (int) $student->admission_year; 
 
        $yearsSpent = 
            $evaluationYear - 
            $admissionYear; 
 
        if ($yearsSpent < 0) { 
            $yearsSpent = 0; 
        } 
 
        $prerequisiteResidency = 
            $this->calculatePrerequisiteResidencyImpact( 
                $student, 
                $semester_id, 
                $evaluationYear 
            ); 
 
        $projectedResidencyYears = 
            max( 
                4,
                $yearsSpent, 
                (int) $prerequisiteResidency['projected_years'] 
            ); 
 
        $residencyExceeded = 
            $yearsSpent > 6; 
 
        $residencyRisk =
            $projectedResidencyYears > 6; 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | SHIFTING OUT REASONS 
        |-------------------------------------------------------------------------- 
        */ 
 
        $shiftingReasons = []; 
 
 
        /* 
        | RULE 1: FAILED ALL SUBJECTS 
        */ 
 
        if ($allSubjectsFailed) { 
 
            $shiftingReasons[] = 
                'failed all subjects in the semester (100% failure)'; 
        } 
 
 
        /* 
        | RULE 2: FAILED SAME SUBJECT THREE TIMES 
        */ 
 
        if ($thirdAttemptFailure) { 
 
            $shiftingReasons[] = 
                'failed the same subject three times'; 
        } 
 
 
        /* 
        | RULE 3: EXCEEDED SIX-YEAR RESIDENCY 
        */ 
 
        if ($residencyExceeded) { 
 
            $shiftingReasons[] = 
                'exceeded the six-year residency limit'; 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | SHIFTING OUT OVERRIDES WARNING / PROBATION 
        |-------------------------------------------------------------------------- 
        */ 
 
        if (!empty($shiftingReasons)) { 

            $status = 
                'Shifting Out'; 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GET PREVIOUS EVALUATION 
        |-------------------------------------------------------------------------- 
        | 
        | IMPORTANT: 
        | 
        | Only look at a semester BEFORE the current semester. 
        | 
        | This prevents the current evaluation from being accidentally 
        | used as the previous evaluation. 
        | 
        */ 
 
        $previousEvaluation = 
            StudentStatusHistory::where( 
                'student_id', 
                $student->student_id 
            ) 
                ->where( 
                    'semester_id', 
                    '<', 
                    $semester_id 
                ) 
                ->orderByDesc('semester_id') 
                ->orderByDesc('status_id') 
                ->first(); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | PRIORITY ALERT 
        |-------------------------------------------------------------------------- 
        | 
        | DEFAULT = FALSE 
        | 
        | Priority Alert does NOT depend on: 
        | 
        | - Probation 
        | - Shifting Out 
        | - Academic Warning 
        | 
        | It depends ONLY on performance comparison. 
        | 
        */ 
 
        $priorityAlert = false; 
 
        $performanceDidNotImprove = false; 
 
        $previousPercentage = null; 
 
 
        if ($previousEvaluation) { 
 
            $previousPercentage = 
                (float) 
                $previousEvaluation->failure_percentage; 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | NO IMPROVEMENT OR WORSE 
            |-------------------------------------------------------------------------- 
            | 
            | Current < Previous 
            | = Improvement 
            | = NO Priority Alert 
            | 
            | Current = Previous 
            | = No improvement 
            | = Priority Alert 
            | 
            | Current > Previous 
            | = Worse 
            | = Priority Alert 
            | 
            */ 
 
            if ( 
                $failurePercentage >= 
                $previousPercentage 
            ) { 
 
                $performanceDidNotImprove = true; 
 
                $priorityAlert = true; 
            } 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | REMARKS 
        |-------------------------------------------------------------------------- 
        */ 
 
        $remarks = 
            'Evaluation recorded for ' . 
            $semester->school_year . 
            ' - ' . 
            $semester->semester_name . 
            '.'; 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | SHIFTING OUT REMARK 
        |-------------------------------------------------------------------------- 
        */ 
 
        if ( 
            $status === 'Shifting Out' && 
            !empty($shiftingReasons) 
        ) { 
 
            $remarks = 
                'Student is for Shifting Out because ' . 
                implode( 
                    ' and ', 
                    $shiftingReasons 
                ) . 
                '.'; 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | PREREQUISITE RESIDENCY RISK REMARK 
        |-------------------------------------------------------------------------- 
        */ 
 
        if (
            $residencyRisk
        ) {

            $residencyRemark =
                'Residency Warning: Projected residency is ' .
                $projectedResidencyYears .
                ' school years.';

            if (!empty($prerequisiteResidency['delayed_subjects'])) {
                $residencyRemark .=
                    ' Delayed subjects: ' .
                    implode(
                        ', ',
                        $prerequisiteResidency['delayed_subjects']
                    ) .
                    '.';
            }

            $remarks .= ' ' . $residencyRemark;
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | PRIORITY ALERT REMARK 
        |-------------------------------------------------------------------------- 
        | 
        | Priority Alert is added only when performance did not improve 
        | or became worse. 
        | 
        */ 
 
        elseif ( 
            $performanceDidNotImprove && 
            $previousEvaluation 
        ) { 
 
            if ( 
                $failurePercentage > 
                $previousPercentage 
            ) { 
 
                $remarks = 
                    'Priority Alert: Student performance became worse compared with the previous evaluation.'; 
 
            } else { 
 
                $remarks = 
                    'Priority Alert: Student performance did not improve compared with the previous evaluation.'; 
            } 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | FIND EXISTING EVALUATION FOR THIS SEMESTER 
        |-------------------------------------------------------------------------- 
        | 
        | Prevent duplicate evaluation records for the same student and 
        | semester. 
        | 
        */ 
 
        $statusHistory = 
            StudentStatusHistory::where( 
                'student_id', 
                $student->student_id 
            ) 
                ->where( 
                    'semester_id', 
                    $semester_id 
                ) 
                ->orderByDesc('status_id') 
                ->first(); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | CREATE NEW STATUS HISTORY 
        |-------------------------------------------------------------------------- 
        */ 
 
        if (!$statusHistory) { 
 
            $statusHistory = 
                new StudentStatusHistory(); 
 
            $statusHistory->student_id = 
                $student->student_id; 
 
            $statusHistory->semester_id = 
                $semester_id; 
 
            $statusHistory->created_at = 
                now(); 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | SAVE EVALUATION 
        |-------------------------------------------------------------------------- 
        */ 
 
        $statusHistory->academic_status = 
            $status; 
 
        $statusHistory->total_subjects = 
            $totalSubjects; 
 
        $statusHistory->failure_percentage = 
            $failurePercentage; 
 
        $statusHistory->priority_alert = 
            $priorityAlert; 
 
        $statusHistory->remarks = 
            $remarks; 
 
        $statusHistory->save(); 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | CALCULATE PREREQUISITE-BASED RESIDENCY IMPACT 
    |-------------------------------------------------------------------------- 
    | 
    | An unresolved failed subject can be retaken in the same semester of 
    | the following school year. If another prospectus subject requires 
    | that subject as a prerequisite, the dependent subject is delayed. 
    | 
    | The calculation follows prerequisite chains. It does not create a 
    | new table and it does not change the existing grade records. 
    | 
    | Common prerequisite formats supported: 
    |   IT101 
    |   IT101, IT102 
    |   IT101 / IT102 
    |   IT101 or IT102 
    |   IT101 and IT102 
    | 
    |-------------------------------------------------------------------------- 
    */ 
    /**
     * Get the student's current residency projection.
     *
     * Residency Risk is a warning only. It is TRUE only when the
     * projected residency will exceed the six-year limit.
     *
     * This public wrapper allows the Home dashboard to use the exact
     * same residency calculation as the Student list and Student view.
     */
    /**
     * Return the latest evaluation according to the actual school year and
     * semester, not the numeric semester_id. IDs are database identifiers and
     * are not a reliable measure of academic chronology.
     */
    private function getLatestAcademicEvaluation(Student $student): ?StudentStatusHistory
    {
        return StudentStatusHistory::with('semester')
            ->where('student_id', $student->student_id)
            ->whereNotNull('semester_id')
            ->get()
            ->sort(function ($a, $b) {
                $yearA = $this->getEvaluationYear($a->semester);
                $yearB = $this->getEvaluationYear($b->semester);

                if ($yearA !== $yearB) {
                    return $yearA <=> $yearB;
                }

                $semesterA = $this->normalizeSemesterNumber(
                    optional($a->semester)->semester_name
                        ?? optional($a->semester)->name
                        ?? optional($a->semester)->semester
                        ?? null
                ) ?? 0;

                $semesterB = $this->normalizeSemesterNumber(
                    optional($b->semester)->semester_name
                        ?? optional($b->semester)->name
                        ?? optional($b->semester)->semester
                        ?? null
                ) ?? 0;

                if ($semesterA !== $semesterB) {
                    return $semesterA <=> $semesterB;
                }

                return (int) $a->status_id <=> (int) $b->status_id;
            })
            ->last();
    }


    public function getResidencyProjection(Student $student): array
    {
        // Use actual school-year chronology, not the numeric semester_id.
        $latestEvaluation = $this->getLatestAcademicEvaluation($student);

        if (!$latestEvaluation || !$latestEvaluation->semester_id) {
            return [
                'residency_risk' => false,
                'residency_exceeded' => false,
                'projected_residency_years' => null,
                'delayed_subjects' => [],
            ];
        }

        $evaluationSemester =
            $latestEvaluation->semester
            ?? Semester::find($latestEvaluation->semester_id);

        $evaluationYear =
            $this->getEvaluationYear($evaluationSemester);

        $yearsSpent = max(
            0,
            $evaluationYear - (int) $student->admission_year
        );

        $prerequisiteResidency =
            $this->calculatePrerequisiteResidencyImpact(
                $student,
                (int) $latestEvaluation->semester_id,
                $evaluationYear
            );

        $projectedResidencyYears = max(
                4,
                $yearsSpent,
                (int) $prerequisiteResidency['projected_years']
            );

        return [
            'residency_risk' =>
                $projectedResidencyYears > 6,

            'residency_exceeded' =>
                $yearsSpent > 6,

            'projected_residency_years' =>
                $projectedResidencyYears,

            'delayed_subjects' =>
                $prerequisiteResidency['delayed_subjects'] ?? [],
        ];
    }


    private function getEvaluationYear(?Semester $semester): int
    {
        if (!$semester) {
            return now()->year;
        }

        $schoolYear = trim(
            (string) $semester->school_year
        );

        if (
            preg_match(
                '/((?:19|20)\d{2})\s*[-\/]\s*((?:19|20)\d{2})/',
                $schoolYear,
                $matches
            )
        ) {
            return (int) $matches[2];
        }

        if (
            preg_match(
                '/((?:19|20)\d{2})/',
                $schoolYear,
                $matches
            )
        ) {
            return (int) $matches[1];
        }

        return now()->year;
    }


    private function calculatePrerequisiteResidencyImpact(
        Student $student,
        int $semester_id,
        int $evaluationYear
    ): array {

        /*
        |--------------------------------------------------------------------------
        | RESIDENCY PROJECTION RULES
        |--------------------------------------------------------------------------
        |
        | 1. The normal four-year curriculum comes from the Prospectus.
        | 2. A failed subject is retaken in the next school year in the
        |    same semester in which it was failed.
        | 3. If the same subject is failed again, the NEW/latest failure
        |    creates another one-year retake delay.
        | 4. A prerequisite must be completed before its dependent subject
        |    can be completed.
        | 5. If a prerequisite is completed late, all dependent subjects
        |    are moved to their next available offering.
        | 6. The calculation follows prerequisite chains.
        | 7. A subject marked as passed is treated as completed in the
        |    semester of its latest passing attempt, so it can immediately
        |    remove the prerequisite block from future projections.
        |
        | This calculation does not create a new table and does not change
        | existing grade records.
        |
        |--------------------------------------------------------------------------
        */

        $admissionYear = (int) $student->admission_year;

        /*
        |--------------------------------------------------------------------------
        | CURRENT EVALUATION SLOT
        |--------------------------------------------------------------------------
        | Unresolved subjects must be projected forward from the semester
        | currently being evaluated. Do not leave a missed retake or an
        | unrecorded curriculum subject in a semester that has already passed.
        |--------------------------------------------------------------------------
        */

        $evaluationSemester = Semester::find($semester_id);
        $evaluationSlot = $this->getSemesterSlot($evaluationSemester);

        if (
            $evaluationSlot['year'] === null ||
            $evaluationSlot['semester'] === null ||
            $evaluationSlot['index'] === null
        ) {
            $evaluationSlot = [
                'year' => $evaluationYear,
                'semester' => 2,
                'index' => ($evaluationYear * 2) + 1,
            ];
        }

        $result = [
            'projected_years' => 0,
            'delay_years' => 0,
            'delayed_subjects' => [],
        ];

        /*
        |--------------------------------------------------------------------------
        | GET PROSPECTUS
        |--------------------------------------------------------------------------
        */

        $prospectusSubjects = DB::table('prospectus')
            ->select(
                'subject_id',
                'subject_code',
                'subject_title',
                'year_level',
                'semester',
                'prerequisite'
            )
            ->get();

        if ($prospectusSubjects->isEmpty()) {
            $result['projected_years'] = max(
                0,
                $evaluationYear - $admissionYear
            );

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | BUILD SUBJECT LOOKUPS
        |--------------------------------------------------------------------------
        */

        $subjectById = [];
        $subjectByCode = [];

        foreach ($prospectusSubjects as $subject) {

            $subjectById[(int) $subject->subject_id] =
                $subject;

            $code = $this->normalizeCourseCode(
                $subject->subject_code
            );

            if ($code !== '') {
                $subjectByCode[$code] =
                    $subject;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GET LATEST ATTEMPT FOR EACH SUBJECT
        |--------------------------------------------------------------------------
        |
        | The latest attempt is the current state of the subject:
        |
        | 75+ = completed
        | 74 or below = unresolved failure
        |
        | This is important when the same subject is failed more than once.
        | The latest failed attempt determines the next retake slot.
        |
        |--------------------------------------------------------------------------
        */

        $latestGrades = AcademicGrade::where(
            'student_id',
            $student->student_id
        )
            ->orderBy('subject_id')
            ->orderByDesc('attempt_no')
            ->orderByDesc('grade_id')
            ->get()
            ->groupBy('subject_id')
            ->map(function ($grades) {
                return $grades->first();
            });

        $passedSubjects = [];
        $failedSubjects = [];

        foreach ($latestGrades as $grade) {

            $subjectId =
                (int) $grade->subject_id;

            if (!isset($subjectById[$subjectId])) {
                continue;
            }

            if ((float) $grade->grade >= 75) {

                $passedSubjects[$subjectId] =
                    $grade;

            } else {

                $failedSubjects[$subjectId] =
                    $grade;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PARSE PREREQUISITES
        |--------------------------------------------------------------------------
        |
        | Supported examples:
        |   PROG 1
        |   PROG 1, MS 1
        |   PROG 1 AND MS 1
        |   PROG 1 / MS 1
        |   PROG 1 OR MS 1
        |   All
        |
        | For OR, each OR group is an alternative.
        | For AND/comma/slash, all subjects in that group are required.
        |
        |--------------------------------------------------------------------------
        */

        $parsePrerequisites = function ($text) {

            $text = trim(
                (string) $text
            );

            if (
                $text === '' ||
                in_array(
                    strtoupper($text),
                    ['NONE', 'N/A', 'NA', '-', '0'],
                    true
                )
            ) {
                return [];
            }

            $groups = preg_split(
                '/\s+OR\s+/i',
                $text
            );

            $requirements = [];

            foreach ($groups as $group) {

                $tokens = preg_split(
                    '/\s*(?:,|;|\/|&|\s+AND\s+)\s*/i',
                    trim($group)
                );

                $codes = [];

                foreach ($tokens as $token) {

                    $code =
                        $this->normalizeCourseCode(
                            $token
                        );

                    if ($code !== '') {
                        $codes[] = $code;
                    }
                }

                if (!empty($codes)) {

                    $requirements[] =
                        array_values(
                            array_unique($codes)
                        );
                }
            }

            return $requirements;
        };

        /*
        |--------------------------------------------------------------------------
        | BUILD PREREQUISITE REQUIREMENTS
        |--------------------------------------------------------------------------
        */

        $requirementsBySubjectId = [];

        foreach ($prospectusSubjects as $subject) {

            $prerequisiteText =
                trim(
                    (string) $subject->prerequisite
                );

            /*
            |--------------------------------------------------------------------------
            | "ALL" MEANS ALL PREVIOUS CURRICULUM SUBJECTS
            |--------------------------------------------------------------------------
            */

            if (
                strtoupper($prerequisiteText) ===
                'ALL'
            ) {

                $subjectYearLevel =
                    $this->normalizeYearLevel(
                        $subject->year_level
                    );

                $subjectSemesterNumber =
                    $this->normalizeSemesterNumber(
                        $subject->semester
                    );

                $requirements = [];

                if (
                    $subjectYearLevel !== null &&
                    $subjectSemesterNumber !== null
                ) {

                    $subjectCurriculumPosition =
                        ($subjectYearLevel * 2)
                        + ($subjectSemesterNumber - 1);

                    $allPrerequisiteCodes = [];

                    foreach ($prospectusSubjects as $candidate) {

                        if (
                            (int) $candidate->subject_id ===
                            (int) $subject->subject_id
                        ) {
                            continue;
                        }

                        $candidateYearLevel =
                            $this->normalizeYearLevel(
                                $candidate->year_level
                            );

                        $candidateSemesterNumber =
                            $this->normalizeSemesterNumber(
                                $candidate->semester
                            );

                        if (
                            $candidateYearLevel === null ||
                            $candidateSemesterNumber === null
                        ) {
                            continue;
                        }

                        $candidateCurriculumPosition =
                            ($candidateYearLevel * 2)
                            + ($candidateSemesterNumber - 1);

                        if (
                            $candidateCurriculumPosition >=
                            $subjectCurriculumPosition
                        ) {
                            continue;
                        }

                        $candidateCode =
                            $this->normalizeCourseCode(
                                $candidate->subject_code
                            );

                        if ($candidateCode !== '') {
                            $allPrerequisiteCodes[] =
                                $candidateCode;
                        }
                    }

                    $allPrerequisiteCodes =
                        array_values(
                            array_unique(
                                $allPrerequisiteCodes
                            )
                        );

                    if (!empty($allPrerequisiteCodes)) {

                        $requirements = [
                            $allPrerequisiteCodes
                        ];
                    }
                }

            } else {

                $requirements =
                    $parsePrerequisites(
                        $prerequisiteText
                    );
            }

            $requirementsBySubjectId[
                (int) $subject->subject_id
            ] = $requirements;
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT NORMAL SLOT
        |--------------------------------------------------------------------------
        |
        | Example:
        | Admission Year = 2024
        |
        | 1st Year = school year ending 2025
        | 2nd Year = school year ending 2026
        | 3rd Year = school year ending 2027
        | 4th Year = school year ending 2028
        |
        |--------------------------------------------------------------------------
        */

        $subjectNormalSlot = function ($subject) use (
            $admissionYear
        ) {

            $yearLevel =
                $this->normalizeYearLevel(
                    $subject->year_level
                );

            $semesterNumber =
                $this->normalizeSemesterNumber(
                    $subject->semester
                );

            if (
                $yearLevel === null ||
                $semesterNumber === null
            ) {
                return null;
            }

            $projectedSchoolYear =
                $admissionYear + $yearLevel;

            return [
                'year' =>
                    $projectedSchoolYear,

                'semester' =>
                    $semesterNumber,

                'index' =>
                    (
                        $projectedSchoolYear * 2
                    )
                    + ($semesterNumber - 1),
            ];
        };

        /*
        |--------------------------------------------------------------------------
        | ACTUAL COMPLETION SLOT OF A PASSED ATTEMPT
        |--------------------------------------------------------------------------
        */

        $gradeCompletionSlot = function ($grade) {

            $semester =
                Semester::find(
                    $grade->semester_id
                );

            $slot =
                $this->getSemesterSlot(
                    $semester
                );

            if (
                $slot['year'] === null ||
                $slot['semester'] === null
            ) {
                return null;
            }

            return [
                'year' =>
                    $slot['year'],

                'semester' =>
                    $slot['semester'],

                'index' =>
                    $slot['index'],
            ];
        };

        /*
        |--------------------------------------------------------------------------
        | RETAKE SLOT OF A FAILED ATTEMPT
        |--------------------------------------------------------------------------
        |
        | A failed subject is retaken in the next school year in the
        | same semester.
        |
        |--------------------------------------------------------------------------
        */

        $failureRetakeSlot = function ($grade) {

            $failureSemester =
                Semester::find(
                    $grade->semester_id
                );

            $slot =
                $this->getSemesterSlot(
                    $failureSemester
                );

            if (
                $slot['year'] === null ||
                $slot['semester'] === null
            ) {
                return null;
            }

            $retakeYear =
                $slot['year'] + 1;

            return [
                'year' =>
                    $retakeYear,

                'semester' =>
                    $slot['semester'],

                'index' =>
                    (
                        $retakeYear * 2
                    )
                    + ($slot['semester'] - 1),
            ];
        };

        /*
        |--------------------------------------------------------------------------
        | NEXT OFFERING AFTER A COMPLETION SLOT
        |--------------------------------------------------------------------------
        |
        | A subject can only be completed in its prospectus semester.
        | The offering must be strictly after the prerequisite completion.
        |
        |--------------------------------------------------------------------------
        */

        $nextOfferingAfter =
            function (
                $subject,
                $afterSlot
            ) {

                $semesterNumber =
                    $this->normalizeSemesterNumber(
                        $subject->semester
                    );

                if ($semesterNumber === null) {
                    return null;
                }

                $year =
                    (int) $afterSlot['year'];

                $afterSemester =
                    (int) $afterSlot['semester'];

                if (
                    $semesterNumber <=
                    $afterSemester
                ) {
                    $year++;
                }

                return [
                    'year' =>
                        $year,

                    'semester' =>
                        $semesterNumber,

                    'index' =>
                        (
                            $year * 2
                        )
                        + ($semesterNumber - 1),
                ];
            };

        /*
        |--------------------------------------------------------------------------
        | INITIAL COMPLETION SLOTS
        |--------------------------------------------------------------------------
        |
        | PASSED:
        |   Use the actual semester in which the latest passing attempt
        |   was recorded.
        |
        | FAILED:
        |   Use the next-school-year same-semester retake.
        |
        | NEVER ENCODED:
        |   Use the normal Prospectus slot.
        |
        |--------------------------------------------------------------------------
        */

        $completionSlots = [];

        foreach ($prospectusSubjects as $subject) {

            $subjectId =
                (int) $subject->subject_id;

            $normalSlot =
                $subjectNormalSlot(
                    $subject
                );

            if ($normalSlot === null) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | ALREADY PASSED
            |--------------------------------------------------------------------------
            */

            if (
                isset(
                    $passedSubjects[$subjectId]
                )
            ) {

                $passedSlot =
                    $gradeCompletionSlot(
                        $passedSubjects[$subjectId]
                    );

                if ($passedSlot !== null) {

                    $completionSlots[$subjectId] = [
                        'year' =>
                            $passedSlot['year'],

                        'semester' =>
                            $passedSlot['semester'],

                        'index' =>
                            $passedSlot['index'],

                        'fixed' =>
                            true,
                    ];

                } else {

                    $completionSlots[$subjectId] = [
                        'year' =>
                            $normalSlot['year'],

                        'semester' =>
                            $normalSlot['semester'],

                        'index' =>
                            $normalSlot['index'],

                        'fixed' =>
                            true,
                    ];
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | CURRENTLY FAILED / UNRESOLVED
            |--------------------------------------------------------------------------
            */

            if (
                isset(
                    $failedSubjects[$subjectId]
                )
            ) {

                $retakeSlot =
                    $failureRetakeSlot(
                        $failedSubjects[$subjectId]
                    );

                /*
                |--------------------------------------------------------------------------
                | DO NOT PROJECT AN OVERDUE RETAKE IN THE PAST
                |--------------------------------------------------------------------------
                | If the next retake opportunity after the recorded failure has
                | already passed by the current evaluated semester, project the
                | next offering after the current evaluation instead.
                |--------------------------------------------------------------------------
                */

                if (
                    $retakeSlot !== null &&
                    $retakeSlot['index'] <= $evaluationSlot['index']
                ) {
                    $retakeSlot = $nextOfferingAfter(
                        $subject,
                        $evaluationSlot
                    );
                }

                if ($retakeSlot !== null) {

                    $completionSlots[$subjectId] = [
                        'year' =>
                            $retakeSlot['year'],

                        'semester' =>
                            $retakeSlot['semester'],

                        'index' =>
                            $retakeSlot['index'],

                        'fixed' =>
                            false,
                    ];

                } else {

                    $completionSlots[$subjectId] = [
                        'year' =>
                            $normalSlot['year'],

                        'semester' =>
                            $normalSlot['semester'],

                        'index' =>
                            $normalSlot['index'],

                        'fixed' =>
                            false,
                    ];
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NO GRADE RECORD
            |--------------------------------------------------------------------------
            |
            | The system encodes FAILED SUBJECTS only. Therefore, a subject with
            | no AcademicGrade record is NOT treated as an uncompleted subject.
            | It is assumed to have been completed in its normal Prospectus slot.
            |
            | This prevents subjects that were not encoded because they were
            | passed from being incorrectly projected into later school years.
            |
            |--------------------------------------------------------------------------
            */

            $completionSlots[$subjectId] = [
                'year' =>
                    $normalSlot['year'],

                'semester' =>
                    $normalSlot['semester'],

                'index' =>
                    $normalSlot['index'],

                'fixed' =>
                    true,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ITERATIVELY APPLY PREREQUISITE DELAYS
        |--------------------------------------------------------------------------
        |
        | This is what makes repeated prerequisite failures propagate:
        |
        | PROG 1
        |    ↓
        | PROG 2
        |    ↓
        | DATASTRUC 1
        |    ↓
        | DBMS 1
        |    ↓
        | DBMS 2
        |
        | If PROG 1 is failed again, its completion moves another year.
        | Every dependent subject is then moved to its next available
        | semester, and the process continues through the chain.
        |
        |--------------------------------------------------------------------------
        */

        $maxIterations =
            max(
                10,
                count($prospectusSubjects) * 5
            );

        for (
            $iteration = 0;
            $iteration < $maxIterations;
            $iteration++
        ) {

            $changed = false;

            foreach (
                $prospectusSubjects
                as $subject
            ) {

                $subjectId =
                    (int) $subject->subject_id;

                if (
                    !isset(
                        $completionSlots[$subjectId]
                    )
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | PASSED SUBJECTS KEEP THEIR ACTUAL COMPLETION
                |--------------------------------------------------------------------------
                */

                if (
                    $completionSlots[$subjectId]['fixed']
                ) {
                    continue;
                }

                $requirements =
                    $requirementsBySubjectId[
                        $subjectId
                    ] ?? [];

                if (empty($requirements)) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CHECK EACH PREREQUISITE ALTERNATIVE
                |--------------------------------------------------------------------------
                |
                | Example:
                |
                | A AND B
                | = both must be completed.
                |
                | A OR B
                | = either complete alternative is enough.
                |
                |--------------------------------------------------------------------------
                */

                $alternativeCompletionIndexes = [];

                foreach (
                    $requirements as $group
                ) {

                    $groupSatisfied = true;

                    $groupLatestIndex = null;

                    foreach (
                        $group as
                        $prerequisiteCode
                    ) {

                        $prerequisite =
                            $subjectByCode[
                                $prerequisiteCode
                            ] ?? null;

                        if (!$prerequisite) {

                            $groupSatisfied =
                                false;

                            break;
                        }

                        $prerequisiteId =
                            (int)
                            $prerequisite->subject_id;

                        if (
                            !isset(
                                $completionSlots[
                                    $prerequisiteId
                                ]
                            )
                        ) {

                            $groupSatisfied =
                                false;

                            break;
                        }

                        $prerequisiteSlot =
                            $completionSlots[
                                $prerequisiteId
                            ];

                        if (
                            $prerequisiteSlot['index'] ===
                            null
                        ) {

                            $groupSatisfied =
                                false;

                            break;
                        }

                        if (
                            $groupLatestIndex ===
                            null
                        ) {

                            $groupLatestIndex =
                                (int)
                                $prerequisiteSlot['index'];

                        } else {

                            $groupLatestIndex =
                                max(
                                    $groupLatestIndex,
                                    (int)
                                    $prerequisiteSlot['index']
                                );
                        }
                    }

                    if (
                        $groupSatisfied &&
                        $groupLatestIndex !== null
                    ) {

                        $alternativeCompletionIndexes[] =
                            $groupLatestIndex;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | NO COMPLETE PREREQUISITE ALTERNATIVE
                |--------------------------------------------------------------------------
                */

                if (
                    empty(
                        $alternativeCompletionIndexes
                    )
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | USE THE EARLIEST SATISFIED ALTERNATIVE
                |--------------------------------------------------------------------------
                */

                $requiredAfterIndex =
                    min(
                        $alternativeCompletionIndexes
                    );

                $currentIndex =
                    (int)
                    $completionSlots[
                        $subjectId
                    ]['index'];

                /*
                |--------------------------------------------------------------------------
                | MOVE SUBJECT IF ITS CURRENT SLOT IS TOO EARLY
                |--------------------------------------------------------------------------
                */

                if (
                    $currentIndex <=
                    $requiredAfterIndex
                ) {

                    $afterYear =
                        intdiv(
                            $requiredAfterIndex,
                            2
                        );

                    $afterSemester =
                        (
                            $requiredAfterIndex % 2
                        ) + 1;

                    $nextSlot =
                        $nextOfferingAfter(
                            $subject,
                            [
                                'year' =>
                                    $afterYear,

                                'semester' =>
                                    $afterSemester,

                                'index' =>
                                    $requiredAfterIndex,
                            ]
                        );

                    if (
                        $nextSlot !== null &&
                        $nextSlot['index'] >
                        $currentIndex
                    ) {

                        $completionSlots[
                            $subjectId
                        ] = [
                            'year' =>
                                $nextSlot['year'],

                            'semester' =>
                                $nextSlot['semester'],

                            'index' =>
                                $nextSlot['index'],

                            'fixed' =>
                                false,
                        ];

                        $changed = true;
                    }
                }
            }

            if (!$changed) {
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DETERMINE NORMAL PROGRAM END
        |--------------------------------------------------------------------------
        */

        $normalProgramEndIndex = null;

        foreach (
            $prospectusSubjects
            as $subject
        ) {

            $normalSlot =
                $subjectNormalSlot(
                    $subject
                );

            if ($normalSlot === null) {
                continue;
            }

            if (
                $normalProgramEndIndex ===
                null
            ) {

                $normalProgramEndIndex =
                    $normalSlot['index'];

            } else {

                $normalProgramEndIndex =
                    max(
                        $normalProgramEndIndex,
                        $normalSlot['index']
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FIND FINAL PROJECTED COMPLETION
        |--------------------------------------------------------------------------
        */

        $maxProjectedIndex =
            $normalProgramEndIndex;

        foreach (
            $completionSlots as $slot
        ) {

            if (
                $slot['index'] ===
                null
            ) {
                continue;
            }

            if (
                $maxProjectedIndex ===
                null
            ) {

                $maxProjectedIndex =
                    $slot['index'];

            } else {

                $maxProjectedIndex =
                    max(
                        $maxProjectedIndex,
                        (int) $slot['index']
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | IDENTIFY DELAYED SUBJECTS
        |--------------------------------------------------------------------------
        */

        $delayedSubjects = [];

        foreach (
            $prospectusSubjects
            as $subject
        ) {

            $subjectId =
                (int) $subject->subject_id;

            $normalSlot =
                $subjectNormalSlot(
                    $subject
                );

            $completionSlot =
                $completionSlots[
                    $subjectId
                ] ?? null;

            if (
                $normalSlot === null ||
                $completionSlot === null ||
                $completionSlot['index'] === null
            ) {
                continue;
            }

            if (
                $completionSlot['index'] <=
                $normalSlot['index']
            ) {
                continue;
            }

            $requirements =
                $requirementsBySubjectId[
                    $subjectId
                ] ?? [];

            $isFailedSubject =
                isset(
                    $failedSubjects[$subjectId]
                );

            /*
            |--------------------------------------------------------------------------
            | A SUBJECT IS REPORTED AS DELAYED WHEN:
            |
            | 1. It is itself unresolved and its retake is later than its
            |    normal curriculum slot; OR
            |
            | 2. Its completion was pushed later because prerequisite timing
            |    forced it to move.
            |--------------------------------------------------------------------------
            */

            if (
                $isFailedSubject ||
                !empty($requirements)
            ) {

                $delayYears =
                    max(
                        1,
                        (int) ceil(
                            (
                                $completionSlot['index']
                                -
                                $normalSlot['index']
                            ) / 2
                        )
                    );

                $delayedSubjects[
                    $subjectId
                ] = [
                    'code' =>
                        trim(
                            (string)
                            $subject->subject_code
                        ),

                    'title' =>
                        $subject->subject_title,

                    'delay' =>
                        $delayYears,
                ];
            }
        }

       
        /*
        |--------------------------------------------------------------------------
        | PROJECTED RESIDENCY
        |--------------------------------------------------------------------------
        |
        | The projection is the TOTAL expected residency from admission
        | through projected completion.
        |
        | Normal four-year curriculum:
        |   4 years
        |
        | One-year delay:
        |   At least 5 years
        |
        | Two-year delay:
        |   At least 6 years
        |
        | Delays beyond two years can produce a projection greater than
        | six years. The six-year limit is a risk threshold, not a cap.
        |--------------------------------------------------------------------------
        */

        $elapsedSchoolYears = max(
            0,
            $evaluationYear - $admissionYear
        );

        if ($maxProjectedIndex === null) {
            $projectedYears = $elapsedSchoolYears;
        } else {
            $projectedCompletionYear = intdiv(
                (int) $maxProjectedIndex,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | TOTAL RESIDENCY FROM ADMISSION
            |--------------------------------------------------------------------------
            */

            $projectedYears = max(
                $elapsedSchoolYears,
                $projectedCompletionYear - $admissionYear
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NORMAL PROGRAM DURATION
        |--------------------------------------------------------------------------
        */

        $normalProgramYears = 0;

        if ($normalProgramEndIndex !== null) {
            $normalProgramYear = intdiv(
                (int) $normalProgramEndIndex,
                2
            );

            $normalProgramYears = max(
                0,
                $normalProgramYear - $admissionYear
            );
        }

        /*
        |--------------------------------------------------------------------------
        | GET THE LARGEST DELAY
        |--------------------------------------------------------------------------
        |
        | Use the largest delay instead of adding delays for every subject.
        | This avoids assuming that concurrent subject delays each require
        | a separate additional school year.
        |--------------------------------------------------------------------------
        */

        $maxDelay = 0;
        $delayedSubjectCodes = [];

        foreach ($delayedSubjects as $delayed) {
            $maxDelay = max(
                $maxDelay,
                (int) $delayed['delay']
            );

            if ($delayed['code'] !== '') {
                $delayedSubjectCodes[] = $delayed['code'];
            }
        }

        $result['delay_years'] = $maxDelay;

        $result['delayed_subjects'] = array_values(
            array_unique($delayedSubjectCodes)
        );

        /*
        |--------------------------------------------------------------------------
        | FINAL PROJECTED RESIDENCY
        |--------------------------------------------------------------------------
        |
        | Compare all relevant values:
        | 1. Four-year minimum program duration.
        | 2. Normal program duration from the prospectus.
        | 3. Total years calculated from the projected completion year.
        | 4. Normal program duration plus the largest identified delay.
        | 5. Years already spent in the program.
        |
        | There is deliberately no upper cap on projected residency.
        |--------------------------------------------------------------------------
        */

        $result['projected_years'] = max(
            4,
            $normalProgramYears,
            $projectedYears,
            $normalProgramYears + $maxDelay,
            $elapsedSchoolYears
        );

        return $result;

    } 
 

    
    /*
    |--------------------------------------------------------------------------
    | NORMALIZE COURSE CODE
    |--------------------------------------------------------------------------
    | Converts course codes to uppercase and removes extra whitespace
    | so prerequisite codes can be matched consistently.
    |--------------------------------------------------------------------------
    */
    private function normalizeCourseCode(mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        return strtoupper(
            preg_replace('/\s+/', ' ', $value)
        );
    }

        
    /*
    |--------------------------------------------------------------------------
    | NORMALIZE YEAR LEVEL
    |--------------------------------------------------------------------------
    | Converts year-level values such as:
    | "1st Year", "First Year", "Year 1", or "1"
    | into their corresponding integer values.
    |--------------------------------------------------------------------------
    */
    private function normalizeYearLevel(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $normalized = strtolower(
            preg_replace('/\s+/', ' ', $value)
        );

        if (
            preg_match('/\b([1-4])\b/', $normalized, $matches)
        ) {
            return (int) $matches[1];
        }

        if (
            strpos($normalized, 'first') !== false ||
            strpos($normalized, '1st') !== false
        ) {
            return 1;
        }

        if (
            strpos($normalized, 'second') !== false ||
            strpos($normalized, '2nd') !== false
        ) {
            return 2;
        }

        if (
            strpos($normalized, 'third') !== false ||
            strpos($normalized, '3rd') !== false
        ) {
            return 3;
        }

        if (
            strpos($normalized, 'fourth') !== false ||
            strpos($normalized, '4th') !== false
        ) {
            return 4;
        }

        return null;
    }

 
        
    /*
    |--------------------------------------------------------------------------
    | NORMALIZE SEMESTER NUMBER
    |--------------------------------------------------------------------------
    */
    private function normalizeSemesterNumber(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/\b([12])\b/', $value, $matches)) {
            return (int) $matches[1];
        }

        $normalized = strtolower(
            preg_replace('/\s+/', ' ', $value)
        );

        if (
            strpos($normalized, 'first') !== false ||
            strpos($normalized, '1st') !== false
        ) {
            return 1;
        }

        if (
            strpos($normalized, 'second') !== false ||
            strpos($normalized, '2nd') !== false
        ) {
            return 2;
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | GET SEMESTER SLOT
    |--------------------------------------------------------------------------
    |
    | Returns a comparable academic slot:
    | year     = ending school year
    | semester = 1 or 2
    | index    = year * 2 + semester offset
    |
    */
    private function getSemesterSlot(?Semester $semester): array
    {
        $result = [
            'year' => null,
            'semester' => null,
            'index' => null,
        ];

        if (!$semester) {
            return $result;
        }

        $schoolYear = trim(
            (string) ($semester->school_year ?? '')
        );

        if (
            preg_match(
                '/((?:19|20)\d{2})\s*[-\/]\s*((?:19|20)\d{2})/',
                $schoolYear,
                $matches
            )
        ) {
            $result['year'] = (int) $matches[2];
        } elseif (
            preg_match(
                '/((?:19|20)\d{2})/',
                $schoolYear,
                $matches
            )
        ) {
            $result['year'] = (int) $matches[1];
        }

        $result['semester'] = $this->normalizeSemesterNumber(
            $semester->semester_name
                ?? $semester->name
                ?? $semester->semester
                ?? null
        );

        if (
            $result['year'] !== null &&
            $result['semester'] !== null
        ) {
            $result['index'] =
                ($result['year'] * 2) +
                ($result['semester'] - 1);
        }

        return $result;
    }




    /* 
    |-------------------------------------------------------------------------- 
    | VALIDATE PREREQUISITE / RETAKE ENROLLMENT 
    |-------------------------------------------------------------------------- 
    | 
    | This prevents an evaluation from recording a subject when: 
    | 
    | 1. Its prerequisite is still failed; or 
    | 2. A failed subject is being retaken in the wrong semester. 
    | 
    | A prerequisite must have been passed in a previous semester. A grade 
    | being entered in the same semester does NOT satisfy its prerequisite. 
    | 
    |-------------------------------------------------------------------------- 
    */ 
    private function validateSubjectEnrollment( 
        Student $student, 
        object $subject, 
        int $semester_id 
    ): ?string { 
 
        $semester = Semester::find($semester_id); 
 
        if (!$semester) { 
            return 'The selected academic semester could not be found.'; 
        } 
 
        $selectedSlot = $this->getSemesterSlot($semester); 
 
        /* 
        |-------------------------------------------------------------------------- 
        | CHECK WHETHER THIS IS A RETAKE OF A FAILED SUBJECT 
        |-------------------------------------------------------------------------- 
        */ 
 
        $latestAttempt = AcademicGrade::where( 
            'student_id', 
            $student->student_id 
        ) 
            ->where( 
                'subject_id', 
                $subject->subject_id 
            ) 
            ->orderByDesc('attempt_no') 
            ->orderByDesc('grade_id') 
            ->first(); 
 
        if ( 
            $latestAttempt && 
            (float) $latestAttempt->grade <= 74 
        ) { 
 
            $failedSemester = Semester::find( 
                $latestAttempt->semester_id 
            ); 
 
            $failedSlot = $this->getSemesterSlot( 
                $failedSemester 
            ); 
 
            if ( 
                $failedSlot['year'] !== null && 
                $failedSlot['semester'] !== null && 
                $selectedSlot['year'] !== null && 
                $selectedSlot['semester'] !== null 
            ) { 
 
                $requiredRetakeYear = 
                    $failedSlot['year'] + 1; 
 
                $requiredRetakeSemester = 
                    $failedSlot['semester']; 
 
                if ( 
                    $selectedSlot['year'] < 
                    $requiredRetakeYear 
                ) { 
 
                     
                    $requiredSemesterLabel = 
                        $requiredRetakeSemester === 1 
                            ? '1st Semester' 
                            : '2nd Semester'; 
 
                    return "Course {$subject->subject_code} was failed in {$failedSemester->school_year} - {$failedSemester->semester_name}. Under the retake rule, it can be enrolled again starting in the {$requiredSemesterLabel} of the next school year."; 
                } 
 
                if ( 
                    $selectedSlot['semester'] !== 
                    $requiredRetakeSemester 
                ) { 
 
                    $semesterLabel = 
                        $requiredRetakeSemester === 1 
                            ? '1st Semester' 
                            : '2nd Semester'; 
 
                    return "Course {$subject->subject_code} was failed in {$failedSemester->school_year} - {$failedSemester->semester_name}. It may be retaken in the {$semesterLabel} of the next school year, not in {$selectedSlot['semester']} semester."; 
                } 
            } 
        } 
 
        /* 
        |-------------------------------------------------------------------------- 
        | CHECK PREREQUISITES 
        |-------------------------------------------------------------------------- 
        */ 
 
        $prerequisiteText = trim( 
            (string) $subject->prerequisite 
        ); 
 
        if ( 
            $prerequisiteText === '' || 
            in_array( 
                strtoupper($prerequisiteText), 
                ['NONE', 'N/A', 'NA', '-', '0'], 
                true 
            ) 
        ) { 
            return null; 
        } 
 
        $groups = preg_split( 
            '/\s+OR\s+/i', 
            $prerequisiteText 
        ); 
 
        /* 
        | Each OR group is an alternative. Every prerequisite inside one 
        | group must be passed. At least one complete group must be passed. 
        */ 
        foreach ($groups as $group) { 
 
            $tokens = preg_split( 
                '/\s*(?:,|;|\/|&|\s+AND\s+)\s*/i', 
                trim($group) 
            ); 
 
            $groupSatisfied = true; 
 
            foreach ($tokens as $token) { 
 
                $code = $this->normalizeCourseCode($token); 
 
                if ($code === '') { 
                    continue; 
                } 
 
                $prerequisite = DB::table('prospectus') 
                    ->whereRaw( 
                        'UPPER(subject_code) = ?', 
                        [$code] 
                    ) 
                    ->first(); 
 
                if (!$prerequisite) { 
                    $groupSatisfied = false; 
                    break; 
                } 
 
                /* 
                | A prerequisite must already have a passing grade. A grade 
                | entered in the same semester does not satisfy it. 
                */ 
                $passed = AcademicGrade::where( 
                    'student_id', 
                    $student->student_id 
                ) 
                    ->where( 
                        'subject_id', 
                        $prerequisite->subject_id 
                    ) 
                    ->where( 
                        'grade', 
                        '>=', 
                        75 
                    ) 
                    ->exists(); 
 
                if (!$passed) { 
                    $groupSatisfied = false; 
                    break; 
                } 
            } 
 
            if ($groupSatisfied) { 
                return null; 
            } 
        } 
 
        return "Course {$subject->subject_code} cannot be enrolled because one or more of its prerequisite subjects have not yet been passed."; 
    } 
 
 
    public function history(int $id) 
    { 
        if (!session()->has('user_id')) { 
            return redirect('/') 
                ->with('error', 'Please login first.'); 
        } 
 
 
        $student = Student::with([ 
            'statusHistory.semester' 
        ])->findOrFail($id); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GET EVALUATED SEMESTERS 
        |-------------------------------------------------------------------------- 
        */ 
 
        $evaluatedSemesters = 
            $student->statusHistory 
                ->filter(function ($history) { 
 
                    return $history->semester !== null; 
 
                }) 
                ->sortByDesc(function ($history) { 
 
                    return $history->semester->semester_id; 
 
                }) 
                ->map(function ($history) { 
 
                    return $history->semester; 
 
                }) 
                ->unique('semester_id') 
                ->values(); 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | SELECTED PERIOD 
        |-------------------------------------------------------------------------- 
        */ 
 
        $selectedSchoolYear = 
            request()->query( 
                'school_year' 
            ); 
 
 
        $selectedSemesterId = 
            request()->query( 
                'semester_id' 
            ); 
 
 
        $selectedHistory = null; 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | FIND SELECTED HISTORY 
        |-------------------------------------------------------------------------- 
        */ 
 
        if ( 
            $selectedSchoolYear && 
            $selectedSemesterId 
        ) { 
 
            $selectedSemester = 
                Semester::where( 
                    'semester_id', 
                    $selectedSemesterId 
                ) 
                    ->where( 
                        'school_year', 
                        $selectedSchoolYear 
                    ) 
                    ->first(); 
 
 
            if ($selectedSemester) { 
 
                $selectedHistory = 
                    $student->statusHistory 
                        ->where( 
                            'semester_id', 
                            $selectedSemester->semester_id 
                        ) 
                        ->sortByDesc( 
                            'status_id' 
                        ) 
                        ->first(); 
            } 
        } 
 
 
        return view( 
            'students.history', 
            compact( 
                'student', 
                'evaluatedSemesters', 
                'selectedSchoolYear', 
                'selectedSemesterId', 
                'selectedHistory' 
            ) 
        ); 
    } 
 
 
 
public function priorityAlert(int $id)
{
    $student = Student::with([
        'statusHistory.semester'
    ])->findOrFail($id);

    /*
    |--------------------------------------------------------------------------
    | GET COMPLETE EVALUATION HISTORY
    |--------------------------------------------------------------------------
    | Group by semester and use the most recently saved status record
    | for each semester. Order by actual evaluation timestamp.
    |--------------------------------------------------------------------------
    */

    $evaluations = $student->statusHistory
        ->filter(function ($history) {
            return $history->semester_id !== null;
        })
        ->groupBy('semester_id')
        ->map(function ($semesterEvaluations) {

            return $semesterEvaluations
                ->sort(function ($a, $b) {

                    $dateComparison = strcmp(
                        (string) $b->created_at,
                        (string) $a->created_at
                    );

                    if ($dateComparison !== 0) {
                        return $dateComparison;
                    }

                    return (int) $b->status_id
                        <=> (int) $a->status_id;
                })
                ->first();

        })
        ->sort(function ($a, $b) {

            $dateComparison = strcmp(
                (string) $b->created_at,
                (string) $a->created_at
            );

            if ($dateComparison !== 0) {
                return $dateComparison;
            }

            return (int) $b->status_id
                <=> (int) $a->status_id;
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | RECENT AND PREVIOUS EVALUATION
    |--------------------------------------------------------------------------
    */

    $recentEvaluation = $evaluations->first();

    $previousEvaluation = $evaluations->skip(1)->first();

    /*
    |--------------------------------------------------------------------------
    | CALCULATE PRIORITY ALERT
    |--------------------------------------------------------------------------
    */

    $priorityAlert = false;

    if ($recentEvaluation && $previousEvaluation) {

        $recentFailure =
            (float) $recentEvaluation->failure_percentage;

        $previousFailure =
            (float) $previousEvaluation->failure_percentage;

        if ($recentFailure >= $previousFailure) {
            $priorityAlert = true;
        }
    }

    return view(
        'students.priority-alert',
        compact(
            'student',
            'recentEvaluation',
            'previousEvaluation',
            'evaluations',
            'priorityAlert'
        )
    );
}

 
 
    /*
    |--------------------------------------------------------------------------
    | MARK BACKLOG AS PASSED
    |--------------------------------------------------------------------------
    |
    | Create a new passing attempt without changing or deleting the
    | original failed attempt.
    |
    */
    public function markBacklogAsPassed(
        int $id,
        int $subjectId
    ) {
        if (!session()->has('user_id')) {
            return redirect('/')
                ->with('error', 'Please login first.');
        }

        $student = Student::findOrFail($id);

        $latestAttempt = AcademicGrade::where(
            'student_id',
            $student->student_id
        )
            ->where(
                'subject_id',
                $subjectId
            )
            ->orderByDesc('attempt_no')
            ->orderByDesc('grade_id')
            ->first();

        if (!$latestAttempt) {
            return redirect()
                ->route(
                    'students.show',
                    $student->student_id
                )
                ->with(
                    'error',
                    'The selected backlog could not be found.'
                );
        }

        if ((float) $latestAttempt->grade >= 75) {
            return redirect()
                ->route(
                    'students.show',
                    $student->student_id
                )
                ->with(
                    'error',
                    'This backlog is already resolved.'
                );
        }

        $nextAttemptNo = AcademicGrade::where(
            'student_id',
            $student->student_id
        )
            ->where(
                'subject_id',
                $subjectId
            )
            ->max('attempt_no');

        $nextAttemptNo =
            ((int) $nextAttemptNo) + 1;

        AcademicGrade::create([
            'student_id' =>
                $student->student_id,

            'semester_id' =>
                $latestAttempt->semester_id,

            'subject_id' =>
                $subjectId,

            'encoded_by' =>
                session('user_id'),

            /*
            | 75 is the minimum passing grade used by
            | the existing system rules.
            */
            'grade' =>
                75,

            'attempt_no' =>
                $nextAttemptNo,

            'date_encoded' =>
                now(),
        ]);

        return redirect()
            ->route(
                'students.show',
                $student->student_id
            )
            ->with(
                'success',
                'Backlog marked as passed successfully. The subject is now in Resolved Backlogs.'
            );
    }


    /* 
    |-------------------------------------------------------------------------- 
    | DELETE 
    |-------------------------------------------------------------------------- 
    */ 
    public function destroy(string $id) 
    { 
        if (!session()->has('user_id')) { 
            return redirect('/') 
                ->with('error', 'Please login first.'); 
        } 
 
 
        $student = 
            Student::findOrFail($id); 
 
 
        $student->delete(); 
 
 
        return redirect() 
            ->route('students.index') 
            ->with( 
                'success', 
                'Student deleted successfully.' 
            ); 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | STATUS LIST 
    |-------------------------------------------------------------------------- 
    */ 
    public function statusList(string $status) 
    { 
        if (!session()->has('user_id')) { 
            return redirect('/') 
                ->with('error', 'Please login first.'); 
        } 
 
 
        $statusToFind = 
            $status; 
 
 
        /* 
        | Normalize route value 
        */ 
 
        if ($status === 'For Shifting Out') {

            $statusToFind =
                'Shifting Out';
        } elseif ($status === 'Regular') {

            // Treat the old status name as the renamed status.
            $statusToFind =
                'Below Treshold';
        }


        $students =
            Student::with('latestStatus')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->filter(function ($student) use (
                    $statusToFind
                ) {

                    $studentStatus = optional(
                        $student->latestStatus
                    )->academic_status;

                    // Include legacy Regular records in the renamed status list.
                    if (
                        $statusToFind === 'Below Treshold' &&
                        $studentStatus === 'Regular'
                    ) {
                        return true;
                    }

                    return $studentStatus === $statusToFind;

                })
                ->values(); 
 
 
        return view( 
            'students.status-list', 
            compact( 
                'students', 
                'status' 
            ) 
        ); 
    } 
}