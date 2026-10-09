@extends('layouts.app')

@section('content')

<div class="container mt-4 mb-5">

    <div class="card shadow border-0">

        <!-- ===================================================== -->
        <!-- HEADER -->
        <!-- ===================================================== -->

        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

            <div>

                <h4 class="mb-0">

                    <i class="fa fa-file-contract me-2"></i>

                    Probationary Agreement Contract

                </h4>

            </div>


            <a
                href="{{ route('students.show', $student->student_id) }}"
                class="btn btn-light"
            >

                <i class="fa fa-arrow-left"></i>

                Back

            </a>

        </div>


        <!-- ===================================================== -->
        <!-- BODY -->
        <!-- ===================================================== -->

        <div class="card-body p-4">


            <!-- ================================================= -->
            <!-- STUDENT INFORMATION -->
            <!-- ================================================= -->

            <div class="row mb-4">

                <div class="col-md-12">

                    <div class="border rounded p-3 bg-light">

                        <div class="row">


                            <!-- STUDENT -->

                            <div class="col-md-4 mb-2">

                                <strong>
                                    Student:
                                </strong>

                                <br>

                                {{ $student->last_name }},
                                {{ $student->first_name }}

                                @if($student->middle_name)

                                    {{ $student->middle_name }}

                                @endif

                            </div>


                            <!-- STUDENT NUMBER -->

                            <div class="col-md-4 mb-2">

                                <strong>
                                    Student Number:
                                </strong>

                                <br>

                                {{ $student->student_no }}

                            </div>


                            <!-- STATUS -->

                            <div class="col-md-4 mb-2">

                                <strong>
                                    Academic Status:
                                </strong>

                                <br>

                                <span class="badge bg-danger">

                                    Probation

                                </span>

                            </div>


                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- ERROR MESSAGE -->
            <!-- ================================================= -->

            @if($errors->any())

                <div class="alert alert-danger">

                    <strong>
                        Please correct the following:
                    </strong>

                    <ul class="mb-0 mt-2">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- ================================================= -->
            <!-- INSTRUCTION -->
            <!-- ================================================= -->

            <div class="alert alert-info">

                <i class="fa fa-info-circle me-1"></i>

                Enter the course code for each priority subject.
                The subject description and units will automatically appear
                based on the Prospectus.

            </div>


            <!-- ================================================= -->
            <!-- FORM -->
            <!-- ================================================= -->

            <form
                method="POST"
                action="{{ route('notices.probation.preview', $student->student_id) }}"
                id="probationForm"
            >

                @csrf


                <!-- ================================================= -->
                <!-- SUBJECT TABLE -->
                <!-- ================================================= -->

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-dark">

                            <tr>

                                <th width="60">
                                    #
                                </th>

                                <th width="270">
                                    Course No.
                                </th>

                                <th>
                                    Description
                                </th>

                                <th width="150">
                                    Units
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            @for($i = 0; $i < 5; $i++)

                                @php

                                    $oldCode =
                                        old(
                                            'subject_code.' . $i,
                                            ''
                                        );

                                @endphp


                                <tr>

                                    <!-- ================================= -->
                                    <!-- NUMBER -->
                                    <!-- ================================= -->

                                    <td class="text-center fw-bold">

                                        {{ $i + 1 }}

                                    </td>


                                    <!-- ================================= -->
                                    <!-- COURSE CODE -->
                                    <!-- ================================= -->

                                    <td>

                                        <input
                                            type="text"
                                            name="subject_code[]"
                                            class="form-control course-code"
                                            placeholder="Enter course code"
                                            value="{{ $oldCode }}"
                                            autocomplete="off"
                                            data-row="{{ $i }}"
                                        >


                                        <!-- Error/message for this row -->

                                        <div
                                            class="course-message text-danger small mt-1"
                                            style="display: none;"
                                        ></div>

                                    </td>


                                    <!-- ================================= -->
                                    <!-- DESCRIPTION -->
                                    <!-- ================================= -->

                                    <td>

                                        <input
                                            type="text"
                                            class="form-control subject-title"
                                            placeholder="Automatically filled from Prospectus"
                                            readonly
                                            tabindex="-1"
                                        >

                                    </td>


                                    <!-- ================================= -->
                                    <!-- UNITS -->
                                    <!-- ================================= -->

                                    <td>

                                        <input
                                            type="text"
                                            class="form-control unit-input"
                                            placeholder="0.00"
                                            readonly
                                            tabindex="-1"
                                        >

                                    </td>


                                </tr>

                            @endfor


                            <!-- ========================================= -->
                            <!-- TOTAL UNITS -->
                            <!-- ========================================= -->

                            <tr class="table-light">

                                <td
                                    colspan="3"
                                    class="text-end"
                                >

                                    <strong>
                                        TOTAL UNITS
                                    </strong>

                                </td>


                                <td>

                                    <strong
                                        id="totalUnits"
                                        class="fs-5"
                                    >
                                        0.00
                                    </strong>

                                </td>

                            </tr>


                        </tbody>

                    </table>

                </div>


                <!-- ================================================= -->
                <!-- MAXIMUM UNIT WARNING -->
                <!-- ================================================= -->

                <div
                    id="unitWarning"
                    class="alert alert-danger"
                    style="display: none;"
                >

                    <i class="fa fa-exclamation-triangle me-1"></i>

                    The approved study load cannot exceed
                    <strong>15 academic units.</strong>

                </div>


                <!-- ================================================= -->
                <!-- BUTTONS -->
                <!-- ================================================= -->

                <div class="d-flex justify-content-end gap-2 mt-4">


                    <a
                        href="{{ route('students.show', $student->student_id) }}"
                        class="btn btn-secondary"
                    >

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="btn btn-success"
                        id="generateButton"
                    >

                        <i class="fa fa-file-pdf me-1"></i>

                        Generate Official Notice

                    </button>


                </div>


            </form>

        </div>

    </div>

</div>


<!-- ============================================================= -->
<!-- JAVASCRIPT -->
<!-- ============================================================= -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /*
        |--------------------------------------------------------------------------
        | PROSPECTUS DATA
        |--------------------------------------------------------------------------
        |
        | The controller sends the Prospectus records to this page.
        |
        | IMPORTANT:
        | We are NOT using AJAX.
        | We are NOT using a search request.
        |
        */

        const prospectusData =
            @json($prospectusSubjects);


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE COURSE CODE
        |--------------------------------------------------------------------------
        |
        | This removes spaces and hyphens and changes everything to uppercase.
        |
        | Example:
        |
        | GEE 3  -> GEE3
        | GEE-3  -> GEE3
        | gee 3  -> GEE3
        |
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

                code:
                    row.querySelector(
                        '.course-code'
                    ),

                title:
                    row.querySelector(
                        '.subject-title'
                    ),

                units:
                    row.querySelector(
                        '.unit-input'
                    ),

                message:
                    row.querySelector(
                        '.course-message'
                    )

            };

        }


        /*
        |--------------------------------------------------------------------------
        | CLEAR ROW
        |--------------------------------------------------------------------------
        */

        function clearRow(row) {

            const fields =
                getRowElements(row);


            fields.title.value = '';

            fields.units.value = '';


            fields.message.textContent = '';

            fields.message.style.display =
                'none';

        }


        /*
        |--------------------------------------------------------------------------
        | FILL DESCRIPTION AND UNITS
        |--------------------------------------------------------------------------
        */

        function fillSubject(row) {

            const fields =
                getRowElements(row);


            const enteredCode =
                fields.code.value.trim();


            /*
            | If the course code is empty,
            | clear description and units.
            */

            if (!enteredCode) {

                clearRow(row);

                calculateTotal();

                return;

            }


            /*
            | Automatically make the entered
            | course code uppercase.
            */

            fields.code.value =
                enteredCode.toUpperCase();


            /*
            | Find the course in Prospectus.
            */

            const subject =
                findProspectusSubject(
                    enteredCode
                );


            /*
            |--------------------------------------------------------------------------
            | COURSE FOUND
            |--------------------------------------------------------------------------
            */

            if (subject) {

                /*
                | Official subject description
                */

                fields.title.value =
                    subject.subject_title || '';


                /*
                | Official units
                */

                fields.units.value =
                    parseFloat(
                        subject.units || 0
                    ).toFixed(2);


                /*
                | Remove error message
                */

                fields.message.textContent =
                    '';

                fields.message.style.display =
                    'none';

            }


            /*
            |--------------------------------------------------------------------------
            | COURSE NOT FOUND
            |--------------------------------------------------------------------------
            */

            else {

                fields.title.value =
                    '';

                fields.units.value =
                    '';


                fields.message.textContent =
                    'Course code not found in the Prospectus.';


                fields.message.style.display =
                    'block';

            }


            /*
            | Update total units.
            */

            calculateTotal();

        }


        /*
        |--------------------------------------------------------------------------
        | CALCULATE TOTAL UNITS
        |--------------------------------------------------------------------------
        */

        function calculateTotal() {

            let total = 0;


            document
                .querySelectorAll('.unit-input')
                .forEach(
                    function (input) {

                        total +=
                            parseFloat(
                                input.value
                            ) || 0;

                    }
                );


            /*
            | Display total.
            */

            const totalUnits =
                document.getElementById(
                    'totalUnits'
                );


            totalUnits.textContent =
                total.toFixed(2);


            /*
            | Maximum allowed units.
            */

            const unitWarning =
                document.getElementById(
                    'unitWarning'
                );


            const generateButton =
                document.getElementById(
                    'generateButton'
                );


            if (total > 15) {

                unitWarning.style.display =
                    'block';

                generateButton.disabled =
                    true;

            }

            else {

                unitWarning.style.display =
                    'none';

                generateButton.disabled =
                    false;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | COURSE CODE INPUTS
        |--------------------------------------------------------------------------
        */

        const courseInputs =
            document.querySelectorAll(
                '.course-code'
            );


        courseInputs.forEach(
            function (input) {


                const row =
                    input.closest('tr');


                /*
                |--------------------------------------------------------------------------
                | WHEN ADMIN TYPES
                |--------------------------------------------------------------------------
                |
                | As soon as the typed course code matches
                | a Prospectus record, description and units
                | automatically appear.
                |
                */

                input.addEventListener(
                    'input',
                    function () {

                        const code =
                            this.value.trim();


                        /*
                        | Empty input
                        */

                        if (!code) {

                            clearRow(row);

                            calculateTotal();

                            return;

                        }


                        /*
                        | Look for exact Prospectus match.
                        */

                        const subject =
                            findProspectusSubject(
                                code
                            );


                        if (subject) {

                            fillSubject(row);

                        }

                        else {

                            /*
                            | Do not show an error
                            | while the admin is still typing.
                            |
                            | This prevents the annoying
                            | "not found" message from
                            | appearing while typing.
                            */

                            clearRow(row);

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | WHEN ADMIN LEAVES THE FIELD
                |--------------------------------------------------------------------------
                |
                | If the final course code is invalid,
                | show the message.
                |
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
                |
                | Pressing Enter will process the course code
                | without submitting the form.
                |
                */

                input.addEventListener(
                    'keydown',
                    function (event) {

                        if (
                            event.key === 'Enter'
                        ) {

                            event.preventDefault();

                            fillSubject(row);

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | RESTORE OLD INPUT
                |--------------------------------------------------------------------------
                |
                | If Laravel sends the user back because
                | of a validation error, restore the
                | description and units.
                |
                */

                if (
                    input.value.trim() !== ''
                ) {

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
            document.getElementById(
                'probationForm'
            );


        probationForm.addEventListener(
            'submit',
            function (event) {

                let hasError = false;

                let hasSubject = false;


                /*
                | Check all five rows.
                */

                document
                    .querySelectorAll(
                        '.course-code'
                    )
                    .forEach(
                        function (input) {

                            const row =
                                input.closest('tr');


                            const code =
                                input.value.trim();


                            /*
                            | Empty row is allowed.
                            */

                            if (!code) {

                                return;

                            }


                            hasSubject = true;


                            /*
                            | Check Prospectus.
                            */

                            const subject =
                                findProspectusSubject(
                                    code
                                );


                            /*
                            | Invalid course code.
                            */

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


                document
                    .querySelectorAll(
                        '.unit-input'
                    )
                    .forEach(
                        function (input) {

                            total +=
                                parseFloat(
                                    input.value
                                ) || 0;

                        }
                    );


                if (total > 15) {

                    event.preventDefault();

                    document
                        .getElementById(
                            'unitWarning'
                        )
                        .style.display =
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