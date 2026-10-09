@extends('layouts.app')

@section('content')

<style>

    /* =========================================================
       PAPER SIZE
       ========================================================= */

    @page {
        size: 8.5in 13in;
        margin: 0.5in;
    }

    /* =========================================================
       REPORT PAGE
       ========================================================= */

    .reports-page {
        max-width: 1000px;
        margin: 28px auto;
        padding: 0 20px 35px;
    }

    .reports-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 0;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid #e7ebf0;
        overflow: hidden;
    }

    /* =========================================================
       PAGE HEADER
       ========================================================= */

    .reports-header {
        position: relative;
        padding: 28px 32px 25px;
        margin-bottom: 0;
        background: linear-gradient(
            135deg,
            #0e8203 0%,
            #0e8203 55%,
            #0e8203 100%
        );
        color: #ffffff;
        overflow: hidden;
    }

    .reports-header::after {
        content: "";
        position: absolute;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        right: -65px;
        top: -105px;
    }

    .reports-header::before {
        content: "";
        position: absolute;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        right: 105px;
        bottom: -70px;
    }

    .page-title {
        position: relative;
        z-index: 1;
        margin: 0 0 7px 0;
        font-size: 25px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.3px;
    }

    .page-description {
        position: relative;
        z-index: 1;
        margin: 0;
        color: rgba(255, 255, 255, 0.86);
        font-size: 14px;
        line-height: 1.6;
        max-width: 700px;
    }

    /* =========================================================
       FORM CONTENT
       ========================================================= */

    .reports-form-content {
        padding: 30px 32px 32px;
    }

    /* =========================================================
       SECTION TITLE
       ========================================================= */

    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 17px;
        font-weight: 700;
        color: #202938;
        margin-bottom: 22px;
    }

    .section-title::before {
        content: "";
        width: 4px;
        height: 21px;
        background: #0e8203;
        border-radius: 4px;
    }

    /* =========================================================
       FORM
       ========================================================= */

    .form-group {
        margin-bottom: 23px;
    }

    .reports-card label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #344054;
    }

    .reports-card select {
        width: 100%;
        height: 46px;
        padding: 0 13px;
        border: 1px solid #d7dee8;
        border-radius: 9px;
        background: #ffffff;
        color: #344054;
        font-size: 14px;
        outline: none;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease,
            background 0.2s ease;
    }

    .reports-card select:hover {
        border-color: #aeb8c6;
        background: #fcfdff;
    }

    .reports-card select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.10);
        background: #ffffff;
    }

    .report-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
    }

    .help-text {
        margin-top: 7px;
        color: #7a8494;
        font-size: 12px;
        line-height: 1.5;
    }

    /* =========================================================
       REPORT TYPE
       ========================================================= */

    #report_type {
        background-color: #fbfcfe;
        font-weight: 500;
    }

    /* =========================================================
       SEMESTER RADIO BUTTONS
       ========================================================= */

    .semester-label {
        margin-bottom: 9px !important;
    }

    .semester-options {
        display: flex;
        gap: 11px;
        flex-wrap: wrap;
    }

    .semester-option {
        position: relative;
        flex: 1;
        min-width: 130px;
    }

    .semester-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .semester-option label {
        position: relative;
        display: flex !important;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 10px 15px;
        margin: 0 !important;
        border: 1px solid #d7dee8;
        border-radius: 9px;
        background: #ffffff;
        color: #475467;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .semester-option label::before {
        content: "";
        width: 8px;
        height: 8px;
        border: 1px solid #aab4c2;
        border-radius: 50%;
        margin-right: 8px;
        transition: all 0.2s ease;
    }

    .semester-option label:hover {
        border-color: #0d6efd;
        background: #f5f9ff;
        color: #0d6efd;
        transform: translateY(-1px);
    }

    .semester-option input[type="radio"]:checked + label {
        background: #eaf3ff;
        border-color: #0d6efd;
        color: #0d6efd;
        box-shadow: 0 3px 10px rgba(13, 110, 253, 0.10);
    }

    .semester-option input[type="radio"]:checked + label::before {
        border: 3px solid #0d6efd;
        background: #ffffff;
    }

    .semester-option input[type="radio"]:focus + label {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
    }

    /* =========================================================
       REPORT DESCRIPTION
       ========================================================= */

    .report-description {
        position: relative;
        padding: 15px 17px 15px 44px;
        background: #f7f9fc;
        border: 1px solid #e3e8ef;
        border-radius: 10px;
        margin-top: 5px;
        font-size: 13px;
        color: #596579;
        line-height: 1.6;
        min-height: 52px;
        display: flex;
        align-items: center;
    }

    .report-description::before {
        content: "i";
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #0d6efd;
        border-radius: 50%;
        color: #0d6efd;
        background: #eaf3ff;
        font-size: 12px;
        font-weight: 700;
    }

    .report-hidden {
        display: none;
    }

    /* =========================================================
       ALERTS
       ========================================================= */

    .report-alert {
        padding: 13px 16px;
        border-radius: 9px;
        margin-bottom: 22px;
        font-size: 14px;
        line-height: 1.5;
    }

    .report-alert-error {
        background: #fff3f3;
        color: #842029;
        border: 1px solid #f1caca;
    }

    .report-alert ul {
        margin: 0;
        padding-left: 20px;
    }

    .report-alert li {
        margin-bottom: 3px;
    }

    .report-alert li:last-child {
        margin-bottom: 0;
    }

    /* =========================================================
       BUTTONS
       ========================================================= */

    .button-row {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid #edf0f4;
    }

    .report-btn {
        min-height: 44px;
        border: none;
        border-radius: 9px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-generate {
        background: #0e8203;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.20);
    }

    .btn-generate:hover {
        background: #0b5ed7;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(13, 110, 253, 0.25);
    }

    .btn-generate:active {
        transform: translateY(0);
    }

    .btn-back {
        background: #eef1f5;
        color: #344054;
    }

    .btn-back:hover {
        background: #e2e7ed;
        color: #202938;
    }

    /* =========================================================
       SMALL VISUAL SEPARATION
       ========================================================= */

    #status-group {
        padding-top: 2px;
    }

    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 700px) {

        .reports-page {
            margin: 15px auto;
            padding: 0 12px 25px;
        }

        .reports-card {
            border-radius: 12px;
        }

        .reports-header {
            padding: 23px 20px 21px;
        }

        .page-title {
            font-size: 22px;
        }

        .page-description {
            font-size: 13px;
        }

        .reports-form-content {
            padding: 22px 18px 24px;
        }

        .section-title {
            font-size: 16px;
        }

        .report-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .semester-options {
            flex-direction: column;
            gap: 9px;
        }

        .semester-option {
            width: 100%;
        }

        .button-row {
            flex-direction: column;
            gap: 9px;
        }

        .report-btn {
            width: 100%;
        }

        .report-description {
            padding-right: 14px;
        }

    }

</style>

<div class="reports-page">

<div class="reports-card">

    {{-- =====================================================
         PAGE HEADER
         ===================================================== --}}

    <div class="reports-header">

        <h1 class="page-title">
            <i class="fa fa-bar-chart"></i>
            Academic Reports
        </h1>

        <p class="page-description">
            Generate academic reports using current or historical
            school year and semester evaluation records.
        </p>

    </div>


    <div class="reports-form-content">


        {{-- =================================================
             ERROR MESSAGE
             ================================================= --}}

        @if (session('error'))

            <div class="report-alert report-alert-error">

                <i class="fa fa-exclamation-circle"></i>

                {{ session('error') }}

            </div>

        @endif


        @if ($errors->any())

            <div class="report-alert report-alert-error">

                <ul>

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <div class="section-title">
            Select Report
        </div>


        <form
            method="GET"
            action="{{ route('reports.generate') }}"
            target="_blank"
        >


            {{-- =================================================
                 REPORT TYPE
                 ================================================= --}}

            <div class="form-group">

                <label for="report_type">
                    Report Type
                </label>

                <select
                    name="report_type"
                    id="report_type"
                    required
                >

                    <option value="">
                    </option>

                    <option value="status">
                        Students by Academic Status
                    </option>

                    <option value="no_improvement">
                        Students With No Academic Improvement
                    </option>

                </select>

            </div>


            {{-- =================================================
                 SCHOOL YEAR + SEMESTER
                 ================================================= --}}

            <div class="report-row">


                {{-- =================================================
                     SCHOOL YEAR
                     ================================================= --}}

                <div class="form-group">

                    <label for="school_year">
                        School Year
                    </label>

                    <select
                        name="school_year"
                        id="school_year"
                    >

                        <option value="">
                        </option>

                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | Arrange school years from past to present/future
                            |--------------------------------------------------------------------------
                            | Example:
                            | 2022-2023
                            | 2023-2024
                            | 2024-2025
                            | 2025-2026
                            | 2026-2027
                            |--------------------------------------------------------------------------
                            */

                            $sortedSchoolYears = collect($schoolYears)
                                ->sortBy(function ($year) {

                                    $parts = explode('-', (string) $year);

                                    return isset($parts[0])
                                        ? (int) $parts[0]
                                        : 0;

                                })
                                ->values();
                        @endphp

                        @foreach ($sortedSchoolYears as $schoolYear)

                            <option value="{{ $schoolYear }}">

                                {{ $schoolYear }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =================================================
                     SEMESTER
                     ================================================= --}}

                <div class="form-group">

                    <label class="semester-label">
                        Semester
                    </label>

                    <div class="semester-options">

                        <div class="semester-option">

                            <input
                                type="radio"
                                name="semester_choice"
                                id="semester_1"
                                value="1"
                            >

                            <label for="semester_1">
                                1st Semester
                            </label>

                        </div>


                        <div class="semester-option">

                            <input
                                type="radio"
                                name="semester_choice"
                                id="semester_2"
                                value="2"
                            >

                            <label for="semester_2">
                                2nd Semester
                            </label>

                        </div>

                    </div>


                    {{-- Actual semester_id submitted to Laravel --}}

                    <input
                        type="hidden"
                        name="semester_id"
                        id="semester_id"
                        value=""
                    >

                </div>

            </div>


            {{-- =================================================
                 ACADEMIC STATUS
                 ================================================= --}}

            <div
                class="form-group"
                id="status-group"
            >

                <label for="academic_status">
                    Academic Status
                </label>

                <select
                    name="academic_status"
                    id="academic_status"
                >

                    <option value="">
                        -- All Academic Statuses --
                    </option>

                    <option value="Regular">
                        Regular
                    </option>

                    <option value="Academic Warning">
                        Academic Warning
                    </option>

                    <option value="Probation">
                        Probation
                    </option>

                    <option value="Shifting Out">
                        Shifting Out
                    </option>

                </select>

                <div class="help-text">

                    This filter is used only for the
                    "Students by Academic Status" report.

                </div>

            </div>


            {{-- =================================================
                 DESCRIPTION
                 ================================================= --}}


            {{-- =================================================
                 BUTTONS
                 ================================================= --}}

            <div class="button-row">

                <button
                    type="submit"
                    class="report-btn btn-generate"
                >

                    <i class="fa fa-file-text-o"></i>

                    Generate Report

                </button>

            </div>


        </form>


    </div>

</div>


</div>

<script>

    const reportType =
        document.getElementById('report_type');

    const schoolYear =
        document.getElementById('school_year');

    const semesterId =
        document.getElementById('semester_id');

    const semesterRadios =
        document.querySelectorAll(
            'input[name="semester_choice"]'
        );

    const statusGroup =
        document.getElementById('status-group');

    const reportDescription =
        document.getElementById('report-description');


    /*
    |--------------------------------------------------------------------------
    | SEMESTER RADIO BUTTON → SEMESTER ID
    |--------------------------------------------------------------------------
    |
    | Clicking a semester once selects it.
    | Clicking the SAME selected semester again unselects it.
    |
    */

    semesterRadios.forEach(function (radio) {

        radio.addEventListener('click', function (event) {

            const selectedSchoolYear =
                schoolYear.value;

            /*
            |--------------------------------------------------------------------------
            | If there is no school year selected,
            | do not select a semester.
            |--------------------------------------------------------------------------
            */

            if (!selectedSchoolYear) {

                event.preventDefault();

                semesterId.value = '';

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | If this semester was already selected,
            | allow it to be unselected.
            |--------------------------------------------------------------------------
            */

            if (this.dataset.wasChecked === 'true') {

                this.checked = false;

                this.dataset.wasChecked = 'false';

                semesterId.value = '';

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Clear the "previously selected" state
            | of the other semester.
            |--------------------------------------------------------------------------
            */

            semesterRadios.forEach(function (otherRadio) {

                otherRadio.dataset.wasChecked = 'false';

            });


            /*
            |--------------------------------------------------------------------------
            | Mark this semester as the selected semester.
            |--------------------------------------------------------------------------
            */

            this.dataset.wasChecked = 'true';

        });


        /*
        |--------------------------------------------------------------------------
        | Change event → Find the matching semester_id
        |--------------------------------------------------------------------------
        */

        radio.addEventListener('change', function () {

            /*
            |--------------------------------------------------------------------------
            | If the radio was unselected,
            | clear semester_id.
            |--------------------------------------------------------------------------
            */

            if (!this.checked) {

                semesterId.value = '';

                return;

            }


            const selectedSemesterNumber =
                this.value;

            const selectedSchoolYear =
                schoolYear.value;


            /*
            |--------------------------------------------------------------------------
            | A school year is required to identify
            | the exact semester_id.
            |--------------------------------------------------------------------------
            */

            if (!selectedSchoolYear) {

                semesterId.value = '';

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Find the matching semester record.
            |--------------------------------------------------------------------------
            */

            const semesters = @json($semesters);


            const matchingSemester =
                semesters.find(function (semester) {

                    const semesterName =
                        String(
                            semester.semester_name
                        ).toLowerCase();


                    const isFirstSemester =
                        selectedSemesterNumber === '1' &&
                        (
                            semesterName.includes('1st') ||
                            semesterName.includes('first') ||
                            semesterName === '1' ||
                            semesterName.includes('1 semester')
                        );


                    const isSecondSemester =
                        selectedSemesterNumber === '2' &&
                        (
                            semesterName.includes('2nd') ||
                            semesterName.includes('second') ||
                            semesterName === '2' ||
                            semesterName.includes('2 semester')
                        );


                    return (
                        String(semester.school_year) ===
                            String(selectedSchoolYear)
                        &&
                        (
                            isFirstSemester ||
                            isSecondSemester
                        )
                    );

                });


            semesterId.value =
                matchingSemester
                    ? matchingSemester.semester_id
                    : '';

        });

    });


    /*
    |--------------------------------------------------------------------------
    | SCHOOL YEAR CHANGE
    |--------------------------------------------------------------------------
    */

    schoolYear.addEventListener('change', function () {

        /*
        |--------------------------------------------------------------------------
        | Clear the previously selected semester.
        |--------------------------------------------------------------------------
        */

        semesterId.value = '';


        semesterRadios.forEach(function (radio) {

            radio.checked = false;

            radio.dataset.wasChecked = 'false';

        });

    });


    /*
    |--------------------------------------------------------------------------
    | REPORT TYPE DESCRIPTION
    |--------------------------------------------------------------------------
    */

    function updateReportType() {

        const type =
            reportType.value;


        if (type === 'status') {

            statusGroup.classList.remove(
                'report-hidden'
            );

            if (reportDescription) {

                reportDescription.textContent =
                    'Generates a list of students based on the academic status recorded for the selected school year and semester.';

            }


        } else if (type === 'no_improvement') {

            statusGroup.classList.add(
                'report-hidden'
            );

            if (reportDescription) {

                reportDescription.textContent =
                    'Generates a list of students whose recorded failure percentage did not improve compared with their previous academic evaluation.';

            }


        } else {

            statusGroup.classList.add(
                'report-hidden'
            );

            if (reportDescription) {

                reportDescription.textContent =
                    'Select a report type to see what information will be included.';

            }

        }

    }


    reportType.addEventListener(
        'change',
        updateReportType
    );


    updateReportType();

</script>

@endsection