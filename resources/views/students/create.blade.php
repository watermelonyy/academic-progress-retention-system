@extends('layouts.app')

@section('content')

<style>

/* =========================================================
   GENERAL
========================================================= */

.create-container {
    max-width: 1100px;
    margin: 0 auto;
}

.create-card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 22px rgba(0, 0, 0, 0.08);
    position: relative;
}


/* =========================================================
   HEADER
========================================================= */

.create-header {
    background: #198754;
    color: #ffffff;
    padding: 23px 28px;
    position: relative;
}

.create-header h3 {
    margin: 0;
    font-weight: 700;
    letter-spacing: 0.2px;
}

.create-header p {
    margin: 5px 0 0;
    opacity: 0.9;
}


/* =========================================================
   CLOSE BUTTON
========================================================= */

.close-create-btn {
    position: absolute;
    top: 15px;
    right: 18px;

    width: 38px;
    height: 38px;

    border: none;
    border-radius: 50%;

    background: rgba(255, 255, 255, 0.18);
    color: white;

    font-size: 24px;
    font-weight: bold;
    line-height: 1;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;
    transition: 0.2s;
}

.close-create-btn:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: scale(1.05);
}


/* =========================================================
   STEP NAVIGATION
========================================================= */

.step-navigation {
    display: flex;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
}

.step-button {
    flex: 1;
    padding: 16px 20px;

    border: none;
    background: transparent;

    font-weight: 700;
    font-size: 15px;

    color: #6c757d;

    cursor: pointer;
    transition: 0.2s;

    border-bottom: 4px solid transparent;
}

.step-button:hover {
    background: #f1f3f5;
}

.step-button.active {
    color: #198754;
    background: #ffffff;
    border-bottom-color: #198754;
}

.step-button.completed {
    color: #198754;
}

.step-button:disabled {
    cursor: not-allowed;
    opacity: 0.55;
    background: #f8f9fa;
}


/* =========================================================
   FORM BODY
========================================================= */

.create-body {
    padding: 30px;
}

.step-content {
    display: none;
}

.step-content.active {
    display: block;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 22px;
    color: #212529;
}


/* =========================================================
   FORM CONTROLS
========================================================= */

.form-label {
    font-weight: 600;
    color: #343a40;
    margin-bottom: 8px;
}

.required {
    color: #dc3545;
}

.form-control,
.form-select {
    min-height: 44px;
    border-color: #d8dee4;
    border-radius: 8px;
}

.form-control:focus,
.form-select:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.18rem rgba(25, 135, 84, 0.13);
}


/* =========================================================
   UPPERCASE
========================================================= */

.uppercase-input {
    text-transform: uppercase;
}


/* =========================================================
   SCHOOL YEAR / SEMESTER
========================================================= */

.semester-container {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #dee2e6;
}

.academic-period-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
    gap: 20px;
    align-items: end;
}

.total-subjects-field {
    margin-bottom: 0 !important;
}

.semester-title {
    font-weight: 700;
    margin-bottom: 15px;
}

.semester-options {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.semester-option {
    display: flex;
    align-items: center;
    min-height: 48px;
    border: 1px solid #ced4da;
    border-radius: 9px;
    padding: 11px 18px;
    background: white;
    cursor: pointer;
    transition: 0.2s;
    font-weight: 600;
}

.semester-option:hover {
    border-color: #198754;
    background: #f7fcf9;
}

.semester-option input {
    margin-right: 9px;
    accent-color: #198754;
} 

.semester-option:has(input:checked) {
    border-color: #198754;
    background: #eaf7ef;
    color: #198754;
    box-shadow: 0 0 0 1px rgba(25, 135, 84, 0.08);
}


/* =========================================================
   GRADES SECTION
========================================================= */

.grades-section {
    margin-top: 28px;
    padding: 22px;
    border: 1px solid #e3e7eb;
    border-radius: 12px;
    background: #ffffff;
}

.grades-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    gap: 15px;
}

.grades-header h5 {
    margin: 0;
    font-weight: 700;
}

.grade-column-header {
    display: grid;
    grid-template-columns: 55px minmax(0, 1fr) 180px 55px;
    gap: 12px;
    padding: 0 5px 8px;
    color: #6c757d;
    font-weight: 700;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.2px;
}

.subject-row {
    display: grid;
    grid-template-columns: 55px minmax(0, 1fr) 180px 55px;
    gap: 12px;
    align-items: start;
    margin-bottom: 12px;
}

.subject-number {
    width: 38px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #f1f3f5;
    border-radius: 50%;

    font-weight: 700;
    color: #495057;
}

.remove-subject-btn {
    width: 38px;
    height: 38px;

    border: none;
    border-radius: 7px;

    background: #dc3545;
    color: white;

    font-size: 18px;
    font-weight: bold;

    cursor: pointer;
    transition: 0.2s;
}

.remove-subject-btn:hover {
    background: #bb2d3b;
}

.add-subject-container {
    margin-top: 15px;
}

.add-subject-btn {
    border: 1px solid #b7dfc8;
    background: #eaf7ef;
    color: #198754;

    padding: 10px 20px;
    border-radius: 8px;

    font-weight: 700;
    cursor: pointer;

    transition: 0.2s;
}

.add-subject-btn:hover {
    background: #198754;
    color: white;
}


/* =========================================================
   COURSE CODE WARNING
========================================================= */

.course-code-warning {
    display: none;
    margin-top: 6px;
    color: #dc3545;
    font-size: 13px;
    font-weight: 600;
}

.course-code-warning.show {
    display: block;
}

.subject-code.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.12);
}


/* =========================================================
   KEYBOARD FOCUS
========================================================= */

.keyboard-field.keyboard-focus {
    border-color: #198754 !important;
    box-shadow:
        0 0 0 0.18rem rgba(25, 135, 84, 0.14) !important;
    background: #fbfffc;
}


/* =========================================================
   BUTTONS
========================================================= */

.form-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #dee2e6;
}

.right-buttons {
    display: flex;
    gap: 10px;
}

.btn {
    border-radius: 8px;
}

.btn-success {
    background: #198754;
    border-color: #198754;
}

.btn-success:hover {
    background: #157347;
    border-color: #146c43;
}


/* =========================================================
   HIDE NUMBER INPUT SPINNERS
========================================================= */

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .create-body {
        padding: 20px;
    }

    .academic-period-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .total-subjects-field {
        margin-top: 16px !important;
    }

    .semester-options {
        grid-template-columns: 1fr;
    }

    .grade-column-header {
        display: none;
    }

    .grades-section {
        padding: 16px;
    }

    .subject-row {
        grid-template-columns: 45px minmax(0, 1fr) 110px 45px;
        gap: 8px;
    }

    .step-button {
        font-size: 13px;
        padding: 13px 8px;
    }

}

</style>


<div class="create-container">

<div class="create-card">

    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="create-header">

        <h3>
            <i class="fa fa-user-plus"></i>
            ADD NEW STUDENT
        </h3>

        {{-- CLOSE / CANCEL BUTTON --}}
        <button
            type="button"
            class="close-create-btn"
            onclick="closeCreatePage()"
            title="Close"
            aria-label="Close"
        >
            &times;
        </button>

    </div>


    {{-- =====================================================
         STEP NAVIGATION
    ====================================================== --}}

    <div class="step-navigation">

        <button
            type="button"
            id="step1Button"
            class="step-button active"
            onclick="goToStep(1)"
        >
            <i class="fa fa-user"></i>
            1. STUDENT INFORMATION
        </button>

        <button
            type="button"
            id="step2Button"
            class="step-button"
            onclick="goToStep(2)"
        >
            <i class="fa fa-book"></i>
            2. ACADEMIC GRADES
        </button>

    </div>


    {{-- =====================================================
         BODY
    ====================================================== --}}

    <div class="create-body">

        @if($errors->any())

            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">

                <strong>
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    PLEASE CORRECT THE FOLLOWING ERRORS:
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        @endif


        <form
            action="{{ route('students.store') }}"
            method="POST"
            id="studentForm"
        >

            @csrf


            {{-- =================================================
                 STEP 1 - STUDENT INFORMATION
            ================================================== --}}

            <div
                id="step1"
                class="step-content active"
            >

                <div class="section-title">
                    <i class="fa fa-user"></i>
                    STUDENT INFORMATION
                </div>


                <div class="row g-3">

                    {{-- STUDENT NUMBER --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            STUDENT NUMBER
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="student_no"
                            id="student_no"
                            class="form-control uppercase-input keyboard-field"
                            value="{{ old('student_no') }}"
                            required
                            autocomplete="off"
                            inputmode="numeric"
                            pattern="[0-9]{1,10}"
                            maxlength="10"
                            data-key-row="student-info"
                            data-key-col="1"
                        >

                    </div>


                    {{-- FIRST NAME --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            FIRST NAME
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="first_name"
                            id="first_name"
                            class="form-control uppercase-input keyboard-field"
                            value="{{ old('first_name') }}"
                            required
                            autocomplete="off"
                            data-key-row="student-info"
                            data-key-col="2"
                        >

                    </div>


                    {{-- MIDDLE NAME --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            MIDDLE NAME
                        </label>

                        <input
                            type="text"
                            name="middle_name"
                            id="middle_name"
                            class="form-control uppercase-input keyboard-field"
                            value="{{ old('middle_name') }}"
                            autocomplete="off"
                            data-key-row="student-info"
                            data-key-col="3"
                        >

                    </div>


                    {{-- LAST NAME --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            LAST NAME
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="last_name"
                            id="last_name"
                            class="form-control uppercase-input keyboard-field"
                            value="{{ old('last_name') }}"
                            required
                            autocomplete="off"
                            data-key-row="student-info"
                            data-key-col="4"
                        >

                    </div>


                    {{-- ADMISSION YEAR --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            ADMISSION YEAR
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="admission_year"
                            id="admission_year"
                            class="form-control keyboard-field"
                            value="{{ old('admission_year') }}"
                            required
                            autocomplete="off"
                            inputmode="numeric"
                            pattern="[0-9]{4}"
                            maxlength="4"
                            data-key-row="student-info"
                            data-key-col="5"
                        >

                    </div>


                    {{-- CURRENT YEAR LEVEL --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            CURRENT YEAR LEVEL
                            <span class="required">*</span>
                        </label>

                        <select
                            name="current_year_level"
                            id="current_year_level"
                            class="form-select keyboard-field"
                            required
                            data-key-row="student-info"
                            data-key-col="6"
                        >

                            <option value="">
                                SELECT YEAR LEVEL
                            </option>

                            <option
                                value="1"
                                {{ old('current_year_level') == '1' ? 'selected' : '' }}
                            >
                                1ST YEAR
                            </option>

                            <option
                                value="2"
                                {{ old('current_year_level') == '2' ? 'selected' : '' }}
                            >
                                2ND YEAR
                            </option>

                            <option
                                value="3"
                                {{ old('current_year_level') == '3' ? 'selected' : '' }}
                            >
                                3RD YEAR
                            </option>

                            <option
                                value="4"
                                {{ old('current_year_level') == '4' ? 'selected' : '' }}
                            >
                                4TH YEAR
                            </option>

                        </select>

                    </div>

                </div>


                {{-- STEP 1 BUTTON --}}
                <div class="form-buttons">

                    <div></div>

                    <button
                        type="button"
                        class="btn btn-primary"
                        onclick="goToStep(2)"
                    >
                        NEXT
                    </button>

                </div>

            </div>


            {{-- =================================================
                 STEP 2 - ACADEMIC GRADES
            ================================================== --}}

            <div
                id="step2"
                class="step-content"
            >

                <div class="section-title">
                    <i class="fa fa-book"></i>
                    ACADEMIC GRADES
                </div>


                <div class="academic-period-row">

                    {{-- SCHOOL YEAR --}}
                    <div class="mb-0">

                        <label class="form-label">
                            SCHOOL YEAR
                            <span class="required">*</span>
                        </label>

                        @php

                            /*
                             * IMPORTANT:
                             * School years are arranged from PAST TO FUTURE.
                             *
                             * Example:
                             * 2021-2022
                             * 2022-2023
                             * 2023-2024
                             * 2024-2025
                             * 2025-2026
                             * 2026-2027
                             *
                             * This replaces the previous "nearest to current year"
                             * sorting.
                             */
                            $databaseSchoolYears = $semesters
                                ->pluck('school_year')
                                ->filter()
                                ->unique()
                                ->sortBy(function ($schoolYear) {

                                    return (int) substr(
                                        trim($schoolYear),
                                        0,
                                        4
                                    );

                                })
                                ->values();

                        @endphp

                        <select
                            name="school_year"
                            id="schoolYear"
                            class="form-select keyboard-field"
                            required
                            data-key-row="academic-period"
                            data-key-col="1"
                        >

                            <option value="">
                                SELECT SCHOOL YEAR
                            </option>

                            @foreach($databaseSchoolYears as $schoolYear)

                                <option
                                    value="{{ $schoolYear }}"
                                    {{ old('school_year') == $schoolYear ? 'selected' : '' }}
                                >
                                    {{ $schoolYear }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- TOTAL SUBJECTS --}}
                    <div class="total-subjects-field mb-0">

                        <label class="form-label">
                            TOTAL SUBJECTS
                            <span class="required">*</span>
                        </label>

                        <input
                            type="number"
                            name="total_subjects"
                            id="totalSubjects"
                            class="form-control keyboard-field"
                            value="{{ old('total_subjects') }}"
                            min="1"
                            max="100"
                            step="1"
                            required
                            autocomplete="off"
                            placeholder="ENTER TOTAL NUMBER OF SUBJECTS"
                            data-key-row="academic-period"
                            data-key-col="3"
                        >

                    </div>

                </div>


                {{-- SEMESTER --}}
                <div
                    id="semesterContainer"
                    class="semester-container"
                >

                    <div class="semester-title">
                        SELECT SEMESTER
                        <span class="required">*</span>
                    </div>

                    <div
                        id="semesterOptions"
                        class="semester-options"
                    >

                    </div>

                    <input
                        type="hidden"
                        name="semester_id"
                        id="semesterId"
                        value="{{ old('semester_id') }}"
                    >

                </div>


                {{-- SUBJECTS AND GRADES --}}

                <div class="grades-section">

                    <div class="grades-header">

                        <h5>
                            <i class="fa fa-list"></i>
                            COURSE CODES AND GRADES
                        </h5>

                    </div>


                    <div class="grade-column-header">

                        <div></div>

                        <div>COURSE CODE</div>

                        <div>FAILED GRADE</div>

                        <div>ACTION</div>

                    </div>


                    <div id="subjectContainer">

                        @php

                            $oldSubjectCodes =
                                old(
                                    'subject_code',
                                    ['']
                                );

                            $oldGrades =
                                old(
                                    'grade',
                                    ['']
                                );

                            $subjectRowCount =
                                max(
                                    count($oldSubjectCodes),
                                    count($oldGrades)
                                );

                            if ($subjectRowCount < 1) {
                                $subjectRowCount = 1;
                            }

                        @endphp


                        @for($i = 0; $i < $subjectRowCount; $i++)

                            <div class="subject-row">

                                <div class="subject-number">
                                    {{ $i + 1 }}
                                </div>

                                <div>

                                    <input
                                        type="text"
                                        name="subject_code[]"
                                        class="form-control subject-code uppercase-input keyboard-field {{ $errors->has('subject_code.' . $i) ? 'is-invalid' : '' }}"
                                        placeholder=""
                                        value="{{ $oldSubjectCodes[$i] ?? '' }}"
                                        autocomplete="off"
                                        data-key-type="subject-code"
                                    >

                                    <div
                                        class="course-code-warning {{ $errors->has('subject_code.' . $i) ? 'show' : '' }}"
                                    >

                                        <i class="fa fa-exclamation-triangle"></i>

                                        @if($errors->has('subject_code.' . $i))

                                            {{ $errors->first('subject_code.' . $i) }}

                                        @else

                                            INVALID COURSE CODE. PLEASE CHECK THE COURSE CODE.

                                        @endif

                                    </div>

                                </div>

                                <div>

                                    <input
                                        type="number"
                                        name="grade[]"
                                        class="form-control grade-input keyboard-field"
                                        placeholder=""
                                        step="0.01"
                                        min="0"
                                        max="74"
                                        value="{{ $oldGrades[$i] ?? '' }}"
                                        data-key-type="grade"
                                    >

                                </div>

                                <div>

                                    @if($i > 0)

                                        <button
                                            type="button"
                                            class="remove-subject-btn"
                                            title="Remove Subject"
                                            aria-label="Remove Subject"
                                        >
                                            &times;
                                        </button>

                                    @endif

                                </div>

                            </div>

                        @endfor

                    </div>


                    <div class="add-subject-container">

                        <button
                            type="button"
                            id="addSubject"
                            class="add-subject-btn"
                        >
                            <i class="fa fa-plus"></i>
                            ADD SUBJECT
                        </button>

                    </div>

                </div>


                {{-- STEP 2 BUTTONS --}}

                <div class="form-buttons">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="goToStep(1)"
                    >
                        Previous
                    </button>


                    <div class="right-buttons">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            <i class="fa fa-save"></i>
                            SAVE STUDENT
                        </button>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {


    /* =========================================================
       AUTOMATIC UPPERCASE
    ========================================================= */

    function enableUppercase() {

        const uppercaseFields =
            document.querySelectorAll(
                '.uppercase-input'
            );

        uppercaseFields.forEach(function (field) {

            field.addEventListener(
                'input',
                function () {

                    this.value =
                        this.value.toUpperCase();

                }
            );

        });

    }

    enableUppercase();


    /* =========================================================
       STUDENT NUMBER - NUMBERS ONLY - MAXIMUM 10 DIGITS
    ========================================================= */

    const studentNumber =
        document.getElementById('student_no');

    if (studentNumber) {

        studentNumber.addEventListener(
            'input',
            function () {

                this.value =
                    this.value
                        .replace(/\D/g, '')
                        .slice(0, 10);

            }
        );

    }


    /* =========================================================
       ADMISSION YEAR - NUMBERS ONLY - EXACTLY 4 DIGITS
    ========================================================= */

    const admissionYear =
        document.getElementById('admission_year');

    if (admissionYear) {

        admissionYear.addEventListener(
            'input',
            function () {

                this.value =
                    this.value
                        .replace(/\D/g, '')
                        .slice(0, 4);

            }
        );

    }


    /* =========================================================
       FIRST / MIDDLE / LAST NAME - NO NUMBERS
    ========================================================= */

    const nameFields = [
        'first_name',
        'middle_name',
        'last_name'
    ];

    nameFields.forEach(function (fieldId) {

        const field =
            document.getElementById(fieldId);

        if (!field) {
            return;
        }

        field.addEventListener(
            'input',
            function () {

                this.value =
                    this.value
                        .replace(/[0-9]/g, '')
                        .toUpperCase();

            }
        );

    });


    /* =========================================================
       SCHOOL YEAR / SEMESTER
    ========================================================= */

    const schoolYearSelect =
        document.getElementById('schoolYear');

    const semesterOptions =
        document.getElementById('semesterOptions');

    const semesterId =
        document.getElementById('semesterId');


    function loadSemesters(selectedSemester = '') {

        const selectedYear =
            schoolYearSelect.value;

        semesterOptions.innerHTML = '';
        semesterId.value = '';

        if (!selectedYear) {
            return;
        }


        @foreach($semesters as $semester)

            if (
                selectedYear ===
                "{{ $semester->school_year }}"
            ) {

                const label =
                    document.createElement('label');

                label.className =
                    'semester-option';

                label.innerHTML = `
                    <input
                        type="radio"
                        name="semester_choice"
                        value="{{ $semester->semester_id }}"
                        class="semester-radio keyboard-field"
                        data-key-row="semester"
                        data-key-col="1"
                    >
                    {{ strtoupper($semester->semester_name) }}
                `;

                semesterOptions.appendChild(
                    label
                );

            }

        @endforeach


        document.querySelectorAll(
            '.semester-radio'
        ).forEach(function (radio) {

            radio.addEventListener(
                'change',
                function () {

                    semesterId.value =
                        this.value;

                }
            );

        });


        if (selectedSemester) {

            const radio =
                document.querySelector(
                    '.semester-radio[value="' +
                    selectedSemester +
                    '"]'
                );

            if (radio) {

                radio.checked = true;

                semesterId.value =
                    selectedSemester;

            }

        }

    }


    schoolYearSelect.addEventListener(
        'change',
        function () {

            loadSemesters();

        }
    );


    @if(old('school_year'))

        loadSemesters(
            "{{ old('semester_id') }}"
        );

    @endif


    /* =========================================================
       SUBJECTS
    ========================================================= */

    const addSubjectButton =
        document.getElementById('addSubject');

    const subjectContainer =
        document.getElementById('subjectContainer');


    function updateSubjectNumbers() {

        const rows =
            subjectContainer.querySelectorAll(
                '.subject-row'
            );

        rows.forEach(function (row, index) {

            const number =
                row.querySelector(
                    '.subject-number'
                );

            if (number) {

                number.textContent =
                    index + 1;

            }

        });

    }


    function attachUppercaseToField(field) {

        if (!field) {
            return;
        }

        field.addEventListener(
            'input',
            function () {

                this.value =
                    this.value.toUpperCase();

                const parent =
                    this.parentElement;

                const warning =
                    parent.querySelector(
                        '.course-code-warning'
                    );

                if (warning) {

                    warning.classList.remove(
                        'show'
                    );

                }

                this.classList.remove(
                    'is-invalid'
                );

            }
        );

    }


    function attachRemoveButton(row) {

        const removeButton =
            row.querySelector(
                '.remove-subject-btn'
            );

        if (!removeButton) {
            return;
        }


        removeButton.addEventListener(
            'click',
            function () {

                row.remove();

                updateSubjectNumbers();

            }
        );

    }


    subjectContainer
        .querySelectorAll('.subject-row')
        .forEach(function (row) {

            const subjectCode =
                row.querySelector(
                    '.subject-code'
                );

            attachUppercaseToField(
                subjectCode
            );

            attachRemoveButton(
                row
            );

        });


    /* =========================================================
       ADD SUBJECT
    ========================================================= */

    addSubjectButton.addEventListener(
        'click',
        function () {

            const rows =
                subjectContainer.querySelectorAll(
                    '.subject-row'
                );

            const rowNumber =
                rows.length + 1;


            const newRow =
                document.createElement('div');

            newRow.className =
                'subject-row';


            newRow.innerHTML = `

                <div class="subject-number">
                    ${rowNumber}
                </div>

                <div>

                    <input
                        type="text"
                        name="subject_code[]"
                        class="form-control subject-code uppercase-input keyboard-field"
                        placeholder="ENTER COURSE CODE"
                        autocomplete="off"
                        data-key-type="subject-code"
                    >

                    <div class="course-code-warning">

                        <i class="fa fa-exclamation-triangle"></i>

                        INVALID COURSE CODE. PLEASE CHECK THE COURSE CODE.

                    </div>

                </div>

                <div>

                    <input
                        type="number"
                        name="grade[]"
                        class="form-control grade-input keyboard-field"
                        placeholder="GRADE"
                        step="0.01"
                        min="0"
                        max="74"
                        data-key-type="grade"
                    >

                </div>

                <div>

                    <button
                        type="button"
                        class="remove-subject-btn"
                        title="Remove Subject"
                        aria-label="Remove Subject"
                    >
                        &times;
                    </button>

                </div>

            `;


            subjectContainer.appendChild(
                newRow
            );


            const newSubjectCode =
                newRow.querySelector(
                    '.subject-code'
                );


            attachUppercaseToField(
                newSubjectCode
            );


            attachRemoveButton(
                newRow
            );


            newSubjectCode.focus();

            newSubjectCode.classList.add(
                'keyboard-focus'
            );

        }
    );


    /* =========================================================
       FORM SUBMISSION VALIDATION
    ========================================================= */

    const form =
        document.getElementById('studentForm');


    form.addEventListener(
        'submit',
        function (event) {

            document.querySelectorAll(
                '.uppercase-input'
            ).forEach(function (field) {

                field.value =
                    field.value.toUpperCase();

            });


            /* =================================================
               STUDENT NUMBER VALIDATION
            ================================================= */

            const studentNo =
                document.getElementById(
                    'student_no'
                );

            const studentNoValue =
                studentNo.value.trim();


            if (!studentNoValue) {

                event.preventDefault();

                alert(
                    'PLEASE ENTER THE STUDENT NUMBER.'
                );

                studentNo.focus();

                return;

            }


            if (
                !/^\d{1,10}$/.test(
                    studentNoValue
                )
            ) {

                event.preventDefault();

                alert(
                    'STUDENT NUMBER MUST CONTAIN NUMBERS ONLY AND MUST NOT EXCEED 10 DIGITS.'
                );

                studentNo.focus();

                return;

            }


            /* =================================================
               NAME VALIDATION
            ================================================= */

            const firstName =
                document.getElementById('first_name');

            const middleName =
                document.getElementById('middle_name');

            const lastName =
                document.getElementById('last_name');


            if (/\d/.test(firstName.value)) {

                event.preventDefault();

                alert(
                    'FIRST NAME MUST NOT CONTAIN NUMBERS.'
                );

                firstName.focus();

                return;

            }


            if (
                middleName.value &&
                /\d/.test(middleName.value)
            ) {

                event.preventDefault();

                alert(
                    'MIDDLE NAME MUST NOT CONTAIN NUMBERS.'
                );

                middleName.focus();

                return;

            }


            if (/\d/.test(lastName.value)) {

                event.preventDefault();

                alert(
                    'LAST NAME MUST NOT CONTAIN NUMBERS.'
                );

                lastName.focus();

                return;

            }


            /* =================================================
               ADMISSION YEAR VALIDATION
            ================================================= */

            const admissionYear =
                document.getElementById(
                    'admission_year'
                );


            if (
                !/^\d{4}$/.test(
                    admissionYear.value.trim()
                )
            ) {

                event.preventDefault();

                alert(
                    'ADMISSION YEAR MUST CONTAIN EXACTLY 4 NUMBERS.'
                );

                admissionYear.focus();

                return;

            }


            /* =================================================
               STEP 1 VALIDATION
            ================================================= */

            if (!validateStudentInformation()) {

                event.preventDefault();

                goToStep(1);

                return;

            }


            /* =================================================
               SEMESTER VALIDATION
            ================================================= */

            if (!semesterId.value) {

                event.preventDefault();

                alert(
                    'PLEASE SELECT A SCHOOL YEAR AND SEMESTER.'
                );

                goToStep(2);

                return;

            }


            /* =================================================
               TOTAL SUBJECTS VALIDATION
            ================================================= */

            const totalSubjects =
                document.getElementById(
                    'totalSubjects'
                );

            const totalSubjectsValue =
                totalSubjects.value.trim();


            if (
                !/^\d+$/.test(
                    totalSubjectsValue
                ) ||
                parseInt(
                    totalSubjectsValue,
                    10
                ) < 1 ||
                parseInt(
                    totalSubjectsValue,
                    10
                ) > 100
            ) {

                event.preventDefault();

                alert(
                    'TOTAL NUMBER OF SUBJECTS MUST BE A WHOLE NUMBER FROM 1 TO 100.'
                );

                totalSubjects.focus();

                return;

            }


            /* =================================================
               SUBJECT / GRADE PAIR VALIDATION
            ================================================= */

            const subjectCodes =
                document.querySelectorAll(
                    '.subject-code'
                );

            const grades =
                document.querySelectorAll(
                    '.grade-input'
                );


            for (
                let i = 0;
                i < subjectCodes.length;
                i++
            ) {

                const subject =
                    subjectCodes[i].value.trim();

                const grade =
                    grades[i].value.trim();


                if (
                    subject === '' &&
                    grade === ''
                ) {

                    continue;

                }


                if (
                    subject !== '' &&
                    grade === ''
                ) {

                    event.preventDefault();

                    alert(
                        'PLEASE ENTER A GRADE FOR COURSE CODE: ' +
                        subject
                    );

                    grades[i].focus();

                    return;

                }


                if (
                    subject === '' &&
                    grade !== ''
                ) {

                    event.preventDefault();

                    alert(
                        'PLEASE ENTER THE COURSE CODE FOR GRADE: ' +
                        grade
                    );

                    subjectCodes[i].focus();

                    return;

                }


                if (grade !== '') {

                    const numericGrade =
                        parseFloat(grade);


                    if (
                        isNaN(numericGrade) ||
                        numericGrade < 0 ||
                        numericGrade > 74
                    ) {

                        event.preventDefault();

                        alert(
                            'ONLY FAILED GRADES FROM 0 TO 74 MAY BE ENCODED.'
                        );

                        grades[i].focus();

                        return;

                    }

                }

            }


            /* =================================================
               FINAL CONFIRMATION
            ================================================= */

            const confirmed =
                confirm(
                    'ARE YOU SURE YOU WANT TO SAVE THIS STUDENT AND THEIR ACADEMIC GRADES?'
                );


            if (!confirmed) {

                event.preventDefault();

                return;

            }

        }
    );


    /* =========================================================
       IF VALIDATION FAILED IN STEP 2,
       STAY ON STEP 2
    ========================================================= */

    @if(
        old('school_year') ||
        old('semester_id') ||
        old('total_subjects') ||
        old('subject_code') ||
        $errors->has('subject_code.*') ||
        $errors->has('grade.*') ||
        $errors->has('semester_id') ||
        $errors->has('school_year')
    )

        goToStep(2);

    @endif

});


/* =============================================================
   VALIDATE STUDENT INFORMATION
============================================================= */

function validateStudentInformation() {

    const studentNo =
        document.getElementById(
            'student_no'
        );

    const firstName =
        document.getElementById(
            'first_name'
        );

    const middleName =
        document.getElementById(
            'middle_name'
        );

    const lastName =
        document.getElementById(
            'last_name'
        );

    const admissionYear =
        document.getElementById(
            'admission_year'
        );

    const currentYearLevel =
        document.getElementById(
            'current_year_level'
        );


    if (
        !studentNo.value.trim()
    ) {

        alert(
            'PLEASE ENTER THE STUDENT NUMBER.'
        );

        studentNo.focus();

        return false;

    }


    if (
        !/^\d{1,10}$/.test(
            studentNo.value.trim()
        )
    ) {

        alert(
            'STUDENT NUMBER MUST CONTAIN NUMBERS ONLY AND MUST NOT EXCEED 10 DIGITS.'
        );

        studentNo.focus();

        return false;

    }


    if (
        !firstName.value.trim()
    ) {

        alert(
            'PLEASE ENTER THE FIRST NAME.'
        );

        firstName.focus();

        return false;

    }


    if (
        /\d/.test(
            firstName.value
        )
    ) {

        alert(
            'FIRST NAME MUST NOT CONTAIN NUMBERS.'
        );

        firstName.focus();

        return false;

    }


    if (
        middleName.value &&
        /\d/.test(
            middleName.value
        )
    ) {

        alert(
            'MIDDLE NAME MUST NOT CONTAIN NUMBERS.'
        );

        middleName.focus();

        return false;

    }


    if (
        !lastName.value.trim()
    ) {

        alert(
            'PLEASE ENTER THE LAST NAME.'
        );

        lastName.focus();

        return false;

    }


    if (
        /\d/.test(
            lastName.value
        )
    ) {

        alert(
            'LAST NAME MUST NOT CONTAIN NUMBERS.'
        );

        lastName.focus();

        return false;

    }


    if (
        !/^\d{4}$/.test(
            admissionYear.value.trim()
        )
    ) {

        alert(
            'ADMISSION YEAR MUST CONTAIN EXACTLY 4 NUMBERS.'
        );

        admissionYear.focus();

        return false;

    }


    if (
        !currentYearLevel.value
    ) {

        alert(
            'PLEASE SELECT THE CURRENT YEAR LEVEL.'
        );

        currentYearLevel.focus();

        return false;

    }


    return true;

}


/* =============================================================
   STEP NAVIGATION
============================================================= */

function goToStep(step) {

    if (step === 1) {

        document
            .getElementById('step1')
            .classList.add('active');

        document
            .getElementById('step2')
            .classList.remove('active');


        document
            .getElementById('step1Button')
            .classList.add('active');

        document
            .getElementById('step2Button')
            .classList.remove('active');

        return;

    }


    if (step === 2) {

        if (
            !validateStudentInformation()
        ) {

            document
                .getElementById('step1')
                .classList.add('active');

            document
                .getElementById('step2')
                .classList.remove('active');

            document
                .getElementById('step1Button')
                .classList.add('active');

            document
                .getElementById('step2Button')
                .classList.remove('active');

            return;

        }


        document
            .getElementById('step1')
            .classList.remove('active');

        document
            .getElementById('step2')
            .classList.add('active');


        document
            .getElementById('step1Button')
            .classList.remove('active');

        document
            .getElementById('step1Button')
            .classList.add('completed');

        document
            .getElementById('step2Button')
            .classList.add('active');

    }

}


/* =============================================================
   CLOSE / CANCEL CREATE PAGE
============================================================= */

function closeCreatePage() {

    const confirmed =
        confirm(
            'ARE YOU SURE YOU WANT TO CANCEL? ANY INFORMATION YOU ENTERED WILL NOT BE SAVED.'
        );


    if (!confirmed) {
        return;
    }


    window.location.href =
        "{{ route('students.index') }}";

}


/* =============================================================
   KEYBOARD NAVIGATION
============================================================= */

/*
    Arrow keys:

    ← = move left
    → = move right
    ↑ = move up
    ↓ = move down

    The browser's normal arrow-key behavior is prevented
    when another form field is available.

    This allows the admin to type and move around the
    form using only the keyboard.
*/

function getKeyboardFields() {

    return Array.from(
        document.querySelectorAll(
            '.keyboard-field'
        )
    ).filter(function(field) {

        return !field.disabled &&
               field.offsetParent !== null;

    });

}


function findClosestCreateField(
    current,
    direction
) {

    const fields =
        getKeyboardFields();

    if (!fields.length) {
        return null;
    }


    const currentRect =
        current.getBoundingClientRect();

    const currentCenterX =
        currentRect.left +
        currentRect.width / 2;

    const currentCenterY =
        currentRect.top +
        currentRect.height / 2;


    let candidates = [];


    fields.forEach(function(field) {

        if (field === current) {
            return;
        }


        const rect =
            field.getBoundingClientRect();

        const centerX =
            rect.left +
            rect.width / 2;

        const centerY =
            rect.top +
            rect.height / 2;

        const dx =
            centerX - currentCenterX;

        const dy =
            centerY - currentCenterY;


        if (
            direction === 'left' &&
            dx < -5
        ) {

            candidates.push({
                field: field,
                horizontal: Math.abs(dx),
                vertical: Math.abs(dy)
            });

        }


        if (
            direction === 'right' &&
            dx > 5
        ) {

            candidates.push({
                field: field,
                horizontal: Math.abs(dx),
                vertical: Math.abs(dy)
            });

        }


        if (
            direction === 'up' &&
            dy < -5
        ) {

            candidates.push({
                field: field,
                horizontal: Math.abs(dx),
                vertical: Math.abs(dy)
            });

        }


        if (
            direction === 'down' &&
            dy > 5
        ) {

            candidates.push({
                field: field,
                horizontal: Math.abs(dx),
                vertical: Math.abs(dy)
            });

        }

    });


    if (!candidates.length) {
        return null;
    }


    candidates.sort(function(a, b) {

        if (
            direction === 'left' ||
            direction === 'right'
        ) {

            const aScore =
                (a.vertical * 3) +
                a.horizontal;

            const bScore =
                (b.vertical * 3) +
                b.horizontal;

            return aScore - bScore;

        }


        const aScore =
            (a.horizontal * 3) +
            a.vertical;

        const bScore =
            (b.horizontal * 3) +
            b.vertical;

        return aScore - bScore;

    });


    return candidates[0].field;

}


document.addEventListener(
    'keydown',
    function(e) {

        if (
            !e.target.classList.contains(
                'keyboard-field'
            )
        ) {
            return;
        }


        let direction = null;


        if (e.key === 'ArrowUp') {
            direction = 'up';
        }

        else if (e.key === 'ArrowDown') {
            direction = 'down';
        }

        else if (e.key === 'ArrowLeft') {
            direction = 'left';
        }

        else if (e.key === 'ArrowRight') {
            direction = 'right';
        }


        if (!direction) {
            return;
        }


        const next =
            findClosestCreateField(
                e.target,
                direction
            );


        if (next) {

            e.preventDefault();

            next.focus();

            next.classList.add(
                'keyboard-focus'
            );


            if (
                next.tagName === 'INPUT' &&
                (
                    next.type === 'text' ||
                    next.type === 'number'
                )
            ) {

                try {

                    const length =
                        next.value.length;

                    next.setSelectionRange(
                        length,
                        length
                    );

                }

                catch (error) {
                    /* Number inputs may not support selection. */
                }

            }

        }

    }
);


/* =============================================================
   ENTER KEY NAVIGATION
============================================================= */

document.addEventListener(
    'keydown',
    function(e) {

        if (
            !e.target.classList.contains(
                'keyboard-field'
            )
        ) {
            return;
        }


        if (e.key !== 'Enter') {
            return;
        }


        e.preventDefault();


        const fields =
            getKeyboardFields();

        const currentIndex =
            fields.indexOf(e.target);


        if (
            currentIndex >= 0 &&
            currentIndex < fields.length - 1
        ) {

            fields[
                currentIndex + 1
            ].focus();

        }

    }
);


/* =============================================================
   FOCUS INDICATOR
============================================================= */

document.addEventListener(
    'focus',
    function(e) {

        if (
            e.target.classList.contains(
                'keyboard-field'
            )
        ) {

            e.target.classList.add(
                'keyboard-focus'
            );

        }

    },
    true
);


document.addEventListener(
    'blur',
    function(e) {

        if (
            e.target.classList.contains(
                'keyboard-field'
            )
        ) {

            e.target.classList.remove(
                'keyboard-focus'
            );

        }

    },
    true
);

</script>

@endsection