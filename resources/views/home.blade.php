@extends('layouts.app')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | PRIORITY ALERT DISPLAY
    |--------------------------------------------------------------------------
    |
    | Priority Alert is based ONLY on performance comparison.
    |
    | Current failure < previous failure
    |     = Improvement
    |     = NO Priority Alert
    |
    | Current failure = previous failure
    |     = No improvement
    |     = Priority Alert
    |
    | Current failure > previous failure
    |     = Worse
    |     = Priority Alert
    |
    | A student with only one evaluation can NEVER be a Priority Alert.
    |
    */

    $dashboardPriorityStudents = collect();


    $allDashboardHistories =
        \App\Models\StudentStatusHistory::with([
            'student',
            'semester'
        ])
        ->orderByDesc('semester_id')
        ->orderByDesc('status_id')
        ->get()
        ->groupBy('student_id');


    foreach (
        $allDashboardHistories
        as $studentHistories
    ) {

        $evaluations =
            $studentHistories
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


        if (
            !$latestEvaluation ||
            !$previousEvaluation ||
            !$latestEvaluation->student
        ) {

            continue;

        }


        $currentFailure =
            (float) $latestEvaluation->failure_percentage;


        $previousFailure =
            (float) $previousEvaluation->failure_percentage;


        if (
            $currentFailure <
            $previousFailure
        ) {

            continue;

        }


        $student =
            $latestEvaluation->student;


        $dashboardStatus =
            $latestEvaluation->academic_status;


        if (
            $dashboardStatus ===
            'For Shifting Out'
        ) {

            $dashboardStatus =
                'Shifting Out';

        }


        $student->dashboard_status =
            $dashboardStatus;


        $student->priority_alert =
            true;


        $dashboardPriorityStudents->push(
            $student
        );

    }


    $priorityStudents =
        $dashboardPriorityStudents->values();


    $priorityAlerts =
        $priorityStudents->count();

@endphp


<div class="dashboard-page">

    <!-- =========================================================
         DASHBOARD HEADER
    ========================================================== -->

    <div class="dashboard-hero">

        <div class="hero-content">

            <div class="hero-left">

                <div class="dashboard-logo">

                    <img
                        src="{{ asset('images/ite-logo.png') }}"
                        alt="System Logo"
                    >

                </div>

                <div>

                    <div class="hero-eyebrow">
                        ACADEMIC MONITORING
                    </div>

                    <h2 class="hero-title">
                        Academic Dashboard
                    </h2>

                    <p class="hero-subtitle">
                        BSIT Academic Progress & Retention Monitoring System
                    </p>

                </div>

            </div>


            <div class="dashboard-date">

                <div class="date-icon">
                    <i class="fa fa-calendar"></i>
                </div>

                <div>

                    <span class="date-label">
                        Today
                    </span>

                    <strong>
                        {{ now()->format('F d, Y') }}
                    </strong>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         OVERVIEW TITLE
    ========================================================== -->

    <div class="section-heading">

        <div>

            <h5>
                Academic Overview
            </h5>

        </div>

    </div>


    <!-- =========================================================
         OVERVIEW CARDS
    ========================================================== -->

    <div class="row g-3 mb-4">


        <!-- TOTAL STUDENTS -->

        <div class="col-xl-3 col-md-6">

            <div class="overview-card total-card h-100">

                <div class="overview-icon">
                    <i class="fa fa-users"></i>
                </div>

                <div class="overview-content">

                    <span>
                        Total Students
                    </span>

                    <strong>
                        {{ $totalStudents }}
                    </strong>

                    <small>
                        Registered students
                    </small>

                </div>

            </div>

        </div>


        <!-- PRIORITY ALERTS -->

        <div class="col-xl-3 col-md-6">

            <div
                class="overview-card priority-card clickable-card h-100"
                data-bs-toggle="modal"
                data-bs-target="#priorityModal"
                tabindex="0"
                role="button"
            >

                <div class="overview-icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>

                <div class="overview-content">

                    <span>
                        Priority Alerts
                    </span>

                    <strong>
                        {{ $priorityAlerts }}
                    </strong>

                    

                </div>

                <div class="card-action-icon">
                    <i class="fa fa-arrow-up-right-from-square"></i>
                </div>

            </div>

        </div>


        <!-- RESIDENCY RISK -->

        <div class="col-xl-3 col-md-6">

            <div
                class="overview-card residency-card clickable-card h-100"
                data-bs-toggle="modal"
                data-bs-target="#residencyModal"
                tabindex="0"
                role="button"
            >

                <div class="overview-icon">
                    <i class="fa fa-clock"></i>
                </div>

                <div class="overview-content">

                    <span>
                        Residency Risk
                    </span>

                    <strong>
                        {{ $residencyRisk }}
                    </strong>

    

                </div>

                <div class="card-action-icon">
                    <i class="fa fa-arrow-up-right-from-square"></i>
                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         ACADEMIC STATUS
    ========================================================== -->

    <div class="section-heading status-heading">

        <div>

            <h5>
                Academic Standing
            </h5>

        </div>

    </div>


    <div class="row g-3 mb-4">


        <!-- REGULAR -->

        <div class="col-xl-3 col-md-6">

            <div
                class="status-card regular-card clickable-card h-100"
                data-bs-toggle="modal"
                data-bs-target="#regularModal"
                tabindex="0"
                role="button"
            >

                <div class="status-icon">
                    <i class="fa fa-check"></i>
                </div>

                <div class="status-content">

                    <span class="status-label">
                        Below Treshold
                    </span>

                    <strong>
                        {{ $regular }}
                    </strong>

        

                </div>

                <div class="status-arrow">
                    <i class="fa fa-chevron-right"></i>
                </div>

            </div>

        </div>


        <!-- ACADEMIC WARNING -->

        <div class="col-xl-3 col-md-6">

            <div
                class="status-card warning-card clickable-card h-100"
                data-bs-toggle="modal"
                data-bs-target="#warningModal"
                tabindex="0"
                role="button"
            >

                <div class="status-icon">
                    <i class="fa fa-exclamation"></i>
                </div>

                <div class="status-content">

                    <span class="status-label">
                        Academic Warning
                    </span>

                    <strong>
                        {{ $warning }}
                    </strong>

                    

                </div>

                <div class="status-arrow">
                    <i class="fa fa-chevron-right"></i>
                </div>

            </div>

        </div>


        <!-- PROBATION -->

        <div class="col-xl-3 col-md-6">

            <div
                class="status-card probation-card clickable-card h-100"
                data-bs-toggle="modal"
                data-bs-target="#probationModal"
                tabindex="0"
                role="button"
            >

                <div class="status-icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>

                <div class="status-content">

                    <span class="status-label">
                        Probation
                    </span>

                    <strong>
                        {{ $probation }}
                    </strong>

                   

                </div>

                <div class="status-arrow">
                    <i class="fa fa-chevron-right"></i>
                </div>

            </div>

        </div>


        <!-- SHIFTING OUT -->

        <div class="col-xl-3 col-md-6">

            <div
                class="status-card shifting-card clickable-card h-100"
                data-bs-toggle="modal"
                data-bs-target="#shiftingModal"
                tabindex="0"
                role="button"
            >

                <div class="status-icon">
                    <i class="fa fa-right-left"></i>
                </div>

                <div class="status-content">

                    <span class="status-label">
                        Shifting Out
                    </span>

                    <strong>
                        {{ $shifting }}
                    </strong>

                   

                </div>

                <div class="status-arrow">
                    <i class="fa fa-chevron-right"></i>
                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         CHARTS
    ========================================================== -->

    <div class="section-heading">

        <div>

            <h5>
                Academic Performance
            </h5>

            <p>
                Current academic standing and flagged students by school year
            </p>

        </div>

    </div>


    <div class="row g-3 mb-4">


        <!-- STATUS DISTRIBUTION -->

        <div class="col-lg-7">

            <div class="dashboard-panel h-100">

                <div class="panel-header">

                    <div class="panel-title-group">

                        <div class="panel-icon green-icon">
                            <i class="fa fa-chart-column"></i>
                        </div>

                        <div>

                            <h6>
                                Academic Status Distribution
                            </h6>

                            <span>
                                Current standing of students
                            </span>

                        </div>

                    </div>

                </div>

                <div class="panel-body">

                    <div class="chart-container">

                        <canvas id="statusChart"></canvas>

                    </div>

                </div>

            </div>

        </div>


        <!-- SCHOOL YEAR PERFORMANCE -->

        <div class="col-lg-5">

            <div class="dashboard-panel school-year-panel h-100">

                <div class="panel-header">

                    <div class="panel-title-group">

                        <div class="panel-icon school-year-icon">
                            <i class="fa fa-calendar-days"></i>
                        </div>

                        <div>

                            <h6>
                                School Year Performance
                            </h6>

                            <span>
                                Students flagged for academic status
                            </span>

                        </div>

                    </div>

                </div>

                <div class="panel-body">

                    @if(count($schoolYearLabels) > 0)

                        <div class="chart-container">

                            <canvas id="schoolYearChart"></canvas>

                        </div>

                        

                    @else

                        <div class="chart-empty-state">

                            <div class="chart-empty-icon">
                                <i class="fa fa-chart-column"></i>
                            </div>

                            <strong>
                                No flagged students yet
                            </strong>

                            <span>
                                School year performance will appear after
                                academic evaluations are recorded.
                            </span>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         RECENT EVALUATIONS
    ========================================================== -->

    <div class="row g-3">


        <!-- RECENTLY EVALUATED -->

        <div class="col-12">

            <div class="dashboard-panel h-100">

                <div class="panel-header">

                    <div class="panel-title-group">

                        <div class="panel-icon green-icon">
                            <i class="fa fa-history"></i>
                        </div>

                        <div>

                            <h6>
                                Recently Evaluated Students
                            </h6>

                            <span>
                                Three most recent unique student evaluations
                            </span>

                        </div>

                    </div>

                </div>


                <div class="panel-body p-0">

                    <div class="table-responsive">

                        <table class="dashboard-table">

                            <thead>

                                <tr>

                                    <th>
                                        Student
                                    </th>

                                    <th>
                                        Semester
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Failure %
                                    </th>

                                    <th>
                                        Evaluated
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @forelse($recentlyEvaluated as $history)

                                    @php

                                        $recentStatus =
                                            $history->academic_status;

                                        if ($recentStatus === 'Regular') {
    $recentStatus = 'Below Treshold';
}


                                        if (
                                            $recentStatus ===
                                            'For Shifting Out'
                                        ) {

                                            $recentStatus =
                                                'Shifting Out';

                                        }

                                    @endphp


                                    <tr>

                                        <!-- STUDENT -->

                                        <td>

                                            @if($history->student)

                                                <div class="table-student">

                                                    <div class="table-avatar">

                                                        {{
                                                            strtoupper(
                                                                substr(
                                                                    $history->student->first_name,
                                                                    0,
                                                                    1
                                                                )
                                                            )
                                                        }}

                                                    </div>

                                                    <div>

                                                        <strong>

                                                            {{
                                                                $history->student->last_name
                                                            }},
                                                            {{
                                                                $history->student->first_name
                                                            }}

                                                        </strong>

                                                        <small>

                                                            {{
                                                                $history->student->student_no
                                                            }}

                                                        </small>

                                                    </div>

                                                </div>

                                            @else

                                                <span class="text-muted">
                                                    Student unavailable
                                                </span>

                                            @endif

                                        </td>


                                        <!-- SEMESTER -->

                                        <td>

                                            @if($history->semester)

                                                <div class="semester-cell">

                                                    <strong>
                                                        {{
                                                            $history->semester->school_year
                                                        }}
                                                    </strong>

                                                    <small>
                                                        {{
                                                            $history->semester->semester_name
                                                        }}
                                                    </small>

                                                </div>

                                            @else

                                                <span class="text-muted">
                                                    N/A
                                                </span>

                                            @endif

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            @if($recentStatus === 'Below Treshold')

                                                <span class="status-pill regular-pill">
                                                    Below Treshold
                                                </span>

                                            @elseif($recentStatus === 'Academic Warning')

                                                <span class="status-pill warning-pill">
                                                    Academic Warning
                                                </span>

                                            @elseif($recentStatus === 'Probation')

                                                <span class="status-pill probation-pill">
                                                    Probation
                                                </span>

                                            @elseif($recentStatus === 'Shifting Out')

                                                <span class="status-pill shifting-pill">
                                                    Shifting Out
                                                </span>

                                            @elseif($recentStatus === 'Residency Risk')

                                                <span class="status-pill residency-pill">
                                                    Residency Risk
                                                </span>

                                            @else

                                                <span class="status-pill neutral-pill">
                                                    {{ $recentStatus }}
                                                </span>

                                            @endif

                                        </td>


                                        <!-- FAILURE PERCENTAGE -->

                                        <td>

                                            <div class="percentage-cell">

                                                <strong>
                                                    {{ number_format((float) $history->failure_percentage, 2) }}%
                                                </strong>

                                            </div>

                                        </td>


                                        <!-- EVALUATED -->

                                        <td>

                                            @if($history->created_at)

                                                <div class="date-cell">

                                                    <strong>
                                                        {{
                                                            $history->created_at
                                                                ->format('M d, Y')
                                                        }}
                                                    </strong>

                                                    <small>
                                                        {{
                                                            $history->created_at
                                                                ->format('h:i A')
                                                        }}
                                                    </small>

                                                </div>

                                            @else

                                                <span class="text-muted">
                                                    N/A
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="5"
                                            class="empty-table"
                                        >

                                            <div class="empty-icon">
                                                <i class="fa fa-clipboard"></i>
                                            </div>

                                            <strong>
                                                No recent evaluations
                                            </strong>

                                            <span>
                                                No academic evaluations have been recorded yet.
                                            </span>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


</div>


<!-- =============================================================
     REGULAR STUDENTS MODAL
============================================================== -->

<div
    class="modal fade"
    id="regularModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content dashboard-modal">

            <div class="modal-header modal-success-header">

                <div class="modal-heading">

                    <div class="modal-icon">
                        <i class="fa fa-check-circle"></i>
                    </div>

                    <div>

                        <h5>
                            Below Treshold Students
                        </h5>

                        <span>
                            Students below 25% failure
                        </span>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="modal-count">
                    {{ $regular }} students
                </div>

                <div class="table-responsive">

                    <table class="modal-table">

                        <thead>

                            <tr>

                                <th>
                                    Student No.
                                </th>

                                <th>
                                    Student Name
                                </th>

                                <th>
                                    Year Level
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($regularStudents as $student)

                                <tr>

                                    <td>
                                        {{ $student->student_no }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $student->last_name }},
                                            {{ $student->first_name }}
                                            {{ $student->middle_name }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $student->current_year_level }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="3"
                                        class="modal-empty"
                                    >

                                        No students found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =============================================================
     ACADEMIC WARNING MODAL
============================================================== -->

<div
    class="modal fade"
    id="warningModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content dashboard-modal">

            <div class="modal-header modal-warning-header">

                <div class="modal-heading">

                    <div class="modal-icon">
                        <i class="fa fa-exclamation-circle"></i>
                    </div>

                    <div>

                        <h5>
                            Academic Warning Students
                        </h5>

                        <span>
                            Students requiring academic improvement
                        </span>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="modal-count">
                    {{ $warning }} students
                </div>

                <div class="table-responsive">

                    <table class="modal-table">

                        <thead>

                            <tr>

                                <th>
                                    Student No.
                                </th>

                                <th>
                                    Student Name
                                </th>

                                <th>
                                    Year Level
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($warningStudents as $student)

                                <tr>

                                    <td>
                                        {{ $student->student_no }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $student->last_name }},
                                            {{ $student->first_name }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $student->current_year_level }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="3"
                                        class="modal-empty"
                                    >

                                        No Academic Warning students found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =============================================================
     PROBATION MODAL
============================================================== -->

<div
    class="modal fade"
    id="probationModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content dashboard-modal">

            <div class="modal-header modal-danger-header">

                <div class="modal-heading">

                    <div class="modal-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>

                    <div>

                        <h5>
                            Students on Probation
                        </h5>

                        <span>
                            Students requiring intervention
                        </span>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="modal-count">
                    {{ $probation }} students
                </div>

                <div class="table-responsive">

                    <table class="modal-table">

                        <thead>

                            <tr>

                                <th>
                                    Student No.
                                </th>

                                <th>
                                    Student Name
                                </th>

                                <th>
                                    Year Level
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($probationStudents as $student)

                                <tr>

                                    <td>
                                        {{ $student->student_no }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $student->last_name }},
                                            {{ $student->first_name }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $student->current_year_level }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="3"
                                        class="modal-empty"
                                    >

                                        No students on Probation found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =============================================================
     SHIFTING OUT MODAL
============================================================== -->

<div
    class="modal fade"
    id="shiftingModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content dashboard-modal">

            <div class="modal-header modal-dark-header">

                <div class="modal-heading">

                    <div class="modal-icon">
                        <i class="fa fa-right-left"></i>
                    </div>

                    <div>

                        <h5>
                            Students for Shifting Out
                        </h5>

                        <span>
                            Students subject to academic action
                        </span>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="modal-count">
                    {{ $shifting }} students
                </div>

                <div class="table-responsive">

                    <table class="modal-table">

                        <thead>

                            <tr>

                                <th>
                                    Student No.
                                </th>

                                <th>
                                    Student Name
                                </th>

                                <th>
                                    Year Level
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($shiftingStudents as $student)

                                <tr>

                                    <td>
                                        {{ $student->student_no }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $student->last_name }},
                                            {{ $student->first_name }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $student->current_year_level }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="3"
                                        class="modal-empty"
                                    >

                                        No students for Shifting Out found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =============================================================
     RESIDENCY RISK MODAL
============================================================== -->

<div
    class="modal fade"
    id="residencyModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content dashboard-modal">

            <div class="modal-header modal-info-header">

                <div class="modal-heading">

                    <div class="modal-icon">
                        <i class="fa fa-clock"></i>
                    </div>

                    <div>

                        <h5>
                            Residency Risk Students
                        </h5>

                        <span>
                            Students near maximum residency
                        </span>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="modal-count">
                    {{ $residencyRisk }} students
                </div>

                <div class="table-responsive">

                    <table class="modal-table">

                        <thead>

                            <tr>

                                <th>
                                    Student No.
                                </th>

                                <th>
                                    Student Name
                                </th>

                                <th>
                                    Year Level
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($residencyRiskStudents as $student)

                                <tr>

                                    <td>
                                        {{ $student->student_no }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $student->last_name }},
                                            {{ $student->first_name }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $student->current_year_level }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="3"
                                        class="modal-empty"
                                    >

                                        No Residency Risk students found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =============================================================
     PRIORITY ALERT MODAL
============================================================== -->

<div
    class="modal fade"
    id="priorityModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content dashboard-modal">

            <div class="modal-header modal-danger-header">

                <div class="modal-heading">

                    <div class="modal-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>

                    <div>

                        <h5>
                            Priority Alert Students
                        </h5>

                        <span>
                            Students requiring priority attention
                        </span>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="modal-count">
                    {{ $priorityAlerts }} students
                </div>

                <div class="table-responsive">

                    <table class="modal-table">

                        <thead>

                            <tr>

                                <th>
                                    Student No.
                                </th>

                                <th>
                                    Student Name
                                </th>

                                <th>
                                    Academic Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($priorityStudents as $student)

                                <tr>

                                    <td>
                                        {{ $student->student_no }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $student->last_name }},
                                            {{ $student->first_name }}
                                        </strong>
                                    </td>

                                    <td>

                                        @if($student->dashboard_status === 'Probation')

                                            <span class="status-pill probation-pill">
                                                Probation
                                            </span>

                                        @elseif($student->dashboard_status === 'Shifting Out')

                                            <span class="status-pill shifting-pill">
                                                Shifting Out
                                            </span>

                                        @elseif($student->dashboard_status === 'Academic Warning')

                                            <span class="status-pill warning-pill">
                                                Academic Warning
                                            </span>

                                        @elseif($student->dashboard_status === 'Residency Risk')

                                            <span class="status-pill residency-pill">
                                                Residency Risk
                                            </span>

                                        @else

                                            <span class="status-pill neutral-pill">
                                                {{ $student->dashboard_status }}
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="3"
                                        class="modal-empty"
                                    >

                                        No priority alerts found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =============================================================
     DASHBOARD STYLE
============================================================== -->

<style>

/* =========================================================
   DASHBOARD THEME — MATCHES SHOW.BLADE BACKGROUND
========================================================= */

.dashboard-page {
    --page-bg: #7e8388;
    --surface: #f8f9fa;
    --surface-soft: #f0f2f4;
    --surface-hover: #e9f3ec;
    --border: #dce1e5;
    --text-main: #263447;
    --text-secondary: #657180;
    --text-muted: #89939e;
    --green: #198754;
    --green-dark: #146c43;

    max-width: 1500px;
    margin: 0 auto;
    padding: 10px 4px 35px;
    color: var(--text-main);
}

.dashboard-page *,
.dashboard-modal * {
    box-sizing: border-box;
}

.dashboard-page .text-muted {
    color: #727d88 !important;
}

/* Keep the surrounding page consistent with show.blade.
   The body rule is scoped through :has() to avoid changing
   unrelated pages. */
body:has(.dashboard-page) {
    background: #7e8388;
}


/* =========================================================
   DASHBOARD HEADER
========================================================= */

.dashboard-hero {
    background: linear-gradient(
        135deg,
        #126c39 0%,
        #198754 55%,
        #36a56a 100%
    );
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 18px;
    padding: 25px 28px;
    margin-bottom: 28px;
    color: #fff;
    box-shadow: 0 8px 22px rgba(25, 48, 35, .17);
    position: relative;
    overflow: hidden;
}

.dashboard-hero::after {
    content: "";
    position: absolute;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: rgba(255,255,255,.07);
    right: -80px;
    top: -100px;
    pointer-events: none;
}

.hero-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 25px;
    position: relative;
    z-index: 1;
}

.hero-left {
    display: flex;
    align-items: center;
    gap: 17px;
    min-width: 0;
}

.dashboard-logo {
    width: 68px;
    height: 68px;
    background: #f8f9fa;
    border: 1px solid rgba(255,255,255,.35);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,.12);
}

.dashboard-logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.hero-eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.2px;
    color: rgba(255,255,255,.8);
    margin-bottom: 4px;
}

.hero-title {
    margin: 0;
    font-size: 27px;
    font-weight: 700;
    letter-spacing: -.3px;
    color: #fff;
}

.hero-subtitle {
    margin: 5px 0 0;
    font-size: 13px;
    color: rgba(255,255,255,.86);
}

.dashboard-date {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    padding: 9px 13px;
    border-radius: 11px;
    min-width: 155px;
    flex-shrink: 0;
}

.date-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(255,255,255,.15);
    display: flex;
    align-items: center;
    justify-content: center;
}

.dashboard-date div:last-child {
    display: flex;
    flex-direction: column;
}

.date-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: rgba(255,255,255,.75);
}

.dashboard-date strong {
    font-size: 13px;
    font-weight: 600;
    color: #fff;
}


/* =========================================================
   SECTION HEADINGS
========================================================= */

.section-heading {
    margin-bottom: 14px;
}

.section-heading h5 {
    margin: 2px 0;
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,.12);
}

.section-heading p {
    margin: 0;
    color: #f0f2f4;
    font-size: 12px;
}

.section-eyebrow {
    font-size: 10px;
    font-weight: 700;
    color: #e4f5e9;
    letter-spacing: 1px;
}

.status-heading {
    margin-top: 4px;
}


/* =========================================================
   OVERVIEW CARDS
========================================================= */

.overview-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 19px;
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(28, 37, 45, .09);
    transition: transform .2s ease, box-shadow .2s ease,
                border-color .2s ease;
}

.overview-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 9px 20px rgba(28, 37, 45, .14);
    border-color: #c3d5ca;
}

.overview-icon {
    width: 50px;
    height: 50px;
    min-width: 50px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.overview-content {
    min-width: 0;
    flex: 1;
}

.overview-content span {
    display: block;
    color: var(--text-secondary);
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 2px;
}

.overview-content strong {
    display: block;
    font-size: 27px;
    line-height: 1.1;
    color: var(--text-main);
    font-weight: 700;
}

.overview-content small {
    display: block;
    color: var(--text-muted);
    font-size: 10px;
    margin-top: 4px;
}

.card-action-icon {
    position: absolute;
    top: 13px;
    right: 13px;
    color: #87939d;
    font-size: 11px;
}

.total-card .overview-icon {
    background: #e3f2e9;
    color: #176b3a;
}

.priority-card .overview-icon {
    background: #fbe9eb;
    color: #c52d3d;
}

.residency-card .overview-icon {
    background: #e1f1f3;
    color: #237d87;
}


/* =========================================================
   CLICKABLE CARDS
========================================================= */

.clickable-card {
    cursor: pointer;
    outline: none;
}

.clickable-card:focus-visible {
    box-shadow: 0 0 0 3px rgba(25,135,84,.25),
                0 8px 20px rgba(28,37,45,.12);
}

.clickable-card:active {
    transform: translateY(-1px);
}


/* =========================================================
   ACADEMIC STATUS CARDS
========================================================= */

.status-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-left-width: 4px;
    border-radius: 14px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 13px;
    min-height: 112px;
    box-shadow: 0 4px 12px rgba(28,37,45,.08);
    transition: transform .2s ease, box-shadow .2s ease;
}

.status-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 9px 20px rgba(28,37,45,.13);
}

.status-icon {
    width: 46px;
    height: 46px;
    min-width: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.status-content {
    flex: 1;
    min-width: 0;
}

.status-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
}

.status-content strong {
    display: block;
    font-size: 26px;
    line-height: 1.15;
    color: var(--text-main);
}

.status-content small {
    display: block;
    color: var(--text-muted);
    font-size: 10px;
    margin-top: 3px;
}

.status-arrow {
    color: #89949e;
    font-size: 11px;
}

.regular-card {
    border-left-color: #198754;
}

.regular-card .status-icon {
    background: #e3f2e9;
    color: #176b3a;
}

.warning-card {
    border-left-color: #d5a000;
}

.warning-card .status-icon {
    background: #fff2cc;
    color: #946e00;
}

.probation-card {
    border-left-color: #dc3545;
}

.probation-card .status-icon {
    background: #fbe8eb;
    color: #c52d3d;
}

.shifting-card {
    border-left-color: #495057;
}

.shifting-card .status-icon {
    background: #e8ebee;
    color: #343a40;
}


/* =========================================================
   CHART AND TABLE PANELS
========================================================= */

.dashboard-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 13px rgba(28,37,45,.09);
}

.panel-header {
    padding: 17px 19px;
    background: #f1f3f5;
    border-bottom: 1px solid var(--border);
}

.panel-title-group {
    display: flex;
    align-items: center;
    gap: 11px;
}

.panel-icon {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.blue-icon,
.green-icon {
    background: #e3f2e9;
    color: #176b3a;
}

.school-year-icon {
    background: #dcefe4;
    color: #176b3a;
}

.purple-icon {
    background: #eee8fa;
    color: #6744aa;
}

.panel-title-group h6 {
    margin: 0;
    color: #29374a;
    font-size: 14px;
    font-weight: 700;
}

.panel-title-group span {
    display: block;
    color: #788491;
    font-size: 10px;
    margin-top: 3px;
}

.panel-body {
    padding: 17px 19px;
}

.chart-container {
    height: 300px;
    position: relative;
}


/* =========================================================
   SCHOOL YEAR PERFORMANCE
========================================================= */

.school-year-panel {
    border-top: 3px solid #198754;
}

.school-year-note {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    margin-top: 10px;
    padding: 9px 10px;
    border-radius: 9px;
    background: #eaf4ee;
    border: 1px solid #d0e5d7;
    color: #526c5b;
    font-size: 9px;
    line-height: 1.5;
}

.school-year-note i {
    color: #198754;
    margin-top: 1px;
}

.school-year-note span {
    flex: 1;
}

.chart-empty-state {
    min-height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 30px 20px;
}

.chart-empty-icon {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    background: #e3f2e9;
    color: #176b3a;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    font-size: 18px;
}

.chart-empty-state strong {
    color: #465366;
    font-size: 13px;
}

.chart-empty-state span {
    max-width: 270px;
    color: #788491;
    font-size: 10px;
    line-height: 1.5;
    margin-top: 5px;
}


/* =========================================================
   RECENT EVALUATIONS TABLE
========================================================= */

.dashboard-table {
    width: 100%;
    border-collapse: collapse;
}

.dashboard-table thead th {
    padding: 12px 16px;
    background: #edf0f2;
    border-bottom: 1px solid var(--border);
    color: #586575;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    white-space: nowrap;
}

.dashboard-table tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid #e4e8eb;
    vertical-align: middle;
    font-size: 11px;
    color: #465366;
}

.dashboard-table tbody tr:last-child td {
    border-bottom: none;
}

.dashboard-table tbody tr {
    transition: background .15s ease;
}

.dashboard-table tbody tr:hover {
    background: #eaf4ee;
}

.table-student {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 175px;
}

.table-avatar {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 9px;
    background: #e3f2e9;
    color: #176b3a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
}

.table-student strong {
    display: block;
    color: #344255;
    font-size: 11px;
}

.table-student small {
    display: block;
    color: #788491;
    font-size: 9px;
    margin-top: 3px;
}

.semester-cell strong,
.date-cell strong {
    display: block;
    font-size: 10px;
    color: #4a5869;
}

.semester-cell small,
.date-cell small {
    display: block;
    color: #788491;
    font-size: 9px;
    margin-top: 3px;
}

.percentage-cell strong {
    display: inline-flex;
    align-items: center;
    background: #e3f2e9;
    color: #176b3a;
    border: 1px solid #cfe5d7;
    border-radius: 7px;
    padding: 5px 8px;
    font-size: 10px;
}

.empty-table {
    text-align: center;
    padding: 45px 20px !important;
}

.empty-table .empty-icon {
    width: 42px;
    height: 42px;
    margin: 0 auto 9px;
    border-radius: 11px;
    background: #e9edf0;
    color: #788491;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-table strong {
    display: block;
    font-size: 12px;
    color: #465366;
}

.empty-table span {
    display: block;
    font-size: 10px;
    color: #788491;
    margin-top: 4px;
}


/* =========================================================
   ACADEMIC STATUS LABELS
========================================================= */

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    padding: 5px 9px;
    font-size: 9px;
    line-height: 1.2;
    font-weight: 700;
    white-space: nowrap;
}

.regular-pill {
    background: #dff0e6;
    color: #176b3a;
}

.warning-pill {
    background: #fff0c2;
    color: #866300;
}

.probation-pill {
    background: #f9dfe3;
    color: #b42332;
}

.shifting-pill {
    background: #e3e6e9;
    color: #343a40;
}

.residency-pill {
    background: #dff0f2;
    color: #216f79;
}

.neutral-pill {
    background: #e8ebee;
    color: #596675;
}


/* =========================================================
   MODALS
========================================================= */

.dashboard-modal {
    background: #f8f9fa;
    border: 1px solid #dce1e5;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(20,35,55,.22);
}

.dashboard-modal .modal-body {
    padding: 19px;
    background: #f8f9fa;
}

.modal-header {
    padding: 17px 19px;
    border: none;
}

.modal-success-header {
    background: #198754;
    color: #fff;
}

.modal-warning-header {
    background: #f0bd31;
    color: #352b00;
}

.modal-danger-header {
    background: #c93443;
    color: #fff;
}

.modal-dark-header {
    background: #41474d;
    color: #fff;
}

.modal-info-header {
    background: #237f8c;
    color: #fff;
}

.modal-heading {
    display: flex;
    align-items: center;
    gap: 11px;
}

.modal-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255,255,255,.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.modal-warning-header .modal-icon {
    background: rgba(0,0,0,.08);
}

.modal-heading h5 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: inherit;
}

.modal-heading span {
    display: block;
    font-size: 10px;
    color: inherit;
    opacity: .82;
    margin-top: 3px;
}

.modal-warning-header .modal-heading span {
    opacity: .75;
}

.modal-count {
    display: inline-flex;
    align-items: center;
    background: #edf0f2;
    color: #526071;
    border: 1px solid #dce1e5;
    border-radius: 20px;
    padding: 6px 10px;
    font-size: 10px;
    font-weight: 600;
    margin-bottom: 13px;
}

.modal-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.modal-table thead th {
    background: #edf0f2;
    color: #586575;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .45px;
    padding: 11px 13px;
    border-top: 1px solid #dce1e5;
    border-bottom: 1px solid #dce1e5;
}

.modal-table thead th:first-child {
    border-left: 1px solid #dce1e5;
    border-radius: 8px 0 0 0;
}

.modal-table thead th:last-child {
    border-right: 1px solid #dce1e5;
    border-radius: 0 8px 0 0;
}

.modal-table tbody td {
    padding: 11px 13px;
    border-bottom: 1px solid #e4e8eb;
    font-size: 10px;
    color: #465366;
}

.modal-table tbody tr td:first-child {
    border-left: 1px solid #e4e8eb;
}

.modal-table tbody tr td:last-child {
    border-right: 1px solid #e4e8eb;
}

.modal-table tbody tr:last-child td:first-child {
    border-radius: 0 0 0 8px;
}

.modal-table tbody tr:last-child td:last-child {
    border-radius: 0 0 8px 0;
}

.modal-table tbody tr:hover {
    background: #eaf4ee;
}

.modal-empty {
    text-align: center;
    padding: 35px 15px !important;
    color: #788491 !important;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1199px) {
    .overview-card {
        min-height: 105px;
    }
}

@media (max-width: 991px) {
    .hero-content {
        align-items: flex-start;
    }

    .dashboard-date {
        min-width: auto;
    }

    .chart-container {
        height: 280px;
    }
}

@media (max-width: 767px) {
    .dashboard-page {
        padding: 5px 0 25px;
    }

    .dashboard-hero {
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 22px;
    }

    .hero-content {
        flex-direction: column;
        align-items: stretch;
    }

    .hero-left {
        align-items: flex-start;
    }

    .dashboard-logo {
        width: 58px;
        height: 58px;
        min-width: 58px;
    }

    .hero-title {
        font-size: 22px;
    }

    .hero-subtitle {
        font-size: 11px;
        line-height: 1.5;
    }

    .dashboard-date {
        width: 100%;
    }

    .section-heading h5 {
        font-size: 16px;
    }

    .status-card {
        min-height: 100px;
    }

    .chart-container {
        height: 260px;
    }

    .dashboard-table {
        min-width: 760px;
    }

    .dashboard-table tbody td {
        padding: 11px 13px;
    }
}

@media (max-width: 480px) {
    .overview-card {
        padding: 16px;
    }

    .overview-icon {
        width: 45px;
        height: 45px;
        min-width: 45px;
    }

    .overview-content strong {
        font-size: 24px;
    }

    .status-card {
        padding: 16px;
    }

    .status-icon {
        width: 43px;
        height: 43px;
        min-width: 43px;
    }

    .chart-container {
        height: 235px;
    }

    .dashboard-modal .modal-body {
        padding: 14px;
    }

    .modal-table {
        min-width: 450px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .overview-card,
    .status-card,
    .dashboard-table tbody tr {
        transition: none;
    }
}

</style>


<!-- =============================================================
     CHART.JS
============================================================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /* =========================================================
           ACADEMIC STATUS DISTRIBUTION CHART
        ========================================================== */

        const statusChartElement =
            document.getElementById(
                'statusChart'
            );


        if (statusChartElement) {

            new Chart(
                statusChartElement,
                {

                    type: 'bar',

                    data: {

                        labels: [

                            'Below Treshold',

                            'Academic Warning',

                            'Probation',

                            'Shifting Out',

                            'Residency Risk'

                        ],

                        datasets: [

                            {

                                label:
                                    'Number of Students',

                                data: [

                                    {{ $regular }},

                                    {{ $warning }},

                                    {{ $probation }},

                                    {{ $shifting }},

                                    {{ $residencyRisk }}

                                ],

                                backgroundColor: [

                                    '#198754',

                                    '#69a96f',

                                    '#3d8b5a',

                                    '#176b3a',

                                    '#8bc9a3'

                                ],

                                borderRadius: 7,

                                borderSkipped: false,

                                maxBarThickness: 45

                            }

                        ]

                    },


                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        plugins: {

                            legend: {

                                display: false

                            },

                            tooltip: {

                                backgroundColor:
                                    '#263447',

                                padding: 10,

                                titleFont: {

                                    size: 11

                                },

                                bodyFont: {

                                    size: 11

                                }

                            }

                        },

                        scales: {

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    precision: 0,

                                    color: '#8b97a5',

                                    font: {

                                        size: 10

                                    }

                                },

                                grid: {

                                    color:
                                        'rgba(0,0,0,0.045)'

                                },

                                border: {

                                    display: false

                                }

                            },

                            x: {

                                ticks: {

                                    color: '#7f8b99',

                                    font: {

                                        size: 9

                                    },

                                    maxRotation: 0,

                                    minRotation: 0

                                },

                                grid: {

                                    display: false

                                },

                                border: {

                                    display: false

                                }

                            }

                        }

                    }

                }

            );

        }


        /* =========================================================
           SCHOOL YEAR PERFORMANCE CHART
        ========================================================== */

        const schoolYearChartElement =
            document.getElementById(
                'schoolYearChart'
            );


        if (
            schoolYearChartElement &&
            @json($schoolYearLabels)
        ) {

            const schoolYearLabels =
                @json($schoolYearLabels);


            const schoolYearCounts =
                @json($schoolYearCounts);


            if (schoolYearLabels.length > 0) {

                new Chart(
                    schoolYearChartElement,
                    {

                        type: 'bar',

                        data: {

                            labels:
                                schoolYearLabels,

                            datasets: [

                                {

                                    label:
                                        'Flagged Students',

                                    data:
                                        schoolYearCounts,

                                    backgroundColor:
                                        '#198754',

                                    borderColor:
                                        '#157347',

                                    borderWidth: 1,

                                    borderRadius: 8,

                                    borderSkipped: false,

                                    maxBarThickness: 42

                                }

                            ]

                        },


                        options: {

                            responsive: true,

                            maintainAspectRatio: false,

                            plugins: {

                                legend: {

                                    display: false

                                },

                                tooltip: {

                                    backgroundColor:
                                        '#263447',

                                    padding: 10,

                                    titleFont: {

                                        size: 11

                                    },

                                    bodyFont: {

                                        size: 11

                                    },

                                    callbacks: {

                                        label:
                                            function (context) {

                                                const count =
                                                    context.parsed.y;

                                                return (
                                                    ' Flagged Students: ' +
                                                    count
                                                );

                                            }

                                    }

                                }

                            },


                            scales: {

                                y: {

                                    beginAtZero: true,

                                    ticks: {

                                        precision: 0,

                                        color: '#8b97a5',

                                        font: {

                                            size: 10

                                        }

                                    },

                                    grid: {

                                        color:
                                            'rgba(0,0,0,0.045)'

                                    },

                                    border: {

                                        display: false

                                    }

                                },

                                x: {

                                    ticks: {

                                        color: '#687586',

                                        font: {

                                            size: 9,

                                            weight: '600'

                                        },

                                        maxRotation: 45,

                                        minRotation: 0

                                    },

                                    grid: {

                                        display: false

                                    },

                                    border: {

                                        display: false

                                    }

                                }

                            }

                        }

                    }

                );

            }

        }

    }

);

</script>

@endsection