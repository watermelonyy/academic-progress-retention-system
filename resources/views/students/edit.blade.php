@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    {{-- Back Button --}}
    <div class="mb-3">
        <button
            type="button"
            class="btn btn-light border shadow-sm back-button"
            onclick="history.back()"
        >
            <i class="fa fa-arrow-left me-2"></i>
            Back
        </button>
    </div>

    <div class="card shadow-sm border-0 edit-card">

        {{-- Header --}}
        <div class="card-header edit-header">
            <div>
                <h4 class="mb-1">
                    <i class="fa fa-user-pen me-2"></i>
                    Edit Student
                </h4>
                <small>
                    Update student information, academic period, and failed subjects.
                </small>
            </div>
        </div>

        <div class="card-body p-4">

            {{-- Validation Errors --}}
            @if ($errors->any())

                <div class="alert alert-danger border-0 shadow-sm mb-4">

                    <div class="d-flex align-items-start">

                        <i class="fa fa-circle-exclamation me-2 mt-1"></i>

                        <div>

                            <strong>Please correct the following:</strong>

                            <ul class="mb-0 mt-2">

                                @foreach ($errors->all() as $error)

                                    <li>{{ $error }}</li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            <form
                action="{{ route('students.update', $student->student_id) }}"
                method="POST"
                id="editStudentForm"
            >

                @csrf
                @method('PUT')


                {{-- =====================================================
                     STUDENT INFORMATION
                ====================================================== --}}

                <div class="section-title">

                    <div class="section-icon">
                        <i class="fa fa-user"></i>
                    </div>

                    <div>
                        <h5 class="mb-0">Student Information</h5>
                        <small>Basic information of the student</small>
                    </div>

                </div>


                <div class="row g-3">

                    {{-- Student Number --}}
                    <div class="col-md-4">

                        <label class="form-label">
                            Student Number
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa fa-id-card"></i>
                            </span>

                            <input
                                type="text"
                                name="student_no"
                                id="student_no"
                                class="form-control keyboard-field"
                                value="{{ old('student_no', $student->student_no) }}"
                                required
                                autocomplete="off"
                                inputmode="numeric"
                                pattern="[0-9]{1,10}"
                                maxlength="10"
                                data-key-row="student-info"
                                data-key-col="1"
                            >

                        </div>

                    </div>


                    {{-- First Name --}}
                    <div class="col-md-4">

                        <label class="form-label">
                            First Name
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa fa-user"></i>
                            </span>

                            <input
                                type="text"
                                name="first_name"
                                id="first_name"
                                class="form-control keyboard-field"
                                value="{{ old('first_name', $student->first_name) }}"
                                required
                                autocomplete="off"
                                data-key-row="student-info"
                                data-key-col="2"
                            >

                        </div>

                    </div>


                    {{-- Middle Name --}}
                    <div class="col-md-4">

                        <label class="form-label">
                            Middle Name
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa fa-user"></i>
                            </span>

                            <input
                                type="text"
                                name="middle_name"
                                id="middle_name"
                                class="form-control keyboard-field"
                                value="{{ old('middle_name', $student->middle_name) }}"
                                autocomplete="off"
                                data-key-row="student-info"
                                data-key-col="3"
                            >

                        </div>

                    </div>


                    {{-- Last Name --}}
                    <div class="col-md-4">

                        <label class="form-label">
                            Last Name
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa fa-user"></i>
                            </span>

                            <input
                                type="text"
                                name="last_name"
                                id="last_name"
                                class="form-control keyboard-field"
                                value="{{ old('last_name', $student->last_name) }}"
                                required
                                autocomplete="off"
                                data-key-row="student-info"
                                data-key-col="1"
                            >

                        </div>

                    </div>


                    {{-- Admission Year --}}
                    <div class="col-md-4">

                        <label class="form-label">
                            Admission Year
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </span>

                            <input
                                type="text"
                                name="admission_year"
                                id="admission_year"
                                class="form-control keyboard-field"
                                value="{{ old('admission_year', $student->admission_year) }}"
                                required
                                autocomplete="off"
                                inputmode="numeric"
                                pattern="[0-9]{4}"
                                maxlength="4"
                                data-key-row="student-info"
                                data-key-col="2"
                            >

                        </div>

                    </div>


                    {{-- Current Year Level --}}
                    <div class="col-md-4">

                        <label class="form-label">
                            Current Year Level
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa fa-graduation-cap"></i>
                            </span>

                            <select
                                name="current_year_level"
                                class="form-select keyboard-field"
                                required
                                data-key-row="student-info"
                                data-key-col="3"
                            >

                                <option value="1st Year"
                                    {{ old('current_year_level', $student->current_year_level) == '1st Year' ? 'selected' : '' }}>
                                    1st Year
                                </option>

                                <option value="2nd Year"
                                    {{ old('current_year_level', $student->current_year_level) == '2nd Year' ? 'selected' : '' }}>
                                    2nd Year
                                </option>

                                <option value="3rd Year"
                                    {{ old('current_year_level', $student->current_year_level) == '3rd Year' ? 'selected' : '' }}>
                                    3rd Year
                                </option>

                                <option value="4th Year"
                                    {{ old('current_year_level', $student->current_year_level) == '4th Year' ? 'selected' : '' }}>
                                    4th Year
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                     ACADEMIC YEAR / SEMESTER
                ====================================================== --}}

                <div class="section-title mt-5">

                    <div class="section-icon">
                        <i class="fa fa-calendar-days"></i>
                    </div>

                    <div>
                        <h5 class="mb-0">Academic Period</h5>
                        <small>Select the school year, semester, and total subjects.</small>
                    </div>

                </div>


                @php

                    $selectedSemesterId =
                        old('semester_id', $currentSemesterId);

                    $selectedSemester =
                        $semesters->firstWhere(
                            'semester_id',
                            $selectedSemesterId
                        );

                    $selectedSchoolYear =
                        $selectedSemester
                            ? $selectedSemester->school_year
                            : '';

                    /*
                     * IMPORTANT:
                     * School years are arranged from PAST TO FUTURE.
                     * Example:
                     * 2021-2022
                     * 2022-2023
                     * 2023-2024
                     * 2024-2025
                     * 2025-2026
                     */
                    $schoolYears =
                        $semesters
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

                    /*
                     * Get the total subjects from the latest
                     * status history record for the selected semester.
                     */
                    $selectedTotalSubjects =
                        old(
                            'total_subjects',
                            optional(
                                $student->statusHistory
                                    ->where('semester_id', $selectedSemesterId)
                                    ->sortByDesc('created_at')
                                    ->first()
                            )->total_subjects
                        );

                @endphp


                <div class="row g-3">

                    {{-- SCHOOL YEAR --}}
                    <div class="col-md-4">

                        <label class="form-label academic-period-label">
                            School Year
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            id="school_year"
                            class="form-select academic-school-year keyboard-field"
                            required
                            data-key-row="academic-period"
                            data-key-col="1"
                        >

                            <option value="">
                                Select School Year
                            </option>

                            @foreach($schoolYears as $schoolYear)

                                <option
                                    value="{{ $schoolYear }}"
                                    {{ (string) $selectedSchoolYear === (string) $schoolYear ? 'selected' : '' }}
                                >
                                    {{ $schoolYear }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- SEMESTER --}}
                    <div class="col-md-4">

                        <label class="form-label academic-period-label">
                            Semester
                            <span class="text-danger">*</span>
                        </label>


                        {{-- Hidden field keeps the existing backend structure --}}
                        <input
                            type="hidden"
                            name="semester_id"
                            id="semester_id"
                            value="{{ $selectedSemesterId }}"
                        >


                        <div class="semester-options">

                            @foreach($semesters as $semester)

                                <label
                                    class="semester-option"
                                    data-school-year="{{ $semester->school_year }}"
                                >

                                    <input
                                        type="radio"
                                        name="semester_choice"
                                        value="{{ $semester->semester_id }}"
                                        class="semester-radio"
                                        data-school-year="{{ $semester->school_year }}"
                                        {{ (string) $selectedSemesterId === (string) $semester->semester_id ? 'checked' : '' }}
                                    >

                                    <span class="semester-circle"></span>

                                    <span class="semester-text">
                                        {{ $semester->semester_name }}
                                    </span>

                                </label>

                            @endforeach

                        </div>

                    </div>


                    {{-- TOTAL SUBJECTS --}}
                    <div class="col-md-4">

                        <label
                            for="total_subjects"
                            class="form-label academic-period-label"
                        >
                            Total Subjects
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa fa-book"></i>
                            </span>

                            <input
                                type="number"
                                name="total_subjects"
                                id="total_subjects"
                                class="form-control keyboard-field"
                                value="{{ $selectedTotalSubjects }}"
                                min="1"
                                max="100"
                                step="1"
                                required
                                autocomplete="off"
                                placeholder="Enter total subjects"
                                data-key-row="academic-period"
                                data-key-col="3"
                            >

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                     SUBJECTS AND GRADES
                ====================================================== --}}

                <div class="section-title mt-5">

                    <div class="section-icon">
                        <i class="fa fa-book-open"></i>
                    </div>

                    <div>
                        <h5 class="mb-0">Subjects and Grades</h5>
                        <small>Update existing failed subjects or add new failed subjects.</small>
                    </div>

                </div>


                <div class="subject-container">

                    <div class="table-responsive">

                        <table class="table subject-table mb-0">

                            <thead>

                                <tr>

                                    <th width="50%">
                                        <i class="fa fa-book me-1"></i>
                                        Course Code
                                    </th>

                                    <th width="25%">
                                        <i class="fa fa-star me-1"></i>
                                        Grade
                                    </th>

                                    <th width="25%">
                                        <i class="fa fa-gears me-1"></i>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="subjectTable">

                                @foreach($student->academicGrades->where('semester_id', $selectedSemesterId)->values() as $record)

                                    <tr>

                                        <td>

                                            <input
                                                type="hidden"
                                                name="grade_id[]"
                                                value="{{ $record->grade_id }}"
                                            >

                                            <label class="mobile-label">
                                                Course Code
                                            </label>

                                            <input
                                                type="text"
                                                name="subject_code[]"
                                                class="form-control keyboard-field subject-code"
                                                style="text-transform: uppercase;"
                                                value="{{ old('subject_code.' . $loop->index, $record->subject->subject_code) }}"
                                                required
                                                autocomplete="off"
                                                data-key-type="subject-code"
                                            >

                                            @if($errors->has('subject_code.' . $loop->index))

                                                <small class="text-danger d-block mt-1">

                                                    {{ $errors->first('subject_code.' . $loop->index) }}

                                                </small>

                                            @endif

                                        </td>


                                        <td>

                                            <label class="mobile-label">
                                                Grade
                                            </label>

                                            <input
                                                type="number"
                                                name="grade[]"
                                                class="form-control keyboard-field grade-input"
                                                min="1"
                                                max="100"
                                                step="1"
                                                value="{{ old('grade.' . $loop->index, $record->grade) }}"
                                                required
                                                data-key-type="grade"
                                            >

                                        </td>


                                        <td class="action-cell">

                                            <button
                                                type="button"
                                                class="btn btn-danger removeRow"
                                            >
                                                <i class="fa fa-trash me-1"></i>
                                                Remove
                                            </button>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>


                {{-- Add Subject --}}
                <div class="mt-3">

                    <button
                        type="button"
                        id="addSubject"
                        class="btn btn-add-subject"
                    >
                        <i class="fa fa-plus me-1"></i>
                        Add Subject
                    </button>

                </div>


                <div id="deletedRecords"></div>


                {{-- =====================================================
                     FORM ACTIONS
                ====================================================== --}}

                <div class="form-actions mt-4">

                    <button
                        type="submit"
                        class="btn btn-success btn-save"
                    >
                        <i class="fa fa-save me-2"></i>
                        Update Student
                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


<style>

/* ============================================================
   GENERAL
============================================================ */

.edit-card {
    border-radius: 16px;
    overflow: hidden;
    background: #ffffff;
}

.edit-card .card-body {
    background: #ffffff;
}


/* ============================================================
   HEADER
============================================================ */

.edit-header {
    background: #198754;
    color: #ffffff;
    padding: 22px 24px;
    border: 0;
}

.edit-header h4 {
    font-weight: 700;
    letter-spacing: 0.2px;
}

.edit-header small {
    opacity: 0.9;
}


/* ============================================================
   BACK BUTTON
============================================================ */

.back-button {
    border-radius: 9px;
    padding: 8px 16px;
    font-weight: 600;
    color: #495057;
    transition: 0.2s ease;
}

.back-button:hover {
    transform: translateX(-2px);
    background: #f8f9fa;
}


/* ============================================================
   SECTION TITLES
============================================================ */

.section-title {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 22px;
    padding-bottom: 13px;
    border-bottom: 1px solid #e9ecef;
}

.section-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #eaf7ef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #198754;
    flex-shrink: 0;
}

.section-title h5 {
    font-weight: 700;
    color: #212529;
}

.section-title small {
    color: #6c757d;
    display: block;
    margin-top: 3px;
}


/* ============================================================
   FORM CONTROLS
============================================================ */

.form-label {
    font-weight: 600;
    color: #343a40;
    margin-bottom: 8px;
}

.form-control,
.form-select,
.input-group-text {
    min-height: 44px;
}

.form-control,
.form-select {
    border-color: #d8dee4;
    border-radius: 8px;
}

.input-group .form-control {
    border-radius: 0 8px 8px 0;
}

.input-group-text {
    background: #f8f9fa;
    border-color: #d8dee4;
    min-width: 45px;
    justify-content: center;
    color: #6c757d;
}

.form-control:focus,
.form-select:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.18rem rgba(25, 135, 84, 0.13);
}

.input-group:focus-within .input-group-text {
    border-color: #198754;
    color: #198754;
}


/* ============================================================
   ACADEMIC PERIOD
============================================================ */

.academic-period-label {
    font-weight: 600;
    margin-bottom: 9px;
}

.academic-school-year {
    width: 100%;
    min-height: 48px;
    border-radius: 9px;
    padding: 0 14px;
    background-color: #ffffff;
    color: #263238;
    font-size: 15px;
}

.academic-school-year:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.18rem rgba(25, 135, 84, 0.13);
}


/* ============================================================
   SEMESTER OPTIONS
============================================================ */

.semester-options {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    min-height: 48px;
}

.semester-option {
    min-height: 48px;
    border: 1px solid #d8dee4;
    border-radius: 9px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 0 14px;
    cursor: pointer;
    color: #343a40;
    transition: 0.2s ease;
    margin: 0;
}

.semester-option:hover {
    border-color: #198754;
    background: #f7fcf9;
}

.semester-option.selected {
    border-color: #198754;
    background: #eaf7ef;
    color: #146c43;
    font-weight: 600;
}

.semester-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.semester-circle {
    width: 12px;
    height: 12px;
    border: 1px solid #adb5bd;
    border-radius: 50%;
    background: #ffffff;
    flex-shrink: 0;
    position: relative;
}

.semester-radio:checked + .semester-circle {
    border-color: #198754;
}

.semester-radio:checked + .semester-circle::after {
    content: "";
    position: absolute;
    width: 6px;
    height: 6px;
    background: #198754;
    border-radius: 50%;
    top: 2px;
    left: 2px;
}

.semester-text {
    font-size: 15px;
    font-weight: 500;
}


/* ============================================================
   SUBJECT TABLE
============================================================ */

.subject-container {
    border: 1px solid #e1e5e9;
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
}

.subject-table {
    vertical-align: middle;
}

.subject-table thead th {
    background: #f6f8f9;
    color: #343a40;
    font-weight: 700;
    padding: 14px 15px;
    border-bottom: 1px solid #e1e5e9;
}

.subject-table tbody td {
    padding: 14px 15px;
    border-color: #edf0f2;
}

.subject-table tbody tr {
    transition: background 0.15s ease;
}

.subject-table tbody tr:hover {
    background: #fafcfb;
}

.action-cell {
    text-align: center;
}

.subject-code {
    text-transform: uppercase;
}


/* ============================================================
   ADD SUBJECT BUTTON
============================================================ */

.btn-add-subject {
    background: #eaf7ef;
    color: #198754;
    border: 1px solid #b7dfc8;
    border-radius: 8px;
    font-weight: 600;
    padding: 9px 16px;
    transition: 0.2s ease;
}

.btn-add-subject:hover {
    background: #198754;
    border-color: #198754;
    color: #ffffff;
}


/* ============================================================
   SAVE BUTTON
============================================================ */

.btn-save {
    background: #198754;
    border-color: #198754;
    border-radius: 9px;
    font-weight: 600;
    padding: 10px 20px;
    min-width: 175px;
}

.btn-save:hover,
.btn-save:focus {
    background: #157347;
    border-color: #146c43;
}


/* ============================================================
   KEYBOARD FOCUS
============================================================ */

.keyboard-field.keyboard-focus {
    border-color: #198754 !important;
    box-shadow:
        0 0 0 0.18rem rgba(25, 135, 84, 0.14) !important;
    background: #fbfffc;
}


/* ============================================================
   REMOVE NUMBER INPUT SPINNERS
============================================================ */

.grade-input::-webkit-outer-spin-button,
.grade-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.grade-input {
    -moz-appearance: textfield;
    appearance: textfield;
}


/* ============================================================
   BUTTONS
============================================================ */

.btn {
    border-radius: 8px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}


/* ============================================================
   MOBILE LABEL
============================================================ */

.mobile-label {
    display: none;
}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 768px) {

    .card-body {
        padding: 20px !important;
    }

    .semester-options {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .semester-option {
        padding: 0 8px;
    }

    .semester-text {
        font-size: 14px;
    }

    .subject-table thead {
        display: none;
    }

    .subject-table,
    .subject-table tbody,
    .subject-table tr,
    .subject-table td {
        display: block;
        width: 100%;
    }

    .subject-table tbody tr {
        border-bottom: 1px solid #dee2e6;
        padding: 15px 0;
    }

    .subject-table tbody tr:last-child {
        border-bottom: 0;
    }

    .subject-table tbody td {
        padding: 8px 15px;
        border: 0;
    }

    .mobile-label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .action-cell {
        text-align: left;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        width: 100%;
    }

}

</style>


<script>

/* ============================================================
   STUDENT NUMBER - NUMBERS ONLY - MAXIMUM 10 DIGITS
============================================================ */

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


/* ============================================================
   ADMISSION YEAR - NUMBERS ONLY - EXACTLY 4 DIGITS
============================================================ */

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


/* ============================================================
   FIRST / MIDDLE / LAST NAME - NO NUMBERS
============================================================ */

const nameFieldIds = [
    'first_name',
    'middle_name',
    'last_name'
];


nameFieldIds.forEach(
    function (fieldId) {

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
                        .replace(/[0-9]/g, '');

            }
        );

    }
);


/* ============================================================
   TOTAL SUBJECTS - WHOLE NUMBERS ONLY
============================================================ */

const totalSubjects =
    document.getElementById('total_subjects');

if (totalSubjects) {

    totalSubjects.addEventListener(
        'input',
        function () {

            this.value =
                this.value
                    .replace(/\D/g, '')
                    .slice(0, 3);

        }
    );

}


/* ============================================================
   SCHOOL YEAR / SEMESTER
============================================================ */

const schoolYearSelect =
    document.getElementById('school_year');

const semesterIdInput =
    document.getElementById('semester_id');

const semesterOptions =
    document.querySelectorAll('.semester-option');

const semesterRadios =
    document.querySelectorAll('.semester-radio');


function updateSemesterOptions() {

    if (!schoolYearSelect) {
        return;
    }

    const selectedSchoolYear =
        schoolYearSelect.value;


    semesterOptions.forEach(function(option) {

        const optionSchoolYear =
            option.getAttribute(
                'data-school-year'
            );

        const radio =
            option.querySelector(
                '.semester-radio'
            );


        if (
            optionSchoolYear ===
            selectedSchoolYear
        ) {

            option.style.display = 'flex';

        }

        else {

            option.style.display = 'none';

            if (radio) {
                radio.checked = false;
            }

        }

    });


    let selectedSemesterFound = false;


    semesterRadios.forEach(function(radio) {

        if (
            radio.checked &&
            radio.getAttribute(
                'data-school-year'
            ) === selectedSchoolYear
        ) {

            selectedSemesterFound = true;

            if (semesterIdInput) {
                semesterIdInput.value =
                    radio.value;
            }

        }

    });


    if (!selectedSemesterFound) {

        let firstAvailableRadio = null;


        semesterRadios.forEach(function(radio) {

            if (
                !firstAvailableRadio &&
                radio.getAttribute(
                    'data-school-year'
                ) === selectedSchoolYear
            ) {

                firstAvailableRadio = radio;

            }

        });


        if (firstAvailableRadio) {

            firstAvailableRadio.checked = true;

            if (semesterIdInput) {
                semesterIdInput.value =
                    firstAvailableRadio.value;
            }

        }

        else {

            if (semesterIdInput) {
                semesterIdInput.value = '';
            }

        }

    }


    updateSemesterSelectedStyle();

}


function updateSemesterSelectedStyle() {

    semesterOptions.forEach(function(option) {

        const radio =
            option.querySelector(
                '.semester-radio'
            );

        if (
            radio &&
            radio.checked
        ) {

            option.classList.add(
                'selected'
            );

        }

        else {

            option.classList.remove(
                'selected'
            );

        }

    });

}


if (schoolYearSelect) {

    schoolYearSelect.addEventListener(
        'change',
        function () {

            updateSemesterOptions();

        }
    );

}


semesterRadios.forEach(function(radio) {

    radio.addEventListener(
        'change',
        function () {

            if (this.checked) {

                if (semesterIdInput) {

                    semesterIdInput.value =
                        this.value;

                }

            }

            updateSemesterSelectedStyle();

        }
    );

});


/* ============================================================
   ADD SUBJECT
============================================================ */

const addSubjectButton =
    document.getElementById('addSubject');

if (addSubjectButton) {

    addSubjectButton.addEventListener(
        'click',
        function() {

            let row = `

                <tr>

                    <td>

                        <input
                            type="hidden"
                            name="grade_id[]"
                            value=""
                        >

                        <label class="mobile-label">
                            Course Code
                        </label>

                        <input
                            type="text"
                            name="subject_code[]"
                            class="form-control keyboard-field subject-code"
                            style="text-transform: uppercase;"
                            placeholder="Enter Course Code"
                            required
                            autocomplete="off"
                            data-key-type="subject-code"
                        >

                    </td>

                    <td>

                        <label class="mobile-label">
                            Grade
                        </label>

                        <input
                            type="number"
                            name="grade[]"
                            class="form-control keyboard-field grade-input"
                            min="1"
                            max="100"
                            step="1"
                            required
                            data-key-type="grade"
                        >

                    </td>

                    <td class="action-cell">

                        <button
                            type="button"
                            class="btn btn-danger removeRow"
                        >
                            <i class="fa fa-trash me-1"></i>
                            Remove
                        </button>

                    </td>

                </tr>

            `;


            document
                .getElementById('subjectTable')
                .insertAdjacentHTML(
                    'beforeend',
                    row
                );


            updateKeyboardNavigation();


            let rows =
                document.querySelectorAll(
                    '#subjectTable tr'
                );


            let lastRow =
                rows[rows.length - 1];


            let newSubject =
                lastRow.querySelector(
                    'input[name="subject_code[]"]'
                );


            if (newSubject) {

                newSubject.focus();

                newSubject.classList.add(
                    'keyboard-focus'
                );

            }

        }
    );

}


/* ============================================================
   REMOVE SUBJECT
============================================================ */

document.addEventListener(
    'click',
    function(e) {

        if (
            e.target.closest('.removeRow')
        ) {

            let button =
                e.target.closest('.removeRow');

            let row =
                button.closest('tr');

            let gradeId =
                row.querySelector(
                    'input[name="grade_id[]"]'
                );


            if (
                gradeId &&
                gradeId.value !== ''
            ) {

                let deletedContainer =
                    document.getElementById(
                        'deletedRecords'
                    );

                let input =
                    document.createElement(
                        'input'
                    );

                input.type = 'hidden';

                input.name =
                    'deleted_grade_ids[]';

                input.value =
                    gradeId.value;

                deletedContainer
                    .appendChild(input);

            }


            row.remove();

            updateKeyboardNavigation();

        }

    }
);


/* ============================================================
   AUTOMATIC UPPERCASE FOR COURSE CODE
============================================================ */

document.addEventListener(
    'input',
    function(e) {

        if (
            e.target.name === 'subject_code[]'
        ) {

            e.target.value =
                e.target.value.toUpperCase();

        }

    }
);


/* ============================================================
   FORM VALIDATION
============================================================ */

document
    .getElementById('editStudentForm')
    .addEventListener(
        'submit',
        function(e) {

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

            const totalSubjects =
                document.getElementById(
                    'total_subjects'
                );


            /* =================================================
               STUDENT NUMBER
            ================================================= */

            if (
                !/^\d{1,10}$/.test(
                    studentNo.value.trim()
                )
            ) {

                e.preventDefault();

                alert(
                    'STUDENT NUMBER MUST CONTAIN NUMBERS ONLY AND MUST NOT EXCEED 10 DIGITS.'
                );

                studentNo.focus();

                return;

            }


            /* =================================================
               FIRST NAME
            ================================================= */

            if (
                !firstName.value.trim()
            ) {

                e.preventDefault();

                alert(
                    'PLEASE ENTER THE FIRST NAME.'
                );

                firstName.focus();

                return;

            }


            if (
                /\d/.test(
                    firstName.value
                )
            ) {

                e.preventDefault();

                alert(
                    'FIRST NAME MUST NOT CONTAIN NUMBERS.'
                );

                firstName.focus();

                return;

            }


            /* =================================================
               MIDDLE NAME
            ================================================= */

            if (
                middleName.value &&
                /\d/.test(
                    middleName.value
                )
            ) {

                e.preventDefault();

                alert(
                    'MIDDLE NAME MUST NOT CONTAIN NUMBERS.'
                );

                middleName.focus();

                return;

            }


            /* =================================================
               LAST NAME
            ================================================= */

            if (
                !lastName.value.trim()
            ) {

                e.preventDefault();

                alert(
                    'PLEASE ENTER THE LAST NAME.'
                );

                lastName.focus();

                return;

            }


            if (
                /\d/.test(
                    lastName.value
                )
            ) {

                e.preventDefault();

                alert(
                    'LAST NAME MUST NOT CONTAIN NUMBERS.'
                );

                lastName.focus();

                return;

            }


            /* =================================================
               ADMISSION YEAR
            ================================================= */

            if (
                !/^\d{4}$/.test(
                    admissionYear.value.trim()
                )
            ) {

                e.preventDefault();

                alert(
                    'ADMISSION YEAR MUST CONTAIN EXACTLY 4 NUMBERS.'
                );

                admissionYear.focus();

                return;

            }


            /* =================================================
               TOTAL SUBJECTS
            ================================================= */

            if (
                !totalSubjects ||
                !/^\d+$/.test(
                    totalSubjects.value.trim()
                ) ||
                parseInt(
                    totalSubjects.value,
                    10
                ) < 1 ||
                parseInt(
                    totalSubjects.value,
                    10
                ) > 100
            ) {

                e.preventDefault();

                alert(
                    'TOTAL SUBJECTS MUST BE A WHOLE NUMBER FROM 1 TO 100.'
                );

                if (totalSubjects) {
                    totalSubjects.focus();
                }

                return;

            }


            /* =================================================
               SEMESTER
            ================================================= */

            const semesterId =
                document.getElementById(
                    'semester_id'
                );


            if (
                !semesterId ||
                !semesterId.value
            ) {

                e.preventDefault();

                alert(
                    'PLEASE SELECT A SCHOOL YEAR AND SEMESTER.'
                );

                if (schoolYearSelect) {
                    schoolYearSelect.focus();
                }

                return;

            }

        }
    );


/* ============================================================
   KEYBOARD NAVIGATION
============================================================ */

/*
    Arrow keys:

    ← = nearest field to the left
    → = nearest field to the right
    ↑ = nearest field above
    ↓ = nearest field below

    IMPORTANT:
    This prevents the normal browser cursor movement when
    the admin is typing in an input.

    It also works with dynamically added subject rows.
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


function updateKeyboardNavigation() {

    getKeyboardFields().forEach(function(field) {

        field.classList.remove(
            'keyboard-focus'
        );

    });

}


function findClosestField(
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


        /*
         * LEFT
         */

        if (
            direction === 'left' &&
            dx < -5
        ) {

            candidates.push({
                field: field,
                distance: Math.sqrt(
                    (dx * dx) +
                    (dy * dy)
                ),
                horizontal: Math.abs(dx),
                vertical: Math.abs(dy)
            });

        }


        /*
         * RIGHT
         */

        if (
            direction === 'right' &&
            dx > 5
        ) {

            candidates.push({
                field: field,
                distance: Math.sqrt(
                    (dx * dx) +
                    (dy * dy)
                ),
                horizontal: Math.abs(dx),
                vertical: Math.abs(dy)
            });

        }


        /*
         * UP
         */

        if (
            direction === 'up' &&
            dy < -5
        ) {

            candidates.push({
                field: field,
                distance: Math.sqrt(
                    (dx * dx) +
                    (dy * dy)
                ),
                horizontal: Math.abs(dx),
                vertical: Math.abs(dy)
            });

        }


        /*
         * DOWN
         */

        if (
            direction === 'down' &&
            dy > 5
        ) {

            candidates.push({
                field: field,
                distance: Math.sqrt(
                    (dx * dx) +
                    (dy * dy)
                ),
                horizontal: Math.abs(dx),
                vertical: Math.abs(dy)
            });

        }

    });


    if (!candidates.length) {
        return null;
    }


    /*
     * For left/right:
     * Prefer fields that are on the same horizontal row.
     *
     * For up/down:
     * Prefer fields that are on the same vertical column.
     */

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


/*
 * Move the typing cursor to the field selected by
 * the arrow key.
 */

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


        const current =
            e.target;


        const next =
            findClosestField(
                current,
                direction
            );


        /*
         * Only stop the browser's normal arrow-key
         * behavior when another field actually exists.
         */

        if (next) {

            e.preventDefault();

            next.focus();

            next.classList.add(
                'keyboard-focus'
            );


            /*
             * For text inputs, put the typing cursor
             * inside the field.
             */

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


/* ============================================================
   ENTER KEY NAVIGATION
============================================================ */

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


        /*
         * Do not submit the form when Enter is pressed
         * inside a field.
         */

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


/* ============================================================
   FOCUS INDICATOR
============================================================ */

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


/* ============================================================
   INITIALIZE
============================================================ */

updateKeyboardNavigation();

updateSemesterOptions();

updateSemesterSelectedStyle();

</script>

@endsection