@extends('layouts.app')

@section('content')

<style>
    .school-year-page {
        max-width: 1100px;
        margin: 0 auto;
    }

    .school-year-header {
        background: #0e8203;
        color: white;
        padding: 24px 28px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
    }

    .school-year-header h3 {
        font-weight: 700;
        margin-bottom: 5px;
    }

    .school-year-card {
        background: white;
        border: 1px solid #dfe8df;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .05);
        margin-bottom: 12px;
        overflow: hidden;
    }

    .school-year-card-header {
        padding: 18px 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .school-year-name {
        font-size: 19px;
        font-weight: 700;
        color: #212529;
        margin: 0;
    }

    .school-year-name i {
        color: #0e8203;
    }

    .school-year-form {
        background: white;
        padding: 24px;
        border-radius: 12px;
        border: 1px solid #dfe8df;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .05);
        margin-bottom: 25px;
    }

    .school-year-form h5 {
        font-weight: 700;
        margin-bottom: 18px;
    }

    .school-year-form h5 i {
        color: #0e8203;
    }

    .form-label {
        font-weight: 600;
    }

    .school-year-input {
        border: 1px solid #ced8ce;
    }

    .school-year-input:focus {
        border-color: #0e8203;
        box-shadow: 0 0 0 .2rem rgba(14, 130, 3, .15);
    }

    .btn-school-green {
        background: #0e8203;
        border-color: #0e8203;
        color: white;
        font-weight: 600;
    }

    .btn-school-green:hover,
    .btn-school-green:focus {
        background: #0b6d02;
        border-color: #0b6d02;
        color: white;
    }

    .btn-outline-school-green {
        color: #0e8203;
        border: 1px solid #0e8203;
        background: white;
        font-weight: 600;
    }

    .btn-outline-school-green:hover,
    .btn-outline-school-green:focus {
        background: #0e8203;
        border-color: #0e8203;
        color: white;
    }

    .existing-years-title {
        color: #212529;
    }

    .existing-years-title i {
        color: #0e8203;
    }

    .school-year-count {
        background: #0e8203;
        color: white;
        font-size: 13px;
        padding: 7px 11px;
        border-radius: 20px;
    }

    /*
    |--------------------------------------------------------------------------
    | SCHOOL YEAR DISPLAY
    |--------------------------------------------------------------------------
    */

    .school-year-list {
        width: 100%;
    }

    .school-year-item {
        border: 1px solid #dfe8df;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 12px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
        background: white;
    }

    .school-year-row {
        padding: 18px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
        background: white;
    }

    .school-year-content {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .school-year-content i {
        color: #0e8203;
    }

    .school-year-text {
        font-size: 18px;
        font-weight: 700;
        color: #212529;
    }

    .existing-school-years-toggle {
        background: #f4faf3;
        border: 1px solid #cfe6cc;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
    }

    .existing-school-years-info {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .existing-school-years-info i {
        color: #0e8203;
        font-size: 19px;
    }

    .existing-school-years-info strong {
        color: #212529;
    }

    .existing-school-years-list {
        transition: opacity .2s ease;
    }

    .existing-school-years-list.d-none {
        display: none !important;
    }

    .school-year-edit-button {
        flex-shrink: 0;
    }

    .empty-state {
        text-align: center;
        padding: 45px 20px;
        background: white;
        border: 1px dashed #b9ccb7;
        border-radius: 12px;
        color: #6c757d;
    }

    .empty-state i {
        color: #0e8203;
    }

    .modal-header-school-green {
        background: #0e8203;
        color: white;
    }

    .modal .form-control:focus {
        border-color: #0e8203;
        box-shadow: 0 0 0 .2rem rgba(14, 130, 3, .15);
    }

    .validation-message {
        color: #dc3545;
        font-size: 13px;
        margin-top: 6px;
        display: none;
    }

    @media (max-width: 576px) {

        .existing-school-years-toggle {
            align-items: flex-start;
        }

        .existing-school-years-toggle .btn {
            width: 100%;
        }

        .school-year-header {
            padding: 20px;
        }

        .school-year-row {
            align-items: flex-start;
        }

        .school-year-row .btn {
            width: 100%;
        }

        .school-year-text {
            font-size: 16px;
        }

        .school-year-content {
            gap: 7px;
        }
    }
</style>

<div class="school-year-page">


<div class="school-year-header">

    <h3>
        <i class="fa fa-calendar-days me-2"></i>
        SCHOOL YEAR MANAGEMENT
    </h3>

</div>


@if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

        <i class="fa fa-circle-check me-2"></i>

        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

@endif


@if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show">

        <i class="fa fa-circle-exclamation me-2"></i>

        {{ session('error') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

@endif


{{-- ADD SCHOOL YEAR --}}

<div class="school-year-form">

    <h5>

        <i class="fa fa-plus-circle me-2"></i>

        ADD SCHOOL YEAR

    </h5>


    <form
        action="{{ route('semesters.store') }}"
        method="POST"
        id="addSchoolYearForm"
    >

        @csrf


        <div class="row g-3 align-items-end">

            <div class="col-md-8">

                <label
                    for="school_year"
                    class="form-label"
                >
                    School Year
                </label>


                <input
                    type="text"
                    name="school_year"
                    id="school_year"
                    class="form-control school-year-input @error('school_year') is-invalid @enderror"
                    placeholder="e.g. 2026-2027"
                    value="{{ old('school_year') }}"
                    pattern="[0-9]{4}-[0-9]{4}"
                    maxlength="9"
                    inputmode="numeric"
                    autocomplete="off"
                    required
                >


                <div
                    id="schoolYearValidation"
                    class="validation-message"
                ></div>


                @error('school_year')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            <div class="col-md-4">

                <button
                    type="submit"
                    class="btn btn-school-green w-100"
                >

                    <i class="fa fa-plus me-1"></i>

                    ADD SCHOOL YEAR

                </button>

            </div>

        </div>

    </form>

</div>


{{-- EXISTING SCHOOL YEARS HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-3">

    <h5 class="fw-bold mb-0 existing-years-title">

        <i class="fa fa-list me-2"></i>

        SCHOOL YEARS

    </h5>


    <span class="school-year-count">

        {{ $schoolYears->count() }}

    </span>

</div>


{{-- SCHOOL YEARS --}}

@php

    /*
    | Keep school years organized from oldest to newest.
    */

    $organizedSchoolYears = $schoolYears

        ->sortBy(function ($item) {

            return (int) substr(
                trim($item['school_year']),
                0,
                4
            );

        })

        ->values();

@endphp


@if($organizedSchoolYears->count() > 0)


    {{-- SHOW / HIDE SCHOOL YEARS --}}

    <div class="existing-school-years-toggle">

        <div class="existing-school-years-info">

            <i class="fa fa-calendar-days"></i>

            <strong>
                School Years
            </strong>

        </div>


        <button
            type="button"
            class="btn btn-outline-school-green btn-sm"
            id="toggleExistingSchoolYears"
            aria-expanded="false"
            aria-controls="existingSchoolYearsList"
        >

            <i class="fa fa-eye me-1"></i>

            SHOW

        </button>

    </div>


    <div
        id="existingSchoolYearsList"
        class="existing-school-years-list d-none"
    >


        <div class="school-year-list">


            @foreach($organizedSchoolYears as $item)


                <div
                    class="school-year-item"
                    id="schoolYearItem{{ $loop->index }}"
                >


                    <div class="school-year-row">


                        <div class="school-year-content">

                            <i class="fa fa-calendar-days"></i>


                            <span class="school-year-text">

                                {{ $item['school_year'] }}

                            </span>

                        </div>


                        <button
                            type="button"
                            class="btn btn-outline-school-green btn-sm school-year-edit-button"
                            data-bs-toggle="modal"
                            data-bs-target="#editSchoolYear{{ $loop->index }}"
                        >

                            <i class="fa fa-pen me-1"></i>

                            EDIT

                        </button>


                    </div>


                </div>


                {{-- EDIT SCHOOL YEAR MODAL --}}

                <div
                    class="modal fade"
                    id="editSchoolYear{{ $loop->index }}"
                    tabindex="-1"
                    aria-hidden="true"
                >


                    <div class="modal-dialog modal-dialog-centered">


                        <div class="modal-content">


                            <form
                                action="{{ route('semesters.update', ['schoolYear' => $item['school_year']]) }}"
                                method="POST"
                                class="edit-school-year-form"
                            >

                                @csrf

                                @method('PUT')


                                <div class="modal-header modal-header-school-green">

                                    <h5 class="modal-title fw-bold">

                                        <i class="fa fa-pen me-2"></i>

                                        EDIT SCHOOL YEAR

                                    </h5>


                                    <button
                                        type="button"
                                        class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"
                                    ></button>

                                </div>


                                <div class="modal-body">

                                    <label class="form-label">

                                        School Year

                                    </label>


                                    <input
                                        type="text"
                                        name="school_year"
                                        class="form-control school-year-input edit-school-year-input"
                                        value="{{ $item['school_year'] }}"
                                        pattern="[0-9]{4}-[0-9]{4}"
                                        maxlength="9"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        required
                                    >


                                    <div class="validation-message">
                                    </div>


                                    <small class="text-muted d-block mt-2">

                                        Both semesters will retain their
                                        existing records and be linked to
                                        the updated school year.

                                    </small>

                                </div>


                                <div class="modal-footer">

                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal"
                                    >

                                        CANCEL

                                    </button>


                                    <button
                                        type="submit"
                                        class="btn btn-school-green"
                                    >

                                        <i class="fa fa-save me-1"></i>

                                        SAVE CHANGES

                                    </button>

                                </div>


                            </form>

                        </div>

                    </div>

                </div>


            @endforeach


        </div>

    </div>


@else


    <div class="empty-state">

        <i class="fa fa-calendar-xmark fa-3x mb-3"></i>


        <h5 class="fw-bold">

            No School Years Yet

        </h5>


        <p class="mb-0">

            Add a school year using the form above.

        </p>

    </div>

@endif


<script>
document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | SHOW / HIDE SCHOOL YEARS
    |--------------------------------------------------------------------------
    */

    const toggleExistingSchoolYears =
        document.getElementById('toggleExistingSchoolYears');


    const existingSchoolYearsList =
        document.getElementById('existingSchoolYearsList');


    if (
        toggleExistingSchoolYears &&
        existingSchoolYearsList
    ) {

        toggleExistingSchoolYears.addEventListener(
            'click',
            function () {

                const isHidden =
                    existingSchoolYearsList.classList.contains(
                        'd-none'
                    );


                existingSchoolYearsList.classList.toggle(
                    'd-none',
                    !isHidden
                );


                this.setAttribute(
                    'aria-expanded',
                    isHidden ? 'true' : 'false'
                );


                this.innerHTML = isHidden

                    ? '<i class="fa fa-eye-slash me-1"></i> HIDE'

                    : '<i class="fa fa-eye me-1"></i> SHOW';

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SCHOOL YEAR INPUT VALIDATION
    |--------------------------------------------------------------------------
    | Allows only:
    | 0-9 and -
    |
    | Example:
    | 2026-2027
    |--------------------------------------------------------------------------
    */

    function sanitizeSchoolYearInput(input) {

        let value = input.value;


        // Remove everything except numbers and hyphen.

        value =
            value.replace(/[^0-9-]/g, '');


        // Keep only the first hyphen.

        const firstHyphen =
            value.indexOf('-');


        if (firstHyphen !== -1) {

            value =
                value.substring(
                    0,
                    firstHyphen + 1
                ) +
                value
                    .substring(firstHyphen + 1)
                    .replace(/-/g, '');

        }


        // Maximum format: YYYY-YYYY

        value =
            value.substring(0, 9);


        input.value = value;

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SCHOOL YEAR FORMAT AND CONSECUTIVE YEARS
    |--------------------------------------------------------------------------
    */

    function validateSchoolYear(
        input,
        messageElement
    ) {

        const value =
            input.value.trim();


        messageElement.style.display =
            'none';


        messageElement.textContent =
            '';


        input.classList.remove(
            'is-invalid'
        );


        if (value === '') {

            return false;

        }


        if (!/^\d{4}-\d{4}$/.test(value)) {

            messageElement.textContent =
                'Use the format YYYY-YYYY, such as 2026-2027.';


            messageElement.style.display =
                'block';


            input.classList.add(
                'is-invalid'
            );


            return false;

        }


        const parts =
            value.split('-');


        const startYear =
            parseInt(parts[0], 10);


        const endYear =
            parseInt(parts[1], 10);


        if (endYear !== startYear + 1) {

            messageElement.textContent =
                'The school year must contain consecutive years, such as 2026-2027.';


            messageElement.style.display =
                'block';


            input.classList.add(
                'is-invalid'
            );


            return false;

        }


        return true;

    }


    /*
    |--------------------------------------------------------------------------
    | ADD SCHOOL YEAR INPUT
    |--------------------------------------------------------------------------
    */

    const addInput =
        document.getElementById(
            'school_year'
        );


    const addValidation =
        document.getElementById(
            'schoolYearValidation'
        );


    if (
        addInput &&
        addValidation
    ) {


        addInput.addEventListener(
            'input',
            function () {

                sanitizeSchoolYearInput(
                    this
                );


                validateSchoolYear(
                    this,
                    addValidation
                );

            }
        );


        addInput.addEventListener(
            'paste',
            function () {

                setTimeout(() => {

                    sanitizeSchoolYearInput(
                        this
                    );


                    validateSchoolYear(
                        this,
                        addValidation
                    );

                }, 0);

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ADD SCHOOL YEAR FORM SUBMISSION
    |--------------------------------------------------------------------------
    */

    const addForm =
        document.getElementById(
            'addSchoolYearForm'
        );


    if (
        addForm &&
        addInput &&
        addValidation
    ) {


        addForm.addEventListener(
            'submit',
            function (event) {

                sanitizeSchoolYearInput(
                    addInput
                );


                if (
                    !validateSchoolYear(
                        addInput,
                        addValidation
                    )
                ) {

                    event.preventDefault();

                    addInput.focus();

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | EDIT SCHOOL YEAR INPUTS
    |--------------------------------------------------------------------------
    */

    const editInputs =
        document.querySelectorAll(
            '.edit-school-year-input'
        );


    editInputs.forEach(
        function (input) {


            const validation =
                input.parentElement.querySelector(
                    '.validation-message'
                );


            input.addEventListener(
                'input',
                function () {

                    sanitizeSchoolYearInput(
                        this
                    );


                    validateSchoolYear(
                        this,
                        validation
                    );

                }
            );


            input.addEventListener(
                'paste',
                function () {

                    setTimeout(() => {

                        sanitizeSchoolYearInput(
                            this
                        );


                        validateSchoolYear(
                            this,
                            validation
                        );

                    }, 0);

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | EDIT SCHOOL YEAR FORM SUBMISSION
    |--------------------------------------------------------------------------
    */

    const editForms =
        document.querySelectorAll(
            '.edit-school-year-form'
        );


    editForms.forEach(
        function (form) {


            form.addEventListener(
                'submit',
                function (event) {


                    const input =
                        form.querySelector(
                            '.edit-school-year-input'
                        );


                    const validation =
                        form.querySelector(
                            '.validation-message'
                        );


                    sanitizeSchoolYearInput(
                        input
                    );


                    if (
                        !validateSchoolYear(
                            input,
                            validation
                        )
                    ) {

                        event.preventDefault();

                        input.focus();

                    }

                }
            );

        }
    );

});
</script>

@endsection
