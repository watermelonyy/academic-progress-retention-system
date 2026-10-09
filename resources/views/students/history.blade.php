@extends('layouts.app')

@section('content')

<style>
    /* =========================================================
       PAGE
    ========================================================= */

    .history-container {
        max-width: 1150px;
        margin: 0 auto;
        padding-bottom: 30px;
    }

    /* =========================================================
       BACK BUTTON
    ========================================================= */

    .history-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
        color: #0e8203;
        background: #ffffff;
        border: 1px solid #dfe5ec;
        border-radius: 9px;
        padding: 9px 16px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }

    .history-back-btn:hover {
        background: #0e8203;
        color: #ffffff;
        border-color: #29fd0d;
        transform: translateX(-2px);
        box-shadow: 0 5px 14px rgba(37, 168, 5, 0.18);
    }

    /* =========================================================
       MAIN CARD
    ========================================================= */

    .history-card {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
    }

    /* =========================================================
       HEADER
    ========================================================= */

    .history-main-header {
        position: relative;
        background: linear-gradient(135deg, #048008 0%, #1dd217 55%, #0e8203 100%);
        color: #ffffff;
        padding: 24px 28px;
        overflow: hidden;
    }

    .history-main-header::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        right: -55px;
        top: -75px;
    }

    .history-main-header h4 {
        position: relative;
        z-index: 1;
        margin: 0;
        font-weight: 700;
        font-size: 21px;
        letter-spacing: 0.1px;
    }

    .history-main-header h4 i {
        margin-right: 8px;
    }

    /* =========================================================
       STUDENT INFORMATION
    ========================================================= */

    .student-information {
        position: relative;
        background: linear-gradient(135deg, #f8fbff 0%, #f4f7fb 100%);
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        padding: 20px 22px;
        margin-bottom: 25px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .student-information::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #0e8203;
    }

    .student-name {
        font-size: 21px;
        font-weight: 700;
        color: #172033;
        margin-bottom: 11px;
    }

    .student-detail {
        margin-bottom: 5px;
        color: #5d6878;
        font-size: 14px;
    }

    .student-detail strong {
        color: #303b4d;
    }

    /* =========================================================
       ACADEMIC PERIOD SELECTOR
    ========================================================= */

    .period-card {
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        padding: 21px;
        background: #ffffff;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.035);
    }

    .period-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 18px;
        font-weight: 700;
        color: #202938;
        margin-bottom: 19px;
    }

    .period-title i {
        color: #0e8203;
    }

    /* =========================================================
       FORM CONTROLS
    ========================================================= */

    .form-label {
        color: #344054;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .form-select {
        min-height: 45px;
        border-radius: 9px;
        border-color: #d7dee8;
        font-size: 14px;
        box-shadow: none;
        transition: all 0.2s ease;
    }

    .form-select:focus {
        border-color: #0e8203;
        box-shadow: 0 0 0 0.2rem rgba(14, 130, 3, 0.10);
    }

    /* =========================================================
       SEMESTER RADIO BUTTONS
    ========================================================= */

    .semester-radio-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 5px;
    }

    .semester-radio-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #d7dee8;
        border-radius: 9px;
        padding: 10px 15px;
        background: #ffffff;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        color: #475467;
        transition: all 0.2s ease;
    }

    .semester-radio-label:hover {
        border-color: #159809;
        background: #f4f8ff;
        color: #0e8203;
        transform: translateY(-1px);
    }

    .semester-radio-label input {
        margin: 0;
        cursor: pointer;
        accent-color: #0e8203;
    }

    .semester-radio-label.selected {
        border-color: #0e8203;
        background: #eef8f1;
        color: #0e8203;
        box-shadow: 0 3px 10px rgba(14, 130, 3, 0.10);
    }

    .semester-radio-label.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f5f6f8;
    }

    .semester-radio-label.disabled:hover {
        border-color: #d7dee8;
        background: #f5f6f8;
        color: #475467;
        transform: none;
    }

    /* =========================================================
       VIEW BUTTON
    ========================================================= */

    .view-evaluation-btn {
        margin-top: 20px;
    }

    .view-evaluation-btn .btn {
        border-radius: 9px;
        padding: 10px 18px;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(13, 242, 32, 0.16);
        transition: all 0.2s ease;
    }

    .view-evaluation-btn .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(13, 253, 121, 0.22);
    }

    /* =========================================================
       EVALUATION CARD
    ========================================================= */

    .evaluation-card {
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        overflow: hidden;
        margin-top: 25px;
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.045);
    }

    .evaluation-header {
        background: #202a38;
        color: #ffffff;
        padding: 16px 20px;
    }

    .evaluation-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 16px;
    }

    .evaluation-header h5 i {
        margin-right: 7px;
    }

    /* =========================================================
       ACADEMIC PERIOD BOXES
    ========================================================= */

    .information-box {
        position: relative;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        padding: 16px 17px;
        height: 100%;
        background: #fbfcfe;
        transition: all 0.2s ease;
    }

    .information-box:hover {
        border-color: #cfd9e6;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        transform: translateY(-1px);
    }

    .information-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #7a8494;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 7px;
    }

    .information-value {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
    }

    /* =========================================================
       STATUS
    ========================================================= */

    .status-badge {
        font-size: 13px;
        padding: 8px 13px;
        border-radius: 7px;
        font-weight: 600;
    }

    /* =========================================================
       FAILED SUBJECTS
    ========================================================= */

    .failed-subjects-section {
        margin-top: 27px;
    }

    .failed-subjects-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 17px;
        font-weight: 700;
        color: #273142;
        margin-bottom: 14px;
    }

    .failed-subjects-table {
        margin-bottom: 0;
        border-color: #e2e8f0;
        overflow: hidden;
    }

    .failed-subjects-table th {
        background: #f5f7fa;
        color: #344054;
        font-size: 13px;
        font-weight: 700;
        border-color: #e2e8f0;
        padding: 12px 14px;
    }

    .failed-subjects-table td {
        color: #475467;
        font-size: 14px;
        padding: 12px 14px;
        vertical-align: middle;
        border-color: #e2e8f0;
    }

    .failed-subjects-table tbody tr {
        transition: background 0.15s ease;
    }

    .failed-subjects-table tbody tr:hover {
        background: #fff8f8;
    }

    .failed-grade {
        font-weight: 700;
        color: #dc3545 !important;
    }

    .no-failed-subjects {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f5fbf7;
        border: 1px solid #d7efdf;
        border-radius: 9px;
        padding: 14px 16px;
        color: #466153;
        font-size: 14px;
    }

    /* =========================================================
       PRIORITY ALERT
    ========================================================= */

    .priority-section {
        margin-top: 27px;
    }

    .priority-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 17px;
        font-weight: 700;
        color: #273142;
        margin-bottom: 12px;
    }

    .priority-title i {
        color: #f59e0b;
    }

    .priority-section .alert {
        border-radius: 9px;
        padding: 13px 15px;
        font-size: 14px;
    }

    /* =========================================================
       NOTICE HISTORY
    ========================================================= */

    .notice-section {
        margin-top: 30px;
        padding-top: 24px;
        border-top: 1px solid #edf0f4;
    }

    .notice-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 17px;
        font-weight: 700;
        color: #273142;
        margin-bottom: 15px;
    }

    .notice-title i {
        color: #0e8203;
    }

    .notice-table {
        margin-bottom: 0;
        border-color: #e2e8f0;
    }

    .notice-table th {
        background: #f5f7fa;
        color: #344054;
        font-size: 13px;
        font-weight: 700;
        border-color: #e2e8f0;
        padding: 12px 14px;
    }

    .notice-table td {
        color: #475467;
        font-size: 14px;
        padding: 12px 14px;
        vertical-align: middle;
        border-color: #e2e8f0;
    }

    .notice-table tbody tr:hover {
        background: #f8fbff;
    }

    .notice-type-badge {
        font-size: 13px;
        padding: 7px 10px;
    }

    .notice-date {
        white-space: nowrap;
    }

    /* =========================================================
       NO NOTICE
    ========================================================= */

    .no-notices {
        background: #f8f9fb;
        border: 1px solid #e1e6ed;
        border-radius: 9px;
        padding: 15px;
        color: #667085;
    }

    /* =========================================================
       NO EVALUATION
    ========================================================= */

    .no-evaluation {
        margin-top: 25px;
    }

    /* =========================================================
       BOOTSTRAP ALERT ENHANCEMENT
    ========================================================= */

    .alert {
        border-radius: 9px;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 768px) {

        .history-container {
            width: 100%;
            padding-bottom: 20px;
        }

        .history-main-header {
            padding: 20px;
        }

        .history-main-header h4 {
            font-size: 18px;
        }

        .card-body.p-4 {
            padding: 18px !important;
        }

        .student-information {
            padding: 17px 18px;
        }

        .student-name {
            font-size: 18px;
        }

        .period-card {
            padding: 17px;
        }

        .semester-radio-container {
            flex-direction: column;
        }

        .semester-radio-label {
            width: 100%;
            justify-content: flex-start;
        }

        .evaluation-header {
            padding: 14px 17px;
        }

        .information-box {
            padding: 14px;
        }

        .failed-subjects-table,
        .notice-table {
            font-size: 13px;
        }

        .notice-section .btn {
            white-space: nowrap;
        }

    }

</style>

<div class="history-container">

<!-- =========================================================
     BACK BUTTON
========================================================= -->

<a
    href="{{ route('students.show', $student->student_id) }}"
    class="history-back-btn"
>

    <i class="fa fa-arrow-left"></i>

    Back

</a>


<!-- =========================================================
     MAIN CARD
========================================================= -->

<div class="card history-card">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="history-main-header">

        <h4>

            <i class="fa fa-history"></i>

            Student Academic History

        </h4>

    </div>


    <!-- =====================================================
         BODY
    ====================================================== -->

    <div class="card-body p-4">

        <!-- =================================================
             STUDENT INFORMATION
        ================================================== -->

        <div class="student-information">

            <div class="student-name">

                {{ strtoupper($student->last_name) }},
                {{ strtoupper($student->first_name) }}

                @if($student->middle_name)

                    {{ strtoupper($student->middle_name) }}

                @endif

            </div>


            <div class="student-detail">

                <strong>
                    Student No:
                </strong>

                {{ strtoupper($student->student_no) }}

            </div>


            <div class="student-detail">

                <strong>
                    Admission Year:
                </strong>

                {{ $student->admission_year }}

            </div>

        </div>


        <!-- =================================================
             SELECT ACADEMIC PERIOD
        ================================================== -->

        <div class="period-card">

            <div class="period-title">

                <i class="fa fa-calendar"></i>

                Select Academic Period

            </div>


            @if($evaluatedSemesters->count() > 0)

                @php
                    /*
                    |--------------------------------------------------------------------------
                    | BUILD ONLY TWO SEMESTER OPTIONS
                    |--------------------------------------------------------------------------
                    |
                    | Regardless of how many school years exist, the page will
                    | always display only:
                    |
                    | 1. 1ST SEMESTER
                    | 2. 2ND SEMESTER
                    |
                    | Each semester option contains the semester_id belonging
                    | to the currently selected school year.
                    |--------------------------------------------------------------------------
                    */

                    $semesterMap = [
                        '1' => [],
                        '2' => [],
                    ];

                    foreach ($evaluatedSemesters as $semesterRecord) {

                        $semesterName = strtolower(
                            trim((string) $semesterRecord->semester_name)
                        );

                        $semesterKey = null;

                        if (
                            preg_match(
                                '/^(1|1st|first)(\s*semester)?$/i',
                                $semesterName
                            )
                        ) {
                            $semesterKey = '1';
                        }

                        if (
                            preg_match(
                                '/^(2|2nd|second)(\s*semester)?$/i',
                                $semesterName
                            )
                        ) {
                            $semesterKey = '2';
                        }

                        if (
                            $semesterKey !== null &&
                            !empty($semesterRecord->school_year)
                        ) {
                            $semesterMap[$semesterKey][
                                (string) $semesterRecord->school_year
                            ] = (string) $semesterRecord->semester_id;
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | DETERMINE THE SCHOOL YEAR OF THE CURRENTLY SELECTED HISTORY
                    |--------------------------------------------------------------------------
                    */

                    $historySchoolYear = $selectedSchoolYear;

                    if (
                        empty($historySchoolYear) &&
                        $selectedHistory &&
                        $selectedHistory->semester
                    ) {
                        $historySchoolYear =
                            $selectedHistory->semester->school_year;
                    }
                @endphp


                <form
                    method="GET"
                    action="{{ route(
                        'students.history',
                        $student->student_id
                    ) }}"
                    id="historyForm"
                >

                    <div class="row g-4">

                        <!-- =====================================
                             SCHOOL YEAR
                        ====================================== -->

                        <div class="col-md-5">

                            <label
                                for="schoolYear"
                                class="form-label fw-bold"
                            >

                                School Year

                            </label>


                            <select
                                name="school_year"
                                id="schoolYear"
                                class="form-select"
                                required
                            >

                                <option
                                    value=""
                                    {{ empty($historySchoolYear) ? 'selected' : '' }}
                                >
                                </option>

                                @foreach(
                                    $evaluatedSemesters
                                        ->pluck('school_year')
                                        ->filter()
                                        ->unique()
                                        ->sortDesc()
                                        ->values()
                                    as $schoolYear
                                )

                                    <option
                                        value="{{ $schoolYear }}"
                                        {{
                                            (string) $historySchoolYear ===
                                            (string) $schoolYear
                                            ? 'selected'
                                            : ''
                                        }}
                                    >
                                        {{ $schoolYear }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <!-- =====================================
                             SEMESTER RADIO BUTTONS
                             ONLY TWO OPTIONS
                        ====================================== -->

                        <div class="col-md-7">

                            <label class="form-label fw-bold">

                                Semester

                            </label>


                            <div
                                class="semester-radio-container"
                                id="semesterOptions"
                            >

                                <!-- =================================
                                     1ST SEMESTER
                                ================================== -->

                                <label
                                    class="semester-radio-label"
                                    data-semester-key="1"
                                >

                                    <input
                                        type="radio"
                                        name="semester_id"
                                        value=""
                                        data-semester-key="1"
                                        {{ 
                                            $selectedHistory &&
                                            $selectedHistory->semester &&
                                            preg_match(
                                                '/^(1|1st|first)(\s*semester)?$/i',
                                                strtolower(trim($selectedHistory->semester->semester_name))
                                            )
                                            ? 'checked'
                                            : ''
                                        }}
                                    >

                                    1ST SEMESTER

                                </label>


                                <!-- =================================
                                     2ND SEMESTER
                                ================================== -->

                                <label
                                    class="semester-radio-label"
                                    data-semester-key="2"
                                >

                                    <input
                                        type="radio"
                                        name="semester_id"
                                        value=""
                                        data-semester-key="2"
                                        {{
                                            $selectedHistory &&
                                            $selectedHistory->semester &&
                                            preg_match(
                                                '/^(2|2nd|second)(\s*semester)?$/i',
                                                strtolower(trim($selectedHistory->semester->semester_name))
                                            )
                                            ? 'checked'
                                            : ''
                                        }}
                                    >

                                    2ND SEMESTER

                                </label>

                            </div>

                        </div>

                    </div>


                    <!-- =========================================
                         VIEW BUTTON
                    ========================================== -->

                    <div class="view-evaluation-btn">

                        <button
                            type="submit"
                            class="btn btn-success"
                            id="viewEvaluationButton"
                        >

                            <i class="fa fa-search"></i>

                            View Evaluation

                        </button>

                    </div>

                </form>


            @else

                <!-- =========================================
                     NO HISTORY
                ========================================== -->

                <div class="alert alert-info mb-0">

                    <i class="fa fa-info-circle"></i>

                    This student has no evaluated academic history yet.

                </div>

            @endif

        </div>


        <!-- =================================================
             SELECTED EVALUATION
        ================================================== -->

        @if($selectedHistory)

            <div class="evaluation-card">

                <!-- =============================================
                     EVALUATION HEADER
                ============================================== -->

                <div class="evaluation-header">

                    <h5>

                        Evaluation Result

                    </h5>

                </div>


                <div class="card-body p-4">

                    <!-- =========================================
                         ACADEMIC PERIOD
                    ========================================== -->

                    <div class="row g-3 mb-4">

                        <!-- SCHOOL YEAR -->

                        <div class="col-md-6">

                            <div class="information-box">

                                <span class="information-label">

                                    School Year

                                </span>


                                <span class="information-value">

                                    {{
                                        optional(
                                            $selectedHistory->semester
                                        )->school_year
                                    }}

                                </span>

                            </div>

                        </div>


                        <!-- SEMESTER -->

                        <div class="col-md-6">

                            <div class="information-box">

                                <span class="information-label">

                                    Semester

                                </span>


                                <span class="information-value">

                                    {{
                                        strtoupper(
                                            optional(
                                                $selectedHistory->semester
                                            )->semester_name
                                        )
                                    }}

                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- =========================================
                         STATUS + FAILURE PERCENTAGE
                    ========================================== -->

                    <div class="row g-3 mb-4">

                        <!-- STATUS -->

                        <div class="col-md-6">

                            <div class="information-box">

                                <span class="information-label">

                                    Academic Status

                                </span>


                                <div class="mt-2">

                                    @php

                                        $historyStatus =
                                            $selectedHistory
                                                ->academic_status;

                                        if (
                                            $historyStatus ===
                                            'For Shifting Out'
                                        ) {

                                            $historyStatus =
                                                'Shifting Out';

                                        }

                                    @endphp


                                    @if(
                                        $historyStatus ===
                                        'Regular'
                                    )

                                        <span
                                            class="badge bg-success status-badge"
                                        >

                                            Regular

                                        </span>


                                    @elseif(
                                        $historyStatus ===
                                        'Academic Warning'
                                    )

                                        <span
                                            class="badge bg-warning text-dark status-badge"
                                        >

                                            Academic Warning

                                        </span>


                                    @elseif(
                                        $historyStatus ===
                                        'Probation'
                                    )

                                        <span
                                            class="badge bg-danger status-badge"
                                        >

                                            Probation

                                        </span>


                                    @elseif(
                                        $historyStatus ===
                                        'Shifting Out'
                                    )

                                        <span
                                            class="badge bg-dark status-badge"
                                        >

                                            Shifting Out

                                        </span>


                                    @elseif(
                                        $historyStatus ===
                                        'Residency Risk'
                                    )

                                        <span
                                            class="badge bg-info status-badge"
                                        >

                                            Residency Risk

                                        </span>


                                    @else

                                        <span
                                            class="badge bg-secondary status-badge"
                                        >

                                            {{ $historyStatus }}

                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        <!-- FAILURE PERCENTAGE -->

                        <div class="col-md-6">

                            <div class="information-box">

                                <span class="information-label">

                                    Failure Percentage

                                </span>


                                <span class="information-value">

                                    {{
                                        number_format(
                                            $selectedHistory
                                                ->failure_percentage,
                                            2
                                        )
                                    }}%

                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- =========================================
                         FAILED SUBJECTS
                    ========================================== -->

                    @php

                        $failedSubjects = collect();


                        if ($selectedHistory->semester_id) {

                            $failedSubjects =
                                \App\Models\AcademicGrade::with(
                                    'subject'
                                )
                                ->where(
                                    'student_id',
                                    $student->student_id
                                )
                                ->where(
                                    'semester_id',
                                    $selectedHistory->semester_id
                                )
                                ->where(
                                    'grade',
                                    '<=',
                                    74
                                )
                                ->orderBy(
                                    'subject_id'
                                )
                                ->get();

                        }

                    @endphp


                    <div class="failed-subjects-section">

                        <div class="failed-subjects-title">

                            Subjects Failed in This Academic Period

                        </div>


                        @if($failedSubjects->count() > 0)

                            <div class="table-responsive">

                                <table
                                    class="table table-bordered table-hover failed-subjects-table"
                                >

                                    <thead>

                                        <tr>

                                            <th style="width: 25%;">
                                                Course Code
                                            </th>

                                            <th>
                                                Subject
                                            </th>

                                            <th
                                                style="width: 15%;"
                                                class="text-center"
                                            >
                                                Grade
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        @foreach(
                                            $failedSubjects
                                            as $failedGrade
                                        )

                                            <tr>

                                                <td class="fw-semibold">

                                                    {{
                                                        strtoupper(
                                                            optional(
                                                                $failedGrade->subject
                                                            )->subject_code
                                                        )
                                                    }}

                                                </td>


                                                <td>

                                                    {{
                                                        strtoupper(
                                                            optional(
                                                                $failedGrade->subject
                                                            )->subject_title
                                                        )
                                                    }}

                                                </td>


                                                <td
                                                    class="text-center failed-grade"
                                                >

                                                    {{
                                                        number_format(
                                                            $failedGrade->grade,
                                                            2
                                                        )
                                                    }}

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>


                        @else

                            <div class="no-failed-subjects">

                                <i class="fa fa-check-circle text-success"></i>

                                No failed subjects were recorded for this
                                academic period.

                            </div>

                        @endif

                    </div>


                    <!-- =========================================
                         PRIORITY ALERT
                    ========================================== -->

                    <div class="priority-section">

                        <div class="priority-title">

                            Priority Alert

                        </div>


                        @if($selectedHistory->priority_alert)

                            <div class="alert alert-danger mb-0">

                                <strong>YES</strong>

                                — This student received a priority alert
                                for this academic period.

                            </div>


                        @else

                            <div class="alert alert-success mb-0">

                                <strong>NO</strong>

                                — This student did not receive a priority
                                alert for this academic period.

                            </div>

                        @endif

                    </div>


                    <!-- =================================================
                         GENERATED NOTICE
                    ================================================== -->

                    @php

                        $latestNotice =
                            \App\Models\AcademicNotice::where(
                                'student_id',
                                $student->student_id
                            )
                            ->where(
                                'semester_id',
                                $selectedHistory->semester_id
                            )
                            ->orderBy(
                                'generated_at',
                                'desc'
                            )
                            ->first();

                    @endphp


                    @if($latestNotice)

                        <div class="notice-section">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <div class="notice-title mb-0">

                                    <i class="fa fa-file-text-o"></i>

                                    Generated Notice

                                </div>


                                <a
                                    href="{{ route(
                                        'notices.view',
                                        [
                                            'id' => $student->student_id,
                                            'notice' => $latestNotice->notice_id,
                                        ]
                                    ) }}"
                                    target="_blank"
                                    class="btn btn-success"
                                >

                                    <i class="fa fa-eye"></i>

                                    View Notice

                                </a>

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        @endif

    </div>

</div>


</div>

<script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const schoolYear =
                document.getElementById(
                    'schoolYear'
                );

            const semesterOptions =
                document.querySelectorAll(
                    '#semesterOptions .semester-radio-label'
                );

            const semesterInputs =
                document.querySelectorAll(
                    '#semesterOptions input[name="semester_id"]'
                );

            /*
            |--------------------------------------------------------------------------
            | SEMESTER ID MAP
            |--------------------------------------------------------------------------
            |
            | Structure:
            |
            | {
            |     "1": {
            |         "2025-2026": "1",
            |         "2026-2027": "3"
            |     },
            |     "2": {
            |         "2025-2026": "2",
            |         "2026-2027": "4"
            |     }
            | }
            |
            | Therefore, the interface always has only two radio buttons,
            | while the actual semester_id changes according to the selected
            | school year.
            |--------------------------------------------------------------------------
            */

            const semesterMap =
                @json($semesterMap);


            /*
            |--------------------------------------------------------------------------
            | CURRENTLY SELECTED SEMESTER
            |--------------------------------------------------------------------------
            */

            const selectedSemesterId =
                @json((string) ($selectedSemesterId ?? ''));


            /*
            |--------------------------------------------------------------------------
            | UPDATE SEMESTER OPTIONS
            |--------------------------------------------------------------------------
            */

            function updateSemesterOptions() {

                if (!schoolYear) {
                    return;
                }

                const selectedYear =
                    schoolYear.value;


                semesterInputs.forEach(
                    function (input) {

                        const semesterKey =
                            input.dataset.semesterKey;

                        const label =
                            input.closest(
                                '.semester-radio-label'
                            );

                        const semesterId =
                            semesterMap[semesterKey] &&
                            semesterMap[semesterKey][selectedYear]
                                ? semesterMap[semesterKey][selectedYear]
                                : '';


                        /*
                        |--------------------------------------------------------------------------
                        | SCHOOL YEAR HAS THIS SEMESTER
                        |--------------------------------------------------------------------------
                        */

                        if (semesterId) {

                            input.value =
                                semesterId;

                            input.disabled =
                                false;

                            if (label) {

                                label.style.display =
                                    'inline-flex';

                                label.classList.remove(
                                    'disabled'
                                );

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | KEEP THE CURRENTLY SELECTED HISTORY
                            |--------------------------------------------------------------------------
                            */

                            if (
                                selectedSemesterId &&
                                String(semesterId) ===
                                String(selectedSemesterId)
                            ) {

                                input.checked =
                                    true;

                                if (label) {

                                    label.classList.add(
                                        'selected'
                                    );

                                }

                            }

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SCHOOL YEAR DOES NOT HAVE THIS SEMESTER
                        |--------------------------------------------------------------------------
                        */

                        else {

                            input.value =
                                '';

                            input.checked =
                                false;

                            input.disabled =
                                true;

                            if (label) {

                                label.style.display =
                                    'inline-flex';

                                label.classList.remove(
                                    'selected'
                                );

                                label.classList.add(
                                    'disabled'
                                );

                            }

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | IF NO SEMESTER IS SELECTED FOR THE NEW SCHOOL YEAR,
                | REMOVE OLD SELECTION HIGHLIGHT
                |--------------------------------------------------------------------------
                */

                const checkedInput =
                    document.querySelector(
                        '#semesterOptions input[name="semester_id"]:checked'
                    );


                if (checkedInput) {

                    const checkedLabel =
                        checkedInput.closest(
                            '.semester-radio-label'
                        );

                    semesterOptions.forEach(
                        function (label) {

                            label.classList.remove(
                                'selected'
                            );

                        }
                    );


                    if (checkedLabel) {

                        checkedLabel.classList.add(
                            'selected'
                        );

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | RADIO BUTTON CHANGE
            |--------------------------------------------------------------------------
            */

            semesterInputs.forEach(
                function (input) {

                    input.addEventListener(
                        'change',
                        function () {

                            semesterOptions.forEach(
                                function (label) {

                                    label.classList.remove(
                                        'selected'
                                    );

                                }
                            );


                            const selectedLabel =
                                input.closest(
                                    '.semester-radio-label'
                                );


                            if (selectedLabel) {

                                selectedLabel.classList.add(
                                    'selected'
                                );

                            }

                        }
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SCHOOL YEAR CHANGE
            |--------------------------------------------------------------------------
            */

            if (schoolYear) {

                schoolYear.addEventListener(
                    'change',
                    function () {

                        /*
                        | When changing school year, remove the old
                        | semester selection first.
                        */

                        semesterInputs.forEach(
                            function (input) {

                                input.checked =
                                    false;

                                const label =
                                    input.closest(
                                        '.semester-radio-label'
                                    );

                                if (label) {

                                    label.classList.remove(
                                        'selected'
                                    );

                                }

                            }
                        );


                        updateSemesterOptions();

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | INITIALIZE
                |--------------------------------------------------------------------------
                */

                updateSemesterOptions();

            }

        }
    );

</script>

@endsection
