@extends('layouts.app')

@section('content')

<div class="container mt-4 mb-5 probation-page">

    {{-- BACK BUTTON --}}
    <div class="mb-3">
        <a
            href="{{ route('students.show', $student->student_id) }}"
            class="btn btn-back"
        >
            <i class="fa fa-arrow-left me-2"></i>
            Back
        </a>
    </div>

    <div class="card probation-card">

        {{-- HEADER --}}
        <div class="card-header probation-header">
            <div class="d-flex align-items-center gap-3">
                <div class="header-icon">
                    <i class="fa fa-file-contract"></i>
                </div>

                <div>
                    <h4 class="mb-1">Probationary Agreement Contract</h4>
                    <p class="mb-0">
                        Prepare the student's approved priority subjects.
                    </p>
                </div>
            </div>
        </div>

        {{-- BODY --}}
        <div class="card-body p-4">

            {{-- STUDENT INFORMATION --}}
            <div class="student-information mb-4">
                <div class="student-information-heading">
                    <i class="fa fa-user-graduate me-2"></i>
                    Student Information
                </div>

                <div class="row g-3 mt-1">

                    <div class="col-md-4">
                        <div class="information-label">Student Name</div>
                        <div class="information-value">
                            {{ $student->last_name }},
                            {{ $student->first_name }}
                            @if($student->middle_name)
                                {{ $student->middle_name }}
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="information-label">Student Number</div>
                        <div class="information-value">
                            {{ $student->student_no }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="information-label">Academic Status</div>
                        <div class="information-value">
                            <span class="status-badge">
                                <i class="fa fa-circle-exclamation me-1"></i>
                                Probation
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- VALIDATION ERRORS --}}
            @if($errors->any())
                <div class="alert alert-danger validation-alert">
                    <div class="d-flex align-items-start">
                        <i class="fa fa-circle-exclamation me-2 mt-1"></i>

                        <div>
                            <strong>Please correct the following:</strong>

                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif


            {{-- FORM --}}
            <form
                method="POST"
                action="{{ route('notices.probation.preview', $student->student_id) }}"
                id="probationForm"
            >
                @csrf

                {{-- SUBJECTS --}}
                <div class="section-heading mb-3">
                    <div class="section-heading-icon">
                        <i class="fa fa-book-open"></i>
                    </div>

                    <div>
                        <h5 class="mb-1">Priority Subjects</h5>
                        <small>Enter the subjects to be included in the agreement.</small>
                    </div>
                </div>

                <div class="subject-table-wrapper">
                    <div class="table-responsive">
                        <table class="table subject-table align-middle mb-0">

                            <thead>
                                <tr>
                                    <th class="number-column">#</th>
                                    <th class="course-column">Course No.</th>
                                    <th>Description</th>
                                    <th class="units-column">Units</th>
                                </tr>
                            </thead>

                            <tbody>
                                @for($i = 0; $i < 5; $i++)

                                    @php
                                        $oldCode = old('subject_code.' . $i, '');
                                    @endphp

                                    <tr>
                                        <td class="text-center">
                                            <span class="row-number">
                                                {{ $i + 1 }}
                                            </span>
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                name="subject_code[]"
                                                class="form-control course-code"
                                                placeholder="Enter course code"
                                                value="{{ $oldCode }}"
                                                autocomplete="off"
                                                data-row="{{ $i }}"
                                                aria-label="Course code for priority subject {{ $i + 1 }}"
                                            >

                                            <div
                                                class="course-message text-danger small mt-1"
                                                style="display: none;"
                                            ></div>
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                class="form-control subject-title"
                                                placeholder="Filled from Prospectus"
                                                readonly
                                                tabindex="-1"
                                                aria-label="Subject description"
                                            >
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                class="form-control unit-input text-center"
                                                placeholder="0.00"
                                                readonly
                                                tabindex="-1"
                                                aria-label="Subject units"
                                            >
                                        </td>
                                    </tr>

                                @endfor

                                {{-- TOTAL UNITS --}}
                                <tr class="total-row">
                                    <td colspan="3" class="text-end">
                                        <i class="fa fa-calculator me-2"></i>
                                        TOTAL UNITS
                                    </td>

                                    <td class="text-center">
                                        <span id="totalUnits">0.00</span>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                </div>

                {{-- UNIT WARNING --}}
                <div
                    id="unitWarning"
                    class="alert alert-danger unit-warning mt-3"
                    style="display: none;"
                >
                    <i class="fa fa-triangle-exclamation me-2"></i>
                    The approved study load cannot exceed
                    <strong>15 academic units.</strong>
                </div>

                {{-- ACTIONS --}}
                <div class="form-actions mt-4">

                    <button
                        type="submit"
                        class="btn btn-generate"
                        id="generateButton"
                    >
                        <i class="fa fa-file-pdf me-2"></i>
                        Generate Official Notice
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
/* ============================================================
   PAGE AND MAIN CARD
============================================================ */

.probation-page {
    max-width: 1200px;
}

.probation-card {
    border: 1px solid #dce9df;
    border-radius: 16px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 6px 24px rgba(30, 80, 48, 0.08);
}

.probation-card .card-body {
    background: #ffffff;
}

/* ============================================================
   BACK BUTTON
============================================================ */

.btn-back {
    display: inline-flex;
    align-items: center;
    padding: 9px 15px;
    border: 1px solid #d5e3d8;
    border-radius: 9px;
    background: #ffffff;
    color: #286640;
    font-weight: 600;
    transition: background 0.2s ease, color 0.2s ease;
}

.btn-back:hover {
    background: #eaf5ed;
    color: #174c2b;
    border-color: #b9d7c1;
}

/* ============================================================
   GREEN HEADER
============================================================ */

.probation-header {
    padding: 23px 26px;
    border: 0;
    background: linear-gradient(135deg, #176b3a, #23864d);
    color: #ffffff;
}

.probation-header h4 {
    font-size: 1.3rem;
    font-weight: 700;
    letter-spacing: 0.1px;
}

.probation-header p {
    color: rgba(255, 255, 255, 0.88);
    font-size: 0.9rem;
}

.header-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.16);
    color: #ffffff;
    font-size: 1.35rem;
}

/* ============================================================
   STUDENT INFORMATION
============================================================ */

.student-information {
    padding: 20px;
    border: 1px solid #d4e8d9;
    border-radius: 12px;
    background: #f2f9f4;
}

.student-information-heading {
    padding-bottom: 12px;
    border-bottom: 1px solid #d8e9dc;
    color: #21663a;
    font-weight: 700;
}

.information-label {
    margin-bottom: 5px;
    color: #66766a;
    font-size: 0.82rem;
    font-weight: 600;
}

.information-value {
    color: #26392c;
    font-size: 0.96rem;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 11px;
    border-radius: 7px;
    background: #fff0d6;
    color: #8a5a00;
    font-size: 0.82rem;
    font-weight: 700;
}

/* ============================================================
   INSTRUCTIONS AND VALIDATION
============================================================ */

.instruction-panel {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 16px 18px;
    border: 1px solid #d0e5d6;
    border-left: 4px solid #25834a;
    border-radius: 10px;
    background: #f5faf6;
    color: #344b3a;
}

.instruction-icon {
    color: #25834a;
    font-size: 1.25rem;
    padding-top: 1px;
}

.instruction-panel strong {
    display: block;
    margin-bottom: 4px;
    color: #21663a;
}

.instruction-panel p {
    font-size: 0.9rem;
    line-height: 1.6;
}

.validation-alert {
    border-radius: 10px;
}

/* ============================================================
   SECTION HEADING
============================================================ */

.section-heading {
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-heading-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    border-radius: 10px;
    background: #e8f5eb;
    color: #218347;
    font-size: 1.1rem;
}

.section-heading h5 {
    color: #26392c;
    font-size: 1.05rem;
    font-weight: 700;
}

.section-heading small {
    color: #758078;
}

/* ============================================================
   SUBJECT TABLE
============================================================ */

.subject-table-wrapper {
    border: 1px solid #dce7de;
    border-radius: 12px;
    overflow: hidden;
}

.subject-table {
    width: 100%;
    vertical-align: middle;
}

.subject-table thead th {
    padding: 15px 14px;
    border: 0;
    border-bottom: 1px solid #cfe1d3;
    background: #e9f5ec;
    color: #225e36;
    font-size: 0.86rem;
    font-weight: 700;
    white-space: nowrap;
}

.subject-table tbody td {
    padding: 13px 14px;
    border-color: #e8eee9;
    background: #ffffff;
}

.subject-table tbody tr:not(.total-row):hover td {
    background: #f9fcf9;
}

.number-column {
    width: 65px;
}

.course-column {
    width: 245px;
}

.units-column {
    width: 125px;
}

.row-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: 1px solid #d4e6d8;
    border-radius: 8px;
    background: #f0f8f2;
    color: #286940;
    font-weight: 700;
}

.subject-table .form-control {
    min-height: 43px;
    border-color: #d7e1d9;
    border-radius: 8px;
    background: #ffffff;
    color: #2d3c30;
    font-size: 0.9rem;
}

.subject-table .form-control:focus {
    border-color: #39965a;
    box-shadow: 0 0 0 0.18rem rgba(39, 132, 72, 0.12);
}

.subject-table .form-control[readonly] {
    background: #f5f8f5;
    color: #56645a;
}

.course-code {
    text-transform: uppercase;
}

.subject-table .form-control::placeholder {
    color: #9aa59c;
    font-size: 0.84rem;
}

/* ============================================================
   TOTAL UNITS
============================================================ */

.subject-table .total-row td {
    padding: 17px 14px;
    border-top: 2px solid #cfe1d3;
    background: #edf7ef;
    color: #245f37;
    font-weight: 700;
}

#totalUnits {
    font-size: 1.15rem;
    font-weight: 800;
    color: #176b3a;
}

.unit-warning {
    border-radius: 9px;
}

/* ============================================================
   FORM ACTIONS
============================================================ */

.form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid #e4ebe5;
}

.form-actions .btn {
    min-height: 43px;
    padding: 10px 17px;
    border-radius: 9px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: background 0.2s ease, border-color 0.2s ease;
}


.btn-generate {
    border: 1px solid #218347;
    background: #218347;
    color: #ffffff;
}

.btn-generate:hover:not(:disabled) {
    border-color: #176b3a;
    background: #176b3a;
    color: #ffffff;
}

.btn-generate:disabled {
    border-color: #a5b8aa;
    background: #a5b8aa;
    color: #ffffff;
    cursor: not-allowed;
}

/* ============================================================
   RESPONSIVE LAYOUT
============================================================ */

@media (max-width: 768px) {
    .probation-page {
        margin-top: 18px !important;
    }

    .probation-card {
        border-radius: 12px;
    }

    .probation-header {
        padding: 19px;
    }

    .probation-header h4 {
        font-size: 1.08rem;
    }

    .header-icon {
        width: 43px;
        height: 43px;
        font-size: 1.15rem;
    }

    .probation-card .card-body {
        padding: 18px !important;
    }

    .student-information {
        padding: 16px;
    }

    .subject-table {
        min-width: 690px;
    }

    .form-actions {
        align-items: stretch;
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .probation-header {
        padding: 16px;
    }

    .probation-header p {
        font-size: 0.82rem;
    }

    .instruction-panel {
        padding: 13px;
    }

    .btn-back {
        font-size: 0.88rem;
    }
}
</style>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | PROSPECTUS DATA
        |--------------------------------------------------------------------------
        */

        const prospectusData =
            @json($prospectusSubjects);


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE COURSE CODE
        |--------------------------------------------------------------------------
        */

        function normalizeCourseCode(code) {
            return String(code || '')
                .toUpperCase()
                .replace(/[\s\-]+/g, '');
        }


        /*
        |--------------------------------------------------------------------------
        | FIND COURSE IN PROSPECTUS
        |--------------------------------------------------------------------------
        */

        function findProspectusSubject(code) {

            const normalizedCode =
                normalizeCourseCode(code);

            if (!normalizedCode) {
                return null;
            }

            return prospectusData.find(
                function (subject) {
                    return normalizeCourseCode(
                        subject.subject_code
                    ) === normalizedCode;
                }
            ) || null;
        }


        /*
        |--------------------------------------------------------------------------
        | GET ROW ELEMENTS
        |--------------------------------------------------------------------------
        */

        function getRowElements(row) {
            return {
                code: row.querySelector('.course-code'),
                title: row.querySelector('.subject-title'),
                units: row.querySelector('.unit-input'),
                message: row.querySelector('.course-message')
            };
        }


        /*
        |--------------------------------------------------------------------------
        | CLEAR ROW
        |--------------------------------------------------------------------------
        */

        function clearRow(row) {

            const fields = getRowElements(row);

            fields.title.value = '';
            fields.units.value = '';
            fields.message.textContent = '';
            fields.message.style.display = 'none';
        }


        /*
        |--------------------------------------------------------------------------
        | FILL DESCRIPTION AND UNITS
        |--------------------------------------------------------------------------
        */

        function fillSubject(row) {

            const fields = getRowElements(row);
            const enteredCode = fields.code.value.trim();

            if (!enteredCode) {
                clearRow(row);
                calculateTotal();
                return;
            }

            fields.code.value = enteredCode.toUpperCase();

            const subject =
                findProspectusSubject(enteredCode);

            if (subject) {

                fields.title.value =
                    subject.subject_title || '';

                fields.units.value =
                    parseFloat(subject.units || 0).toFixed(2);

                fields.message.textContent = '';
                fields.message.style.display = 'none';

            } else {

                fields.title.value = '';
                fields.units.value = '';

                fields.message.textContent =
                    'Course code not found in the Prospectus.';

                fields.message.style.display = 'block';
            }

            calculateTotal();
        }


        /*
        |--------------------------------------------------------------------------
        | CALCULATE TOTAL UNITS
        |--------------------------------------------------------------------------
        */

        function calculateTotal() {

            let total = 0;

            document.querySelectorAll('.unit-input').forEach(
                function (input) {
                    total += parseFloat(input.value) || 0;
                }
            );

            document.getElementById('totalUnits').textContent =
                total.toFixed(2);

            const unitWarning =
                document.getElementById('unitWarning');

            const generateButton =
                document.getElementById('generateButton');

            if (total > 15) {

                unitWarning.style.display = 'block';
                generateButton.disabled = true;

            } else {

                unitWarning.style.display = 'none';
                generateButton.disabled = false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | COURSE CODE INPUTS
        |--------------------------------------------------------------------------
        */

        const courseInputs =
            document.querySelectorAll('.course-code');

        courseInputs.forEach(
            function (input) {

                const row = input.closest('tr');

                input.addEventListener(
                    'input',
                    function () {

                        const code = this.value.trim();

                        if (!code) {
                            clearRow(row);
                            calculateTotal();
                            return;
                        }

                        const subject =
                            findProspectusSubject(code);

                        if (subject) {
                            fillSubject(row);
                        } else {
                            clearRow(row);
                        }
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | WHEN ADMIN LEAVES THE FIELD
                |--------------------------------------------------------------------------
                */

                input.addEventListener(
                    'blur',
                    function () {
                        fillSubject(row);
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | ENTER KEY
                |--------------------------------------------------------------------------
                */

                input.addEventListener(
                    'keydown',
                    function (event) {

                        if (event.key === 'Enter') {
                            event.preventDefault();
                            fillSubject(row);
                        }
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | RESTORE OLD INPUT
                |--------------------------------------------------------------------------
                */

                if (input.value.trim() !== '') {
                    fillSubject(row);
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMISSION VALIDATION
        |--------------------------------------------------------------------------
        */

        const probationForm =
            document.getElementById('probationForm');

        probationForm.addEventListener(
            'submit',
            function (event) {

                let hasError = false;
                let hasSubject = false;

                document.querySelectorAll('.course-code').forEach(
                    function (input) {

                        const row = input.closest('tr');
                        const code = input.value.trim();

                        if (!code) {
                            return;
                        }

                        hasSubject = true;

                        const subject =
                            findProspectusSubject(code);

                        if (!subject) {
                            fillSubject(row);
                            hasError = true;
                        }
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | REQUIRE AT LEAST ONE SUBJECT
                |--------------------------------------------------------------------------
                */

                if (!hasSubject) {

                    event.preventDefault();

                    alert(
                        'Please enter at least one course code.'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | CHECK TOTAL UNITS
                |--------------------------------------------------------------------------
                */

                let total = 0;

                document.querySelectorAll('.unit-input').forEach(
                    function (input) {
                        total += parseFloat(input.value) || 0;
                    }
                );

                if (total > 15) {

                    event.preventDefault();

                    document.getElementById('unitWarning').style.display =
                        'block';

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | INVALID COURSE CODE
                |--------------------------------------------------------------------------
                */

                if (hasError) {

                    event.preventDefault();

                    alert(
                        'Please enter valid course codes from the Prospectus.'
                    );
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL TOTAL
        |--------------------------------------------------------------------------
        */

        calculateTotal();
    }
);
</script>

@endsection