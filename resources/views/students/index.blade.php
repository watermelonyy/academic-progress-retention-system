@extends('layouts.app')

@section('content')

<style>

    /* =========================================================
       PAGE
    ========================================================= */

    .students-page {
        max-width: 1250px;
        margin: 0 auto;
        padding-bottom: 30px;
    }


    /* =========================================================
       PAGE HEADER
    ========================================================= */

    .students-header {
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 14px;
        padding: 24px 26px;
        margin-bottom: 20px;

        box-shadow: 0 3px 14px rgba(0, 0, 0, 0.05);
    }

    .students-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .students-title-area {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .students-title-icon {
        width: 52px;
        height: 52px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 12px;

        background: #eaf3ff;
        color: #0e8203;

        font-size: 21px;
        flex-shrink: 0;
    }

    .students-title {
        margin: 0;
        font-size: 25px;
        font-weight: 700;
        color: #212529;
        letter-spacing: -0.3px;
    }

    .students-subtitle {
        margin: 4px 0 0;
        color: #6c757d;
        font-size: 14px;
    }


    /* =========================================================
       ADD STUDENT BUTTON
    ========================================================= */

    .add-student-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        min-height: 44px;
        padding: 10px 18px;

        border-radius: 9px;

        font-weight: 600;

        box-shadow: 0 3px 8px rgba(39, 220, 7, 0.18);

        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .add-student-btn:hover {
        transform: translateY(-2px);

        box-shadow:
            0 6px 14px rgba(3, 171, 17, 0.22);
    }


    /* =========================================================
       ALERTS
    ========================================================= */

    .students-alert {
        border: none;
        border-radius: 10px;
        padding: 13px 16px;
        margin-bottom: 18px;

        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .students-alert ul {
        padding-left: 20px;
    }


    /* =========================================================
       SEARCH CARD
    ========================================================= */

    .search-card {
        background: #ffffff;

        border: 1px solid #e9ecef;
        border-radius: 12px;

        margin-bottom: 20px;

        box-shadow: 0 3px 14px rgba(0, 0, 0, 0.045);
    }

    .search-card-body {
        padding: 18px;
    }


    /* =========================================================
       FILTER AREA
    ========================================================= */

    .filter-area {
        position: relative;

        display: flex;
        align-items: center;
        justify-content: flex-start;

        margin-bottom: 13px;
    }

    .filter-button {
        width: 38px;
        height: 38px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border: 1px solid #dee2e6;
        border-radius: 8px;

        background: #ffffff;
        color: #495057;

        font-size: 15px;

        cursor: pointer;

        transition:
            background-color 0.2s ease,
            border-color 0.2s ease,
            color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .filter-button:hover,
    .filter-button.active {
        background: #eaf3ff;
        border-color: #b9d7ff;
        color:#0e8203;

        box-shadow:
            0 2px 7px rgba(13, 110, 253, 0.12);
    }

    .filter-button.has-filter {
        background: #0e8203;
        border-color: #0d6efd;
        color: #ffffff;
    }


    /* =========================================================
       FILTER POPUP
    ========================================================= */

    .filter-dropdown {
        position: absolute;

        top: calc(100% + 8px);
        left: 0;

        width: 220px;

        background: #ffffff;

        border: 1px solid #e2e6ea;

        border-radius: 10px;

        padding: 8px;

        box-shadow:
            0 8px 25px rgba(0, 0, 0, 0.12);

        z-index: 1000;

        display: none;

        animation: filterDropdownIn 0.15s ease;
    }

    .filter-dropdown.show {
        display: block;
    }

    @keyframes filterDropdownIn {

        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }

    }


    .filter-dropdown-header {
        padding: 8px 10px 7px;

        font-size: 12px;
        font-weight: 700;

        color: #6c757d;

        border-bottom: 1px solid #f0f1f3;

        margin-bottom: 5px;
    }


    .filter-option {
        width: 100%;

        display: flex;
        align-items: center;

        gap: 9px;

        border: none;

        background: transparent;

        color: #343a40;

        padding: 9px 10px;

        border-radius: 7px;

        text-align: left;

        font-size: 13px;

        cursor: pointer;

        transition:
            background-color 0.15s ease,
            color 0.15s ease;
    }

    .filter-option:hover {
        background: #f4f8ff;
        color: #0d6efd;
    }

    .filter-option.selected {
        background: #eaf3ff;
        color: #0d6efd;
        font-weight: 600;
    }

    .filter-option-icon {
        width: 17px;

        text-align: center;

        font-size: 12px;
    }


    /* =========================================================
       ACTIVE FILTER TEXT
    ========================================================= */

    .active-filter-text {
        display: none;

        margin-left: 9px;

        font-size: 12px;

        color: #6c757d;
    }

    .active-filter-text.show {
        display: inline-block;
    }

    .active-filter-text strong {
        color: #0d6efd;
        font-weight: 600;
    }


    /* =========================================================
       SEARCH LABEL
    ========================================================= */

    .search-label {
        display: block;

        font-size: 13px;
        font-weight: 700;

        color: #495057;

        margin-bottom: 8px;
    }


    /* =========================================================
       SEARCH
    ========================================================= */

    .search-wrapper {
        display: flex;
        align-items: stretch;
        gap: 8px;
    }

    .search-input-wrapper {
        display: flex;
        align-items: center;

        flex: 1;

        min-width: 0;

        border: 1px solid #ced4da;
        border-radius: 9px;

        background: #ffffff;

        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .search-input-wrapper:focus-within {
        border-color: #13e333;

        box-shadow:
            0 0 0 3px rgba(13, 253, 25, 0.1);
    }

    .search-icon {
        width: 45px;
        min-width: 45px;

        display: flex;
        align-items: center;
        justify-content: center;

        color: #6c757d;
    }

    .search-input {
        flex: 1;

        min-width: 0;

        border: none !important;
        outline: none !important;

        box-shadow: none !important;

        padding: 11px 12px 11px 0;

        font-size: 14px;
    }

    .search-input::placeholder {
        color: #adb5bd;
    }

    .search-clear-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 6px;

        padding: 0 14px;

        border-radius: 8px;

        white-space: nowrap;
    }

    .search-submit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        min-width: 105px;

        border-radius: 9px;

        font-weight: 600;
    }


    /* =========================================================
       STUDENT CARD
    ========================================================= */

    .student-card {
        background: #ffffff;

        border: 1px solid #e9ecef;
        border-radius: 14px;

        overflow: hidden;

        box-shadow: 0 3px 16px rgba(0, 0, 0, 0.05);
    }


    /* =========================================================
       STUDENT CARD HEADER
    ========================================================= */

    .student-card-header {
        padding: 17px 21px;

        background: #ffffff;

        border-bottom: 1px solid #e9ecef;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 15px;
    }

    .student-card-heading {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .student-card-heading-icon {
        width: 34px;
        height: 34px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 8px;

        background: #f0f6ff;
        color: #0e8203;

        font-size: 14px;
    }

    .student-card-title {
        margin: 0;

        font-size: 16px;
        font-weight: 700;

        color: #212529;
    }

    .student-count {
        background: #eaf3ff;
        color: #0d6efd;

        border: 1px solid #d5e7ff;

        border-radius: 20px;

        padding: 6px 11px;

        font-size: 12px;
        font-weight: 700;

        white-space: nowrap;
    }


    /* =========================================================
       STUDENT LIST
    ========================================================= */

    .student-list {
        width: 100%;
    }


    /* =========================================================
       STUDENT ITEM
    ========================================================= */

    .student-item {
        display: flex;
        align-items: center;

        width: 100%;

        padding: 16px 21px;

        text-decoration: none;

        color: #212529;

        border-bottom: 1px solid #f0f1f3;

        background: #ffffff;

        transition:
            background-color 0.2s ease,
            padding-left 0.2s ease;
    }

    .student-item:last-child {
        border-bottom: none;
    }

    .student-item:hover {
        background: #f8fbff;
        padding-left: 26px;
    }


    /* =========================================================
       STUDENT AVATAR
    ========================================================= */

    .student-avatar {
        width: 44px;
        height: 44px;

        min-width: 44px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 11px;

        background: #eef5ff;
        color: #0e8203;

        font-size: 17px;

        margin-right: 14px;

        transition:
            background-color 0.2s ease,
            color 0.2s ease;
    }

    .student-item:hover .student-avatar {
        background: #0e8203;
        color: #ffffff;
    }


    /* =========================================================
       STUDENT NAME
    ========================================================= */

    .student-name {
        flex: 1;

        min-width: 0;

        font-size: 15px;
        font-weight: 600;

        color: #343a40;

        line-height: 1.4;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .student-item:hover .student-name {
        color: #0e8203;
    }


    /* =========================================================
       VIEW RECORD
    ========================================================= */

    .student-view-text {
        display: inline-flex;
        align-items: center;

        gap: 7px;

        margin-right: 10px;

        font-size: 12px;
        font-weight: 600;

        color: #6c757d;

        transition: color 0.2s ease;
    }

    .student-item:hover .student-view-text {
        color: #0d6efd;
    }


    /* =========================================================
       ARROW
    ========================================================= */

    .student-arrow {
        width: 32px;
        height: 32px;

        min-width: 32px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #f8f9fa;

        color: #6c757d;

        font-size: 11px;

        transition:
            background-color 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease;
    }

    .student-item:hover .student-arrow {
        background: #0d6efd;
        color: #ffffff;

        transform: translateX(3px);
    }


    /* =========================================================
       EMPTY STATES
    ========================================================= */

    .empty-state,
    .search-empty-state {
        text-align: center;

        padding: 65px 20px;
    }

    .search-empty-state {
        display: none;
    }

    .empty-icon,
    .search-empty-icon {
        width: 70px;
        height: 70px;

        margin: 0 auto;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #f4f6f8;

        color: #adb5bd;

        font-size: 27px;
    }

    .empty-title,
    .search-empty-title {
        margin-top: 18px;
        margin-bottom: 7px;

        font-size: 18px;
        font-weight: 700;

        color: #343a40;
    }

    .empty-description,
    .search-empty-description {
        max-width: 420px;

        margin: 0 auto 18px;

        color: #6c757d;

        font-size: 14px;

        line-height: 1.6;
    }

    .empty-action {
        border-radius: 8px;
        font-weight: 600;
    }


    /* ============================================================
       PRIORITY ALERT ICON
    ============================================================ */

    .student-priority-alert {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 34px;
        height: 34px;
        min-width: 34px;

        margin-right: 10px;

        border-radius: 50%;

        background: #fff3cd;
        color: #dc3545;

        border: 1px solid #f1d58a;

        font-size: 14px;

        cursor: pointer;

        transition:
            background-color 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .student-priority-alert:hover {
        background: #dc3545;
        color: #ffffff;

        transform: scale(1.08);

        box-shadow:
            0 4px 10px rgba(220, 53, 69, 0.25);
    }

    .student-priority-alert:focus {
        outline: none;

        box-shadow:
            0 0 0 3px rgba(220, 53, 69, 0.15);
    }


    /* ============================================================
       RESIDENCY WARNING ICON
    ============================================================ */

    .student-residency-alert {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 34px;
        height: 34px;
        min-width: 34px;

        margin-right: 10px;

        border-radius: 50%;

        background: #fff8e1;
        color: #d39e00;

        border: 1px solid #f0d98c;

        font-size: 14px;

        cursor: help;

        transition:
            background-color 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .student-residency-alert:hover {
        background: #d39e00;
        color: #ffffff;

        transform: scale(1.08);

        box-shadow:
            0 4px 10px rgba(211, 158, 0, 0.22);
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 768px) {

        .students-page {
            padding-bottom: 20px;
        }

        .students-header {
            padding: 20px;
        }

        .students-header-content {
            align-items: stretch;
        }

        .students-title-area {
            align-items: flex-start;
        }

        .students-title-icon {
            width: 46px;
            height: 46px;
        }

        .students-title {
            font-size: 21px;
        }

        .add-student-btn {
            width: 100%;
        }

        .search-card-body {
            padding: 14px;
        }

        .search-wrapper {
            flex-direction: column;
        }

        .search-input-wrapper {
            width: 100%;
        }

        .search-submit-btn,
        .search-clear-btn {
            min-height: 42px;
            width: 100%;
        }

        .filter-dropdown {
            width: 215px;
        }

        .student-card-header {
            padding: 15px 16px;
        }

        .student-item {
            padding: 15px 16px;
        }

        .student-item:hover {
            padding-left: 19px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            min-width: 40px;

            margin-right: 12px;

            font-size: 15px;
        }

        .student-name {
            font-size: 14px;
        }

        .student-view-text {
            display: none;
        }

        .student-arrow {
            width: 30px;
            height: 30px;
            min-width: 30px;
        }

        .student-priority-alert {
            width: 31px;
            height: 31px;
            min-width: 31px;

            margin-right: 7px;

            font-size: 12px;
        }

        .student-residency-alert {
            width: 31px;
            height: 31px;
            min-width: 31px;

            margin-right: 7px;

            font-size: 12px;
        }

        .empty-state,
        .search-empty-state {
            padding: 55px 18px;
        }

    }


    @media (max-width: 400px) {

        .students-title {
            font-size: 19px;
        }

        .students-subtitle {
            font-size: 13px;
        }

        .student-card-title {
            font-size: 15px;
        }

        .student-count {
            font-size: 11px;
            padding: 5px 9px;
        }

    }

</style>


<div class="students-page">


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="students-header">

    <div class="students-header-content">


        <div class="students-title-area">

            <div class="students-title-icon">

                <i class="fa fa-users"></i>

            </div>


            <div>

                <h3 class="students-title">

                    Student List

                </h3>

            </div>

        </div>


        <a
            href="{{ route('students.create') }}"
            class="btn btn-primary add-student-btn"
        >

            <i class="fa fa-plus"></i>

            Add Student

        </a>


    </div>

</div>


<!-- =========================================================
     ALERT MESSAGES
========================================================= -->

@if(session('success'))

    <div
        class="alert alert-success alert-dismissible fade show students-alert"
        role="alert"
    >

        <i class="fa fa-check-circle me-2"></i>

        {{ session('success') }}


        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

@endif


@if(session('error'))

    <div
        class="alert alert-danger alert-dismissible fade show students-alert"
        role="alert"
    >

        <i class="fa fa-exclamation-circle me-2"></i>

        {{ session('error') }}


        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

@endif


@if($errors->any())

    <div
        class="alert alert-danger alert-dismissible fade show students-alert"
        role="alert"
    >

        <strong>

            <i class="fa fa-exclamation-triangle me-2"></i>

            Validation Error

        </strong>


        <ul class="mb-0 mt-2">

            @foreach($errors->all() as $error)

                <li>

                    {{ $error }}

                </li>

            @endforeach

        </ul>


        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

@endif


<!-- =========================================================
     SEARCH CARD
========================================================= -->

<div class="search-card">

    <div class="search-card-body">

        <form
            method="GET"
            action="{{ route('students.index') }}"
            id="studentSearchForm"
        >


            <!-- =================================================
                 FILTER ICON
            ================================================== -->

            <div class="filter-area">


                <button
                    type="button"
                    class="filter-button"
                    id="filterButton"
                    title="Filter by academic status"
                    aria-label="Filter by academic status"
                    aria-expanded="false"
                >

                    <i class="fa fa-filter"></i>

                </button>


                <span
                    class="active-filter-text"
                    id="activeFilterText"
                >

                    Filter:

                    <strong id="activeFilterName"></strong>

                </span>


                <!-- =================================================
                     FILTER POPUP
                ================================================== -->

                <div
                    class="filter-dropdown"
                    id="filterDropdown"
                >

                    <div class="filter-dropdown-header">

                        Academic Status / Residency

                    </div>


                    <button
                        type="button"
                        class="filter-option selected"
                        data-status="all"
                    >

                        <span class="filter-option-icon">

                            <i class="fa fa-list"></i>

                        </span>

                        <span>

                            All Statuses

                        </span>

                    </button>


                    <button
                        type="button"
                        class="filter-option"
                        data-status="Regular"
                    >

                        <span class="filter-option-icon">

                            <i class="fa fa-user-check"></i>

                        </span>

                        <span>

                            Regular

                        </span>

                    </button>


                    <button
                        type="button"
                        class="filter-option"
                        data-status="Academic Warning"
                    >

                        <span class="filter-option-icon">

                            <i class="fa fa-triangle-exclamation"></i>

                        </span>

                        <span>

                            Academic Warning

                        </span>

                    </button>


                    <button
                        type="button"
                        class="filter-option"
                        data-status="Probation"
                    >

                        <span class="filter-option-icon">

                            <i class="fa fa-circle-exclamation"></i>

                        </span>

                        <span>

                            Probation

                        </span>

                    </button>


                    <button
                        type="button"
                        class="filter-option"
                        data-status="Residency Risk"
                    >

                        <span class="filter-option-icon">

                            <i class="fa fa-clock"></i>

                        </span>

                        <span>

                            Residency Risk

                        </span>

                    </button>


                    <button
                        type="button"
                        class="filter-option"
                        data-status="Shifting Out"
                    >

                        <span class="filter-option-icon">

                            <i class="fa fa-right-from-bracket"></i>

                        </span>

                        <span>

                            Shifting Out

                        </span>

                    </button>

                </div>

            </div>



            <!-- =================================================
                 SEARCH INPUT
            ================================================== -->

            <div class="search-wrapper">


                <div class="search-input-wrapper">

                    <div class="search-icon">

                        <i class="fa fa-search"></i>

                    </div>


                    <input
                        type="text"
                        name="search"
                        id="studentSearch"
                        value="{{ request('search') }}"
                        class="form-control search-input"
                        placeholder="Search"
                        autocomplete="off"
                    >

                </div>


                <!-- SEARCH -->

                <button
                    type="submit"
                    class="btn btn-primary search-submit-btn"
                    id="searchSubmitButton"
                >

                    <i class="fa fa-search"></i>

                    Search

                </button>


            </div>

        </form>

    </div>

</div>


<!-- =========================================================
     STUDENT LIST
========================================================= -->

<div class="student-card">


    <div class="student-card-header">


        <div class="student-card-heading">

            <div class="student-card-heading-icon">

                <i class="fa fa-user-graduate"></i>

            </div>


            <h5 class="student-card-title">

                Students

            </h5>

        </div>


        <span
            class="student-count"
            id="studentCount"
        >

            {{ $students->count() }}

            {{ $students->count() === 1 ? 'Student' : 'Students' }}

        </span>


    </div>


    <div>


        @if($students->count() > 0)


            <div
                class="student-list"
                id="studentList"
            >


                @foreach($students as $student)


                    @php

                        $fullName = trim(
                            ($student->last_name ?? '') .
                            ', ' .
                            ($student->first_name ?? '') .
                            ' ' .
                            ($student->middle_name ?? '')
                        );

                        $studentStatus =
                            optional($student->latestStatus)
                                ->academic_status
                            ?? 'No Status';

                    @endphp


                    <a
                        href="{{ route(
                            'students.show',
                            $student->student_id
                        ) }}"
                        class="student-item"
                        data-student-name="{{ strtolower($fullName) }}"
                        data-student-status="{{ $studentStatus }}"
                        data-student-residency-risk="{{ $student->residency_risk ? '1' : '0' }}"
                    >


                        <div class="student-avatar">

                            <i class="fa fa-user"></i>

                        </div>


                        <div class="student-name">

                            {{ $fullName }}

                        </div>


                        @if(
                            $student->latestStatus &&
                            $student->latestStatus->priority_alert
                        )

                            <span
                                class="student-priority-alert"
                                title="Priority Alert: View academic performance history"
                                role="button"
                                tabindex="0"
                                onclick="
                                    event.preventDefault();
                                    event.stopPropagation();

                                    window.location.href =
                                        '{{ route(
                                            'students.priorityAlert',
                                            $student->student_id
                                        ) }}';
                                "
                                onkeydown="
                                    if (
                                        event.key === 'Enter' ||
                                        event.key === ' '
                                    ) {
                                        event.preventDefault();
                                        event.stopPropagation();

                                        window.location.href =
                                            '{{ route(
                                                'students.priorityAlert',
                                                $student->student_id
                                            ) }}';
                                    }
                                "
                            >

                                <i class="fa fa-exclamation-triangle"></i>

                            </span>

                        @endif


                        @if($student->residency_risk)

                            <span
                                class="student-residency-alert"
                                title="Residency Risk: Projected residency is {{ $student->projected_residency_years }} school years"
                            >

                                <i class="fa fa-clock"></i>

                            </span>

                        @endif


                        <div class="student-view-text">

                            View Record

                        </div>


                        <div class="student-arrow">

                            <i class="fa fa-chevron-right"></i>

                        </div>


                    </a>


                @endforeach


            </div>


            <!-- =================================================
                 SEARCH/FILTER EMPTY STATE
            ================================================== -->

            <div
                class="search-empty-state"
                id="searchEmptyState"
            >

                <div class="search-empty-icon">

                    <i class="fa fa-search"></i>

                </div>


                <h5 class="search-empty-title">

                    No Students Found

                </h5>


                <p
                    class="search-empty-description"
                    id="searchEmptyDescription"
                >

                    No student matches your search or selected academic status.

                </p>

            </div>


        @else


            <!-- =================================================
                 EMPTY DATABASE STATE
            ================================================== -->

            <div class="empty-state">


                <div class="empty-icon">

                    <i class="fa fa-user-slash"></i>

                </div>


                <h5 class="empty-title">

                    No Students Found

                </h5>


                <p class="empty-description">

                    @if(request('search'))

                        No student matches your search.

                    @else

                        There are currently no students in the system.

                    @endif

                </p>


                @if(request('search'))

                    <a
                        href="{{ route('students.index') }}"
                        class="btn btn-outline-primary empty-action"
                    >

                        <i class="fa fa-refresh me-1"></i>

                        Show All Students

                    </a>

                @else

                    <a
                        href="{{ route('students.create') }}"
                        class="btn btn-primary empty-action"
                    >

                        <i class="fa fa-plus me-1"></i>

                        Add First Student

                    </a>

                @endif


            </div>


        @endif


    </div>


</div>


</div>


<!-- =============================================================
     SEARCH + FILTER JAVASCRIPT
============================================================= -->

<script>

document.addEventListener('DOMContentLoaded', function () {


    const searchInput =
        document.getElementById('studentSearch');


    const searchForm =
        document.getElementById('studentSearchForm');


    const searchClearButton =
        document.getElementById('searchClearButton');


    const studentList =
        document.getElementById('studentList');


    const searchEmptyState =
        document.getElementById('searchEmptyState');


    const searchEmptyDescription =
        document.getElementById('searchEmptyDescription');


    const studentCount =
        document.getElementById('studentCount');


    /*
     * FILTER ELEMENTS
     */

    const filterButton =
        document.getElementById('filterButton');


    const filterDropdown =
        document.getElementById('filterDropdown');


    const filterOptions =
        document.querySelectorAll('.filter-option');


    const activeFilterText =
        document.getElementById('activeFilterText');


    const activeFilterName =
        document.getElementById('activeFilterName');


    /*
     * Current selected status.
     */

    let selectedStatus = 'all';


    /*
     * If the student list does not exist,
     * stop the script.
     */

    if (!searchInput || !studentList) {

        return;

    }


    /*
     * Get all students.
     */

    const studentItems = Array.from(
        studentList.querySelectorAll('.student-item')
    );


    /* =========================================================
       FILTER DROPDOWN
    ========================================================= */


    /*
     * Open / close filter popup.
     */

    if (filterButton && filterDropdown) {

        filterButton.addEventListener(
            'click',
            function (event) {

                event.stopPropagation();

                const isOpen =
                    filterDropdown.classList.contains('show');


                if (isOpen) {

                    filterDropdown.classList.remove('show');

                    filterButton.classList.remove('active');

                    filterButton.setAttribute(
                        'aria-expanded',
                        'false'
                    );

                } else {

                    filterDropdown.classList.add('show');

                    filterButton.classList.add('active');

                    filterButton.setAttribute(
                        'aria-expanded',
                        'true'
                    );

                }

            }
        );

    }


    /*
     * Prevent clicks in
      the dropdown
     * from closing it immediately.
     */

    if (filterDropdown) {

        filterDropdown.addEventListener(
            'click',
            function (event) {

                event.stopPropagation();

            }
        );

    }


    /*
     * Close filter popup when clicking
     * somewhere else on the page.
     */

    document.addEventListener(
        'click',
        function () {

            if (
                filterDropdown &&
                filterButton
            ) {

                filterDropdown.classList.remove('show');

                filterButton.classList.remove('active');

                filterButton.setAttribute(
                    'aria-expanded',
                    'false'
                );

            }

        }
    );


    /* =========================================================
       FILTER SELECTION
    ========================================================= */

    filterOptions.forEach(
        function (option) {

            option.addEventListener(
                'click',
                function () {


                    /*
                     * Get selected status.
                     */

                    selectedStatus =
                        option.getAttribute(
                            'data-status'
                        ) || 'all';


                    /*
                     * Remove selected state
                     * from all options.
                     */

                    filterOptions.forEach(
                        function (item) {

                            item.classList.remove(
                                'selected'
                            );

                        }
                    );


                    /*
                     * Mark selected option.
                     */

                    option.classList.add(
                        'selected'
                    );


                    /*
                     * Change filter button appearance.
                     */

                    if (
                        filterButton
                    ) {

                        if (
                            selectedStatus !== 'all'
                        ) {

                            filterButton.classList.add(
                                'has-filter'
                            );

                        } else {

                            filterButton.classList.remove(
                                'has-filter'
                            );

                        }

                    }


                    /*
                     * Show active filter name.
                     */

                    if (
                        activeFilterText &&
                        activeFilterName
                    ) {


                        if (
                            selectedStatus !== 'all'
                        ) {

                            activeFilterName.textContent =
                                selectedStatus;

                            activeFilterText.classList.add(
                                'show'
                            );

                        } else {

                            activeFilterName.textContent =
                                '';

                            activeFilterText.classList.remove(
                                'show'
                            );

                        }

                    }


                    /*
                     * Apply filter.
                     */

                    filterStudents();


                    /*
                     * Close dropdown.
                     */

                    if (
                        filterDropdown &&
                        filterButton
                    ) {

                        filterDropdown.classList.remove(
                            'show'
                        );

                        filterButton.classList.remove(
                            'active'
                        );

                        filterButton.setAttribute(
                            'aria-expanded',
                            'false'
                        );

                    }

                }
            );

        }
    );


    /* =========================================================
       FILTER STUDENTS
    ========================================================= */

    function filterStudents() {


        const searchValue =
            searchInput.value
                .trim()
                .toLowerCase();


        let visibleCount = 0;


        studentItems.forEach(
            function (studentItem) {


                /*
                 * Student name.
                 */

                const studentName =
                    studentItem.getAttribute(
                        'data-student-name'
                    ) || '';


                /*
                 * Latest academic status.
                 */

                const studentStatus =
                    studentItem.getAttribute(
                        'data-student-status'
                    ) || 'No Status';


                const hasResidencyRisk =
                    studentItem.getAttribute(
                        'data-student-residency-risk'
                    ) === '1';


                /*
                 * Search condition.
                 */

                const matchesSearch =
                    searchValue === '' ||
                    studentName.includes(
                        searchValue
                    );


                /*
                 * Status condition.
                 */

                const matchesStatus =
                    selectedStatus === 'all' ||
                    (
                        selectedStatus === 'Residency Risk'
                            ? hasResidencyRisk
                            : studentStatus === selectedStatus
                    );


                /*
                 * Student must satisfy
                 * BOTH conditions.
                 */

                if (
                    matchesSearch &&
                    matchesStatus
                ) {

                    studentItem.style.display =
                        'flex';

                    visibleCount++;

                } else {

                    studentItem.style.display =
                        'none';

                }

            }
        );


        /* =====================================================
           UPDATE COUNT
        ====================================================== */

        if (studentCount) {

            studentCount.textContent =
                visibleCount +
                ' ' +
                (
                    visibleCount === 1
                        ? 'Student'
                        : 'Students'
                );

        }


        /* =====================================================
           EMPTY STATE
        ====================================================== */

        if (searchEmptyState) {


            if (visibleCount === 0) {

                searchEmptyState.style.display =
                    'block';


                if (
                    searchValue !== '' &&
                    selectedStatus !== 'all'
                ) {

                    searchEmptyDescription.textContent =
                        'No student matches your search and selected academic status.';

                } else if (
                    searchValue !== ''
                ) {

                    searchEmptyDescription.textContent =
                        'No student name matches your search.';

                } else if (
                    selectedStatus !== 'all'
                ) {

                    searchEmptyDescription.textContent =
                        'No students have the selected academic status.';

                } else {

                    searchEmptyDescription.textContent =
                        'No students found.';

                }

            } else {

                searchEmptyState.style.display =
                    'none';

            }

        }


        /* =====================================================
           CLEAR BUTTON
        ====================================================== */

        if (searchClearButton) {

            if (searchValue !== '') {

                searchClearButton.style.display =
                    'inline-flex';

            } else {

                searchClearButton.style.display =
                    'none';

            }

        }

    }


    /* =========================================================
       LIVE SEARCH
    ========================================================= */

    searchInput.addEventListener(
        'input',
        function () {

            filterStudents();

        }
    );


    /* =========================================================
       FORM SUBMIT
    ========================================================= */

    if (searchForm) {

        searchForm.addEventListener(
            'submit',
            function (event) {

                event.preventDefault();

                filterStudents();

            }
        );

    }


    /* =========================================================
       CLEAR SEARCH
    ========================================================= */

    if (searchClearButton) {

        searchClearButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();

                searchInput.value = '';

                filterStudents();

                searchInput.focus();

            }
        );

    }


    /* =========================================================
       INITIAL FILTER
    ========================================================= */

    filterStudents();


});

</script>

@endsection
