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
 
                $q->where( 
                    'first_name', 
                    'ILIKE', 
                    "%{$search}%" 
                ) 
                ->orWhere( 
                    'middle_name', 
                    'ILIKE', 
                    "%{$search}%" 
                ) 
                ->orWhere( 
                    'last_name', 
                    'ILIKE', 
                    "%{$search}%" 
                ) 
                ->orWhere( 
                    'student_no', 
                    'ILIKE', 
                    "%{$search}%" 
                ); 
 
            }); 
 
        }) 
        ->orderBy('last_name') 
        ->orderBy('first_name') 
        ->get(); 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | CALCULATE PRIORITY ALERT FROM PERFORMANCE HISTORY 
    |-------------------------------------------------------------------------- 
    | 
    | Priority Alert is NOT based on academic_status. 
    | 
    | It is TRUE only when: 
    | 
    | 1. The student has a previous evaluation; AND 
    | 2. The latest failure percentage is equal to or higher than 
    |    the previous failure percentage. 
    | 
    | Lower failure percentage = improvement = NO Priority Alert. 
    | 
    */ 
 
    $students->each(function ($student) { 
 
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
 
 
        $priorityAlert = false; 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | ONLY COMPARE WHEN A PREVIOUS EVALUATION EXISTS 
        |-------------------------------------------------------------------------- 
        */ 
 
        if ( 
            $latestEvaluation && 
            $previousEvaluation 
        ) { 
 
            $currentFailure = 
                (float) $latestEvaluation->failure_percentage; 
 
            $previousFailure = 
                (float) $previousEvaluation->failure_percentage; 
 
 
            /* 
            |-------------------------------------------------------------------------- 
            | NO IMPROVEMENT OR WORSE 
            |-------------------------------------------------------------------------- 
            | 
            | Current < Previous 
            | = Improvement 
            | = NO ALERT 
            | 
            | Current = Previous 
            | = No improvement 
            | = ALERT 
            | 
            | Current > Previous 
            | = Worse 
            | = ALERT 
            | 
            */ 
 
            if ( 
                $currentFailure >= 
                $previousFailure 
            ) { 
 
                $priorityAlert = true; 
 
            } 
 
        } 
 
 
        /* 
        |-------------------------------------------------------------------------- 
        | SET DISPLAY VALUE ONLY 
        |-------------------------------------------------------------------------- 
        */ 
 
        if ($student->latestStatus) { 
 
            $student->latestStatus->priority_alert = 
                $priorityAlert; 

            /*
            | Old records may contain Residency Risk as an academic status.
            | It is now a warning only, so display those old records as
            | Regular without changing the database record here.
            */
            if (
                $student->latestStatus->academic_status ===
                'Residency Risk'
            ) {
                $student->latestStatus->academic_status =
                    'Regular';
            }
 
        } 
 


        /*
        |--------------------------------------------------------------------------
        | RESIDENCY WARNING FOR STUDENT LIST
        |--------------------------------------------------------------------------
        | Residency Risk is a display/filter warning only. It does NOT
        | replace the student's academic status.
        |--------------------------------------------------------------------------
        */

        $student->residency_risk = false;
        $student->projected_residency_years = null;
        $student->residency_exceeded = false;

        if (
            $latestEvaluation &&
            $latestEvaluation->semester_id
        ) {

            $evaluationSemester =
                $latestEvaluation->semester
                ?? Semester::find($latestEvaluation->semester_id);

            $evaluationYear =
                $this->getEvaluationYear(
                    $evaluationSemester
                );

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
                $yearsSpent,
                (int) $prerequisiteResidency['projected_years']
            );

            $student->projected_residency_years =
                $projectedResidencyYears;

            $student->residency_exceeded =
                $yearsSpent > 6;

            $student->residency_risk =
                $projectedResidencyYears > 6;
        }
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
 
        $latestStatus = StudentStatusHistory::where( 
            'student_id', 
            $student->student_id 
        ) 
            ->orderByDesc('semester_id') 
            ->orderByDesc('status_id') 
            ->first(); 
 
 
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
                'semesters', 
                'failedSubjects', 
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
            | evaluation is still recorded as 0% failure and Regular. 
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
    | Below 25% = Regular 
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
 
            $status = 'Regular'; 
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
    public function getResidencyProjection(Student $student): array
    {
        $latestEvaluation = $student->latestStatus;

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
        | RESULT 
        |-------------------------------------------------------------------------- 
        | 
        | projected_years is the projected number of school years from the 
        | student's admission year to the latest projected curriculum slot. 
        | 
        | The calculation is semester-aware: 
        | 
        | - A failed subject is retaken in the NEXT SCHOOL YEAR in the same 
        |   semester in which it was failed. 
        | - A dependent subject cannot be taken until every required 
        |   prerequisite has been passed. 
        | - If a dependent subject is missed because its prerequisite is still 
        |   failed, it is moved to its next available offering. 
        | - The calculation follows prerequisite chains. 
        | 
        |-------------------------------------------------------------------------- 
        */ 
 
        $admissionYear = (int) $student->admission_year; 
 
        $currentSemester = Semester::find($semester_id); 
 
        $currentSlot = $this->getSemesterSlot($currentSemester); 
 
        $fallbackCurrentYear = 
            $currentSlot['year'] !== null 
                ? $currentSlot['year'] 
                : (int) $evaluationYear; 
 
        $baseProjectedYears = max( 
            0, 
            $fallbackCurrentYear - $admissionYear 
        ); 
 
        $result = [ 
            'projected_years' => $baseProjectedYears, 
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
 
            $subjectById[(int) $subject->subject_id] = $subject; 
 
            $code = $this->normalizeCourseCode( 
                $subject->subject_code 
            ); 
 
            if ($code !== '') { 
                $subjectByCode[$code] = $subject; 
            } 
        } 
 
        /* 
        |-------------------------------------------------------------------------- 
        | GET THE LATEST ATTEMPT FOR EACH SUBJECT 
        |-------------------------------------------------------------------------- 
        | 
        | A subject is considered resolved only when its latest attempt is 
        | 75 or higher. A previous failure followed by a passing attempt is 
        | therefore not treated as a current prerequisite blocker. 
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
 
            $subjectId = (int) $grade->subject_id; 
 
            if (!isset($subjectById[$subjectId])) { 
                continue; 
            } 
 
            if ((float) $grade->grade >= 75) { 
 
                $passedSubjects[$subjectId] = true; 
 
            } else { 
 
                $failedSubjects[$subjectId] = $grade; 
            } 
        } 
 
        if (empty($failedSubjects)) { 
            return $result; 
        } 
 
        /* 
        |-------------------------------------------------------------------------- 
        | BUILD PREREQUISITE REQUIREMENTS 
        |-------------------------------------------------------------------------- 
        | 
        | The parser supports common values such as: 
        |   IT101 
        |   IT101, IT102 
        |   IT101 and IT102 
        |   IT101 / IT102 
        |   IT101 or IT102 
        | 
        | For OR expressions, a dependent subject is considered satisfied 
        | when at least one prerequisite in the OR group is passed. 
        | Commas, AND and slash are treated as separate required prerequisites. 
        | 
        |-------------------------------------------------------------------------- 
        */ 
 
        $parsePrerequisites = function ($text) { 
 
            $text = trim((string) $text); 
 
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
 
                    $code = $this->normalizeCourseCode($token); 
 
                    if ($code !== '') { 
                        $codes[] = $code; 
                    } 
                } 
 
                if (!empty($codes)) { 
                    $requirements[] = array_values( 
                        array_unique($codes) 
                    ); 
                } 
            } 
 
            return $requirements; 
        }; 
 
        $requirementsBySubjectId = []; 
        $dependencyMap = []; 
 
        foreach ($prospectusSubjects as $subject) { 
 
            $requirements = $parsePrerequisites( 
                $subject->prerequisite 
            ); 
 
            $requirementsBySubjectId[ 
                (int) $subject->subject_id 
            ] = $requirements; 
 
            foreach ($requirements as $group) { 
 
                foreach ($group as $prerequisiteCode) { 
 
                    $dependencyMap[$prerequisiteCode][] = $subject; 
                } 
            } 
        } 
 
        /* 
        |-------------------------------------------------------------------------- 
        | SUBJECT SLOT HELPERS 
        |-------------------------------------------------------------------------- 
        | 
        | The prospectus year_level + semester determine the normal curriculum 
        | slot. A first-year first-semester subject is slot 0, first-year 
        | second-semester is slot 1, second-year first-semester is slot 2, etc. 
        | 
        | This is used only for projection; existing grade records are never 
        | changed by this calculation. 
        | 
        |-------------------------------------------------------------------------- 
        */ 
 
        $subjectNormalSlot = function ($subject) use ($admissionYear) { 
 
            $yearLevel = $this->normalizeYearLevel( 
                $subject->year_level 
            ); 
 
            $semesterNumber = $this->normalizeSemesterNumber( 
                $subject->semester 
            ); 
 
            if ($yearLevel === null || $semesterNumber === null) { 
                return null; 
            } 
 
            return [ 
                'year' => $admissionYear + ($yearLevel - 1), 
                'semester' => $semesterNumber, 
                'index' => 
                    (($admissionYear + ($yearLevel - 1)) * 2) 
                    + ($semesterNumber - 1), 
            ]; 
        }; 
 
        /* 
        |-------------------------------------------------------------------------- 
        | FIND ORIGINAL FAILURE SLOT 
        |-------------------------------------------------------------------------- 
        | 
        | The retake rule is based on the semester in which the subject was 
        | failed. Example: 
        | 
        |   Failed: 2025-2026, 2nd Semester 
        |   Retake: 2026-2027, 2nd Semester 
        | 
        |-------------------------------------------------------------------------- 
        */ 
 
        $failureSlotForSubject = function ($grade) { 
 
            $failureSemester = Semester::find( 
                $grade->semester_id 
            ); 
 
            $slot = $this->getSemesterSlot( 
                $failureSemester 
            ); 
 
            if ( 
                $slot['year'] === null || 
                $slot['semester'] === null 
            ) { 
                return null; 
            } 
 
            return [ 
                'year' => $slot['year'] + 1, 
                'semester' => $slot['semester'], 
                'index' => 
                    (($slot['year'] + 1) * 2) 
                    + ($slot['semester'] - 1), 
            ]; 
        }; 
 
        /* 
        |-------------------------------------------------------------------------- 
        | EARLIEST OFFERING AFTER A GIVEN SLOT 
        |-------------------------------------------------------------------------- 
        | 
        | A subject may be offered only in its prospectus semester. Once its 
        | prerequisites are satisfied, it is placed in the first offering 
        | strictly after the semester in which the prerequisite was passed. 
        | 
        |-------------------------------------------------------------------------- 
        */ 
 
        $nextOfferingAfter = function ($subject, $afterSlot) { 
 
            $semesterNumber = $this->normalizeSemesterNumber( 
                $subject->semester 
            ); 
 
            if ($semesterNumber === null) { 
                return null; 
            } 
 
            $year = (int) $afterSlot['year']; 
            $afterSemester = (int) $afterSlot['semester']; 
 
            if ($semesterNumber <= $afterSemester) { 
                $year++; 
            } 
 
            return [ 
                'year' => $year, 
                'semester' => $semesterNumber, 
                'index' => 
                    ($year * 2) + ($semesterNumber - 1), 
            ]; 
        }; 
 
        /* 
        |-------------------------------------------------------------------------- 
        | INITIAL SUBJECT COMPLETION SLOTS 
        |-------------------------------------------------------------------------- 
        | 
        | Passed subjects are already completed. 
        | Failed subjects receive their next-school-year same-semester retake 
        | slot. Unattempted subjects use their normal prospectus slot. 
        | 
        | The current evaluation semester is also respected so the projection 
        | never places an unresolved subject in a semester that has already 
        | passed. 
        | 
        |-------------------------------------------------------------------------- 
        */ 
 
        $completionSlots = []; 
        $failedSubjectCodes = []; 
 
        foreach ($prospectusSubjects as $subject) { 
 
            $subjectId = (int) $subject->subject_id; 
            $normalSlot = $subjectNormalSlot($subject); 
 
            if ($normalSlot === null) { 
                continue; 
            } 
 
            if (isset($passedSubjects[$subjectId])) { 
 
                $completionSlots[$subjectId] = [ 
                    'year' => null, 
                    'semester' => null, 
                    'index' => null, 
                    'fixed' => true, 
                ]; 
 
                continue; 
            } 
 
            if (isset($failedSubjects[$subjectId])) { 
 
                $failedGrade = $failedSubjects[$subjectId]; 
                $retakeSlot = $failureSlotForSubject($failedGrade); 
 
                if ($retakeSlot !== null) { 
                    $completionSlots[$subjectId] = [ 
                        'year' => $retakeSlot['year'], 
                        'semester' => $retakeSlot['semester'], 
                        'index' => $retakeSlot['index'], 
                        'fixed' => false, 
                    ]; 
                } else { 
                    $completionSlots[$subjectId] = [ 
                        'year' => $normalSlot['year'], 
                        'semester' => $normalSlot['semester'], 
                        'index' => $normalSlot['index'], 
                        'fixed' => false, 
                    ]; 
                } 
 
                $failedSubjectCodes[$subjectId] = 
                    trim((string) $subject->subject_code); 
 
                continue; 
            } 
 
            /* 
            | Do not schedule an unattempted subject before the student's 
            | current evaluation point. 
            */ 
            $normalIndex = $normalSlot['index']; 
 
            if ( 
                $currentSlot['index'] !== null && 
                $normalIndex <= $currentSlot['index'] 
            ) { 
                /* 
                | If an old curriculum subject has not been attempted yet, 
                | it is already overdue and must be scheduled at its next 
                | available offering from the current point. 
                */ 
                $afterSlot = $currentSlot; 
 
                $nextSlot = $nextOfferingAfter( 
                    $subject, 
                    $afterSlot 
                ); 
 
                $completionSlots[$subjectId] = [ 
                    'year' => $nextSlot['year'], 
                    'semester' => $nextSlot['semester'], 
                    'index' => $nextSlot['index'], 
                    'fixed' => false, 
                ]; 
 
            } else { 
 
                $completionSlots[$subjectId] = [ 
                    'year' => $normalSlot['year'], 
                    'semester' => $normalSlot['semester'], 
                    'index' => $normalSlot['index'], 
                    'fixed' => false, 
                ]; 
            } 
        } 
 
        /* 
        |-------------------------------------------------------------------------- 
        | ITERATIVELY APPLY PREREQUISITE DELAYS 
        |-------------------------------------------------------------------------- 
        | 
        | If a prerequisite is delayed, every dependent subject is moved to 
        | the first semester in which it is offered AFTER that prerequisite 
        | has been passed. This repeats until the schedule stops changing. 
        | 
        | This handles chains such as: 
        | 
        |   A failed 
        |      -> B requires A 
        |      -> C requires B 
        |      -> D requires C 
        | 
        | without assuming that every dependency is only a one-year delay. 
        | 
        |-------------------------------------------------------------------------- 
        */ 
 
        $maxIterations = max( 
            10, 
            count($prospectusSubjects) * 3 
        ); 
 
        for ($iteration = 0; $iteration < $maxIterations; $iteration++) { 
 
            $changed = false; 
 
            foreach ($prospectusSubjects as $subject) { 
 
                $subjectId = (int) $subject->subject_id; 
 
                if (!isset($completionSlots[$subjectId])) { 
                    continue; 
                } 
 
                /* 
                | Already passed subjects do not need rescheduling. 
                */ 
                if ($completionSlots[$subjectId]['fixed']) { 
                    continue; 
                } 
 
                $requirements = 
                    $requirementsBySubjectId[$subjectId] ?? []; 
 
                if (empty($requirements)) { 
                    continue; 
                } 
 
                /* 
                | A prerequisite expression is represented as alternatives: 
                | A AND B = one group containing A and B. 
                | A OR B  = two alternative groups: A or B. 
                */ 
                $alternativeCompletionIndexes = []; 
 
                foreach ($requirements as $group) { 
 
                    $groupSatisfied = true; 
                    $groupLatestIndex = 
                        (int) ($currentSlot['index'] ?? 0); 
 
                    foreach ($group as $prerequisiteCode) { 
 
                        $prerequisite = 
                            $subjectByCode[$prerequisiteCode] ?? null; 
 
                        if (!$prerequisite) { 
                            $groupSatisfied = false; 
                            break; 
                        } 
 
                        $prerequisiteId = 
                            (int) $prerequisite->subject_id; 
 
                        if (!isset($completionSlots[$prerequisiteId])) { 
                            $groupSatisfied = false; 
                            break; 
                        } 
 
                        $prerequisiteSlot = 
                            $completionSlots[$prerequisiteId]; 
 
                        if ($prerequisiteSlot['fixed']) { 
                            continue; 
                        } 
 
                        if ($prerequisiteSlot['index'] === null) { 
                            $groupSatisfied = false; 
                            break; 
                        } 
 
                        $groupLatestIndex = max( 
                            $groupLatestIndex, 
                            (int) $prerequisiteSlot['index'] 
                        ); 
                    } 
 
                    if ($groupSatisfied) { 
                        $alternativeCompletionIndexes[] = 
                            $groupLatestIndex; 
                    } 
                } 
 
                if (empty($alternativeCompletionIndexes)) { 
                    continue; 
                } 
 
                /* Use the earliest prerequisite alternative. */ 
                $requiredAfterIndex = min( 
                    $alternativeCompletionIndexes 
                ); 
 
                if ($requiredAfterIndex === null) { 
                    continue; 
                } 
 
                $currentIndex = 
                    (int) $completionSlots[$subjectId]['index']; 
 
                if ($currentIndex <= $requiredAfterIndex) { 
 
                    $afterYear = intdiv( 
                        $requiredAfterIndex, 
                        2 
                    ); 
 
                    $afterSemester = 
                        ($requiredAfterIndex % 2) + 1; 
 
                    $nextSlot = $nextOfferingAfter( 
                        $subject, 
                        [ 
                            'year' => $afterYear, 
                            'semester' => $afterSemester, 
                            'index' => $requiredAfterIndex, 
                        ] 
                    ); 
 
                    if ( 
                        $nextSlot !== null && 
                        $nextSlot['index'] > $currentIndex 
                    ) { 
 
                        $completionSlots[$subjectId] = [ 
                            'year' => $nextSlot['year'], 
                            'semester' => $nextSlot['semester'], 
                            'index' => $nextSlot['index'], 
                            'fixed' => false, 
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
        | IDENTIFY SUBJECTS ACTUALLY DELAYED BY FAILED PREREQUISITES 
        |-------------------------------------------------------------------------- 
        */ 
 
        $delayedSubjects = []; 
        $maxProjectedIndex = 
            $currentSlot['index'] !== null 
                ? (int) $currentSlot['index'] 
                : (($evaluationYear * 2) + 1); 
 
        foreach ($prospectusSubjects as $subject) { 
 
            $subjectId = (int) $subject->subject_id; 
 
            if (!isset($completionSlots[$subjectId])) { 
                continue; 
            } 
 
            $slot = $completionSlots[$subjectId]; 
 
            if ( 
                $slot['index'] !== null && 
                $slot['index'] > $maxProjectedIndex 
            ) { 
                $maxProjectedIndex = $slot['index']; 
            } 
 
            $normalSlot = $subjectNormalSlot($subject); 
 
            if ( 
                $normalSlot === null || 
                $slot['index'] === null 
            ) { 
                continue; 
            } 
 
            $requirements = 
                $requirementsBySubjectId[$subjectId] ?? []; 
 
            $blockedByFailedPrerequisite = false; 
 
            foreach ($requirements as $group) { 
 
                foreach ($group as $prerequisiteCode) { 
 
                    $prerequisite = 
                        $subjectByCode[$prerequisiteCode] ?? null; 
 
                    if (!$prerequisite) { 
                        continue; 
                    } 
 
                    $prerequisiteId = 
                        (int) $prerequisite->subject_id; 
 
                    if ( 
                        isset($failedSubjects[$prerequisiteId]) 
                    ) { 
                        $blockedByFailedPrerequisite = true; 
                        break 2; 
                    } 
                } 
            } 
 
            if ( 
                $blockedByFailedPrerequisite && 
                $slot['index'] > $normalSlot['index'] 
            ) { 
 
                $delayedSubjects[$subjectId] = [ 
                    'code' => trim((string) $subject->subject_code), 
                    'title' => $subject->subject_title, 
                    'delay' => max( 
                        1, 
                        (int) ceil( 
                            ($slot['index'] - $normalSlot['index']) / 2 
                        ) 
                    ), 
                ]; 
            } 
        } 
 
        /* 
        |-------------------------------------------------------------------------- 
        | ALSO REPORT THE FAILED SUBJECTS THEMSELVES WHEN THEIR RETAKE 
        | PUSHES THEM PAST THE CURRENT CURRICULUM SLOT 
        |-------------------------------------------------------------------------- 
        */ 
 
        foreach ($failedSubjects as $subjectId => $failedGrade) { 
 
            $subject = $subjectById[$subjectId] ?? null; 
 
            if (!$subject) { 
                continue; 
            } 
 
            $normalSlot = $subjectNormalSlot($subject); 
            $completionSlot = $completionSlots[$subjectId] ?? null; 
 
            if ( 
                $normalSlot === null || 
                $completionSlot === null || 
                $completionSlot['index'] === null 
            ) { 
                continue; 
            } 
 
            if ($completionSlot['index'] > $normalSlot['index']) { 
 
                $code = trim((string) $subject->subject_code); 
 
                $delayedSubjects[$subjectId] = [ 
                    'code' => $code, 
                    'title' => $subject->subject_title, 
                    'delay' => max( 
                        1, 
                        (int) ceil( 
                            ($completionSlot['index'] - $normalSlot['index']) / 2 
                        ) 
                    ), 
                ]; 
            } 
        } 
 
        /* 
        |-------------------------------------------------------------------------- 
        | PROJECTED RESIDENCY 
        |-------------------------------------------------------------------------- 
        | 
        | Convert the final projected semester into a school-year count from 
        | admission. A student at any point in school year N has spent N minus 
        | admissionYear completed school years, while a projected subject in 
        | that school year keeps the same residency-year count. 
        | 
        |-------------------------------------------------------------------------- 
        */ 
 
        $projectedYear = 
            intdiv( 
                $maxProjectedIndex, 
                2 
            ); 
 
        $projectedYears = max( 
            $baseProjectedYears, 
            $projectedYear - $admissionYear 
        ); 
 
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
        $result['projected_years'] = $projectedYears; 
 
        return $result; 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | NORMALIZE COURSE CODE 
    |-------------------------------------------------------------------------- 
    */ 
    private function normalizeCourseCode(mixed $value): string 
    { 
        return strtoupper( 
            preg_replace( 
                '/[^A-Z0-9]/i', 
                '', 
                trim((string) $value) 
            ) 
        ); 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | NORMALIZE YEAR LEVEL 
    |-------------------------------------------------------------------------- 
    | 
    | Supports values such as: 
    |   1 
    |   1st 
    |   1st Year 
    |   First Year 
    |-------------------------------------------------------------------------- 
    */ 
    private function normalizeYearLevel(mixed $value): ?int 
    { 
        $value = trim((string) $value); 
 
        if ($value === '') { 
            return null; 
        } 
 
        if (preg_match('/\b([1-9][0-9]*)\b/', $value, $matches)) { 
            return (int) $matches[1]; 
        } 
 
        $normalized = strtolower($value); 
 
        $map = [ 
            'first' => 1, 
            'first year' => 1, 
            'second' => 2, 
            'second year' => 2, 
            'third' => 3, 
            'third year' => 3, 
            'fourth' => 4, 
            'fourth year' => 4, 
        ]; 
 
        return $map[$normalized] ?? null; 
    } 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | NORMALIZE SEMESTER NUMBER 
    |-------------------------------------------------------------------------- 
    | 
    | Supports numeric and common textual semester values. 
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
    | 
    |   year     = ending school year 
    |   semester = 1 or 2 
    |   index    = year * 2 + semester offset 
    | 
    |-------------------------------------------------------------------------- 
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
 
        if (preg_match( 
            '/((?:19|20)\d{2})\s*[-\/]\s*((?:19|20)\d{2})/', 
            $schoolYear, 
            $matches 
        )) { 
            $result['year'] = (int) $matches[2]; 
        } elseif (preg_match( 
            '/((?:19|20)\d{2})/', 
            $schoolYear, 
            $matches 
        )) { 
            $result['year'] = (int) $matches[1]; 
        } 
 
        $result['semester'] = 
            $this->normalizeSemesterNumber( 
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
    */ 
 
    $evaluations = $student->statusHistory 
        ->sortByDesc(function ($history) { 
 
            return [ 
                (int) $history->semester_id, 
                (int) $history->status_id 
            ]; 
 
        }) 
        ->values(); 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | RECENT AND PREVIOUS EVALUATION 
    |-------------------------------------------------------------------------- 
    */ 
 
    $recentEvaluation = 
        $evaluations->first(); 
 
    $previousEvaluation = 
        $evaluations->skip(1)->first(); 
 
 
    /* 
    |-------------------------------------------------------------------------- 
    | CALCULATE PRIORITY ALERT 
    |-------------------------------------------------------------------------- 
    | 
    | Priority Alert exists ONLY when the student has a previous 
    | evaluation and the current failure percentage is equal to 
    | or greater than the previous failure percentage. 
    | 
    */ 
 
    $priorityAlert = false; 
 
 
    if ( 
        $recentEvaluation && 
        $previousEvaluation 
    ) { 
 
        $recentFailure = 
            (float) $recentEvaluation->failure_percentage; 
 
        $previousFailure = 
            (float) $previousEvaluation->failure_percentage; 
 
 
        if ( 
            $recentFailure >= 
            $previousFailure 
        ) { 
 
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
        } 
 
 
        $students = 
            Student::with('latestStatus') 
                ->orderBy('last_name') 
                ->orderBy('first_name') 
                ->get() 
                ->filter(function ($student) use ( 
                    $statusToFind 
                ) { 
 
                    return optional( 
                        $student->latestStatus 
                    )->academic_status === 
                        $statusToFind; 
 
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