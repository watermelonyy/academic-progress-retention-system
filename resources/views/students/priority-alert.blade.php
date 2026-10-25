@extends('layouts.app')

@section('content')

<style>
    .priority-page {
        max-width: 1400px;
        margin: 0 auto;
        padding-bottom: 35px;
        color: #344054;
    }

    /* PAGE BACKGROUND */
    .priority-page .page-background {
        background: #83888c;
        border: 1px solid #83888c;
        border-radius: 16px;
        padding: 24px;
    }

    /* BACK BUTTON */
    .priority-page .back-button {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 9px 17px;
        border: 1px solid #d5d9de;
        border-radius: 8px;
        background: #ffffff;
        color: #344054;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .priority-page .back-button:hover {
        background: #eaf5ed;
        color: #16723a;
        border-color: #b7d8c1;
        transform: translateX(-2px);
    }

    /* MAIN CARD */
    .priority-page .main-card {
        border: 1px solid #83888c;
        border-radius: 14px;
        overflow: hidden;
        background: #83888c;
        box-shadow: 0 5px 18px rgba(31, 41, 55, 0.06);
    }

    .priority-page .main-header {
        background: #198754;
        color: #ffffff;
        padding: 22px 26px;
        border: none;
    }

    .priority-page .main-header h4 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
    }

    .priority-page .main-body {
        padding: 26px;
        background: #ffffff;
    }

    /* SECTION TITLES */
    .priority-page .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.05rem;
        font-weight: 700;
        color: #26382c;
        margin-bottom: 18px;
    }

    .priority-page .section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 9px;
        background: #e6f3e9;
        color: #198754;
        flex-shrink: 0;
    }

    /* STUDENT INFORMATION */
    .priority-page .student-panel {
        background: #fafbfc;
        border: 1px solid #e0e4e8;
        border-left: 5px solid #198754;
        border-radius: 12px;
        padding: 22px;
        margin-bottom: 30px;
    }

    .priority-page .student-name {
        color: #26382c;
        font-size: 1.2rem;
        font-weight: 750;
        margin-bottom: 18px;
        overflow-wrap: anywhere;
    }

    .priority-page .student-detail {
        height: 100%;
        padding: 14px 15px;
        background: #ffffff;
        border: 1px solid #e2e6ea;
        border-radius: 9px;
    }

    .priority-page .detail-label {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-weight: 700;
        color: #737d89;
        margin-bottom: 5px;
    }

    .priority-page .detail-value {
        display: block;
        font-weight: 650;
        color: #344054;
        overflow-wrap: anywhere;
    }

    /* EVALUATION CARDS */
    .priority-page .evaluation-card {
        height: 100%;
        background: #ffffff;
        border: 1px solid #dfe4e8;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(31, 41, 55, 0.035);
    }

    .priority-page .recent-card {
        border-color: #e8caca;
    }

    .priority-page .evaluation-card-header {
        padding: 16px 19px;
        background: #f0f2f4;
        color: #344054;
        border-bottom: 1px solid #e0e4e8;
    }

    .priority-page .recent-card .evaluation-card-header {
        background: #fff1f1;
        color: #a52d2d;
        border-bottom-color: #efdada;
    }

    .priority-page .evaluation-card-header strong {
        font-size: 0.96rem;
    }

    .priority-page .evaluation-card-body {
        padding: 21px;
    }

    .priority-page .evaluation-detail {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #edf0f2;
    }

    .priority-page .evaluation-detail:first-child {
        padding-top: 0;
    }

    .priority-page .evaluation-detail:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .priority-page .evaluation-label {
        color: #737d89;
        font-size: 0.87rem;
    }

    .priority-page .evaluation-value {
        text-align: right;
        font-weight: 650;
        color: #344054;
        overflow-wrap: anywhere;
    }

    .priority-page .failure-number {
        font-size: 1.2rem;
        font-weight: 750;
        color: #344054;
    }

    .priority-page .recent-card .failure-number {
        color: #c03939;
    }

    .priority-page .status-badge {
        display: inline-block;
        max-width: 100%;
        white-space: normal;
        text-align: center;
        line-height: 1.5;
        padding: 6px 11px;
        border-radius: 7px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    /* PRIORITY INDICATOR */
    .priority-page .priority-indicator {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        background: #fff1f1;
        border: 1px solid #efcccc;
        border-radius: 10px;
        color: #a52d2d;
        margin-top: 20px;
    }

    .priority-page .priority-indicator i {
        margin-top: 3px;
        font-size: 1.1rem;
    }

    /* PERFORMANCE RESULT */
    .priority-page .result-card {
        border: 1px solid #dfe4e8;
        border-radius: 11px;
        padding: 20px;
        text-align: center;
        background: #fafbfc;
        margin-top: 20px;
    }

    .priority-page .result-danger {
        background: #fff3f3;
        border-color: #edcccc;
        color: #a52d2d;
    }

    .priority-page .result-warning {
        background: #fff9e9;
        border-color: #ecdfb7;
        color: #795d12;
    }

    .priority-page .result-success {
        background: #eff8f1;
        border-color: #c9e3d0;
        color: #176b36;
    }

    .priority-page .result-card p {
        margin: 8px 0 0;
        color: #536357;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    /* INFORMATION NOTICE */
    .priority-page .info-notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #f0f5fa;
        border: 1px solid #d9e3ed;
        color: #345b7c;
        border-radius: 10px;
        padding: 15px 17px;
        margin-top: 22px;
        font-size: 0.88rem;
        line-height: 1.6;
    }

    .priority-page .info-notice i {
        margin-top: 3px;
        font-size: 1.05rem;
    }

    /* COMPARATIVE ANALYSIS */
    .priority-page .analysis-card {
        background: #ffffff;
        border: 1px solid #dfe4e8;
        border-radius: 13px;
        overflow: hidden;
        margin-top: 32px;
        box-shadow: 0 3px 12px rgba(31, 41, 55, 0.04);
    }

    .priority-page .analysis-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 23px;
        background: #edf4ef;
        border-bottom: 1px solid #d9e5dc;
    }

    .priority-page .analysis-header h5 {
        margin: 0;
        font-weight: 750;
        font-size: 1.02rem;
        color: #245a36;
    }

    .priority-page .analysis-header i {
        color: #198754;
        font-size: 1.1rem;
    }

    .priority-page .analysis-body {
        padding: 24px;
    }

    /* METRIC CARDS */
    .priority-page .metric-card {
        height: 100%;
        padding: 18px;
        background: #f7f8fa;
        border: 1px solid #e2e6ea;
        border-radius: 10px;
    }

    .priority-page .metric-label {
        display: block;
        color: #737d89;
        font-size: 0.72rem;
        font-weight: 750;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .priority-page .metric-value {
        display: block;
        color: #26382c;
        font-size: 1.5rem;
        font-weight: 750;
        margin-bottom: 8px;
    }

    .priority-page .metric-period {
        color: #737d89;
        font-size: 0.82rem;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    /* TREND CARDS */
    .priority-page .trend-card {
        height: 100%;
        padding: 18px;
        border: 1px solid transparent;
        border-radius: 10px;
    }

    .priority-page .trend-card strong {
        display: block;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .priority-page .trend-card p {
        margin: 10px 0 0;
        font-size: 0.84rem;
        line-height: 1.6;
    }

    .priority-page .trend-improved {
        background: #eff8f1;
        border-color: #c9e3d0;
        color: #176b36;
    }

    .priority-page .trend-worsened {
        background: #fff2f2;
        border-color: #edcccc;
        color: #a52d2d;
    }

    .priority-page .trend-neutral {
        background: #fff9e9;
        border-color: #ecdfb7;
        color: #795d12;
    }

    /* SEMESTER TRENDS */
    .priority-page .table-heading {
        margin-bottom: 15px;
    }

    .priority-page .table-heading h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 750;
        color: #26382c;
    }

    .priority-page .table-heading p {
        color: #737d89;
        font-size: 0.83rem;
        margin: 5px 0 0;
    }

    /* LINE GRAPH */
    .priority-page .trend-chart-container {
        position: relative;
        width: 100%;
        height: 380px;
        padding: 20px;
        border: 1px solid #dfe4e8;
        border-radius: 11px;
        background: #ffffff;
    }

    .priority-page .trend-chart-container canvas {
        max-width: 100%;
    }

    /* EMPTY STATE */
    .priority-page .empty-state {
        padding: 28px 20px;
        text-align: center;
        background: #f7f8fa;
        border: 1px dashed #d5dce2;
        border-radius: 10px;
        color: #737d89;
    }

    .priority-page .empty-state i {
        display: block;
        font-size: 1.8rem;
        color: #98a3ad;
        margin-bottom: 10px;
    }

    /* RESPONSIVE LAYOUT */
    @media (max-width: 767.98px) {
        .priority-page .page-background {
            padding: 12px;
            border-radius: 10px;
        }

        .priority-page .main-header {
            padding: 18px;
        }

        .priority-page .main-header h4 {
            font-size: 1.15rem;
        }

        .priority-page .main-body {
            padding: 17px;
        }

        .priority-page .student-panel,
        .priority-page .analysis-body,
        .priority-page .evaluation-card-body {
            padding: 17px;
        }

        .priority-page .metric-value {
            font-size: 1.35rem;
        }

        .priority-page .evaluation-detail {
            gap: 10px;
        }

        .priority-page .evaluation-label,
        .priority-page .evaluation-value {
            font-size: 0.83rem;
        }

        .priority-page .trend-chart-container {
            height: 320px;
            padding: 12px;
        }
    }

    /* PRINT */
    @media print {
        .priority-page .back-button {
            display: none;
        }

        .priority-page .page-background {
            background: #ffffff;
            border: none;
            padding: 0;
        }

        .priority-page .main-card,
        .priority-page .analysis-card {
            box-shadow: none;
        }
    }

/* =========================================================
   STRONGER BOX OUTLINES FOR BETTER VISIBILITY
========================================================= */

/* Main content card */
.priority-page .main-card {
    border: 2px solid #b7c2cb;
}

/* Student information panel */
.priority-page .student-panel {
    border: 2px solid #aebbb4;
    border-left: 5px solid #198754;
}

/* Individual student information boxes */
.priority-page .student-detail {
    border: 1.5px solid #b8c3cc;
}

/* Previous and recent evaluation boxes */
.priority-page .evaluation-card {
    border: 2px solid #b5c0c9;
}

.priority-page .recent-card {
    border: 2px solid #d9aaaa;
}

/* Performance result box */
.priority-page .result-card {
    border: 2px solid #b5c0c9;
}

.priority-page .result-danger {
    border-color: #d98f8f;
}

.priority-page .result-warning {
    border-color: #d8c17b;
}

.priority-page .result-success {
    border-color: #8fc5a0;
}

/* Comparative analysis container */
.priority-page .analysis-card {
    border: 2px solid #aebdb2;
}

/* Comparison metric boxes */
.priority-page .metric-card {
    border: 1.5px solid #b5c0c9;
}

/* Semester trend summary boxes */
.priority-page .trend-card {
    border-width: 1.5px;
}

/* Semester trends line graph container */
.priority-page .trend-chart-container {
    border: 2px solid #aebdb8;
    box-shadow: 0 2px 8px rgba(31, 41, 55, 0.05);
}

/* Evaluation card headers */
.priority-page .evaluation-card-header {
    border-bottom: 1.5px solid #c2cbd2;
}

/* Comparative analysis header */
.priority-page .analysis-header {
    border-bottom: 1.5px solid #b5c9ba;
}

/* Information and empty-state boxes */
.priority-page .info-notice {
    border: 1.5px solid #a9c3d9;
}

.priority-page .empty-state {
    border: 1.5px dashed #aebbc5;
}
</style>

<div class="container-fluid mt-4 priority-page">

    <div class="page-background">

        <div class="mb-4">
            <a href="{{ route('students.index') }}" class="back-button">
                <i class="fa fa-arrow-left"></i>
                Back
            </a>
        </div>

        <div class="card main-card">

            <div class="card-header main-header">
                <div class="d-flex align-items-center">
                    <i class="fa fa-exclamation-triangle fa-2x me-3"></i>
                    <h4>Priority Alert</h4>
                </div>
            </div>

            <div class="card-body main-body">

                <!-- STUDENT INFORMATION -->
                <div class="section-title">
                    <span class="section-icon">
                        <i class="fa fa-user"></i>
                    </span>
                    Student Information
                </div>

                <div class="student-panel">

                    <div class="student-name">
                        {{ $student->last_name }}, {{ $student->first_name }}
                        @if($student->middle_name)
                            {{ $student->middle_name }}
                        @endif
                    </div>

                    <div class="row g-3">

                        <div class="col-md-4">
                            <div class="student-detail">
                                <span class="detail-label">Student No.</span>
                                <span class="detail-value">
                                    {{ $student->student_no }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="student-detail">
                                <span class="detail-label">Admission Year</span>
                                <span class="detail-value">
                                    {{ $student->admission_year }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="student-detail">
                                <span class="detail-label">Year Level</span>
                                <span class="detail-value">
                                    {{ $student->current_year_level }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- PERFORMANCE COMPARISON -->
                <div class="section-title">
                    <span class="section-icon">
                        <i class="fa fa-bar-chart"></i>
                    </span>
                    Performance Comparison
                </div>

                @if($previousEvaluation && $recentEvaluation)

                    <div class="row g-3">

                        <!-- PREVIOUS EVALUATION -->
                        <div class="col-lg-6">

                            <div class="evaluation-card">

                                <div class="evaluation-card-header">
                                    <strong>
                                        <i class="fa fa-history me-2"></i>
                                        Previous Evaluation
                                    </strong>
                                </div>

                                <div class="evaluation-card-body">

                                    @if($previousEvaluation->semester)

                                        <div class="evaluation-detail">
                                            <span class="evaluation-label">School Year</span>
                                            <span class="evaluation-value">
                                                {{ $previousEvaluation->semester->school_year }}
                                            </span>
                                        </div>

                                        <div class="evaluation-detail">
                                            <span class="evaluation-label">Semester</span>
                                            <span class="evaluation-value">
                                                {{ $previousEvaluation->semester->semester_name }}
                                            </span>
                                        </div>

                                    @endif

                                    <div class="evaluation-detail">
                                        <span class="evaluation-label">Failure</span>
                                        <span class="evaluation-value failure-number">
                                            {{ number_format($previousEvaluation->failure_percentage, 2) }}%
                                        </span>
                                    </div>

                                    <div class="evaluation-detail">
                                        <span class="evaluation-label">Status</span>
                                        <span class="evaluation-value">

                                            @if($previousEvaluation->academic_status == 'Below Treshold')
                                                <span class="badge bg-success status-badge">Below Treshold</span>
                                            @elseif($previousEvaluation->academic_status == 'Academic Warning')
                                                <span class="badge bg-warning text-dark status-badge">Academic Warning</span>
                                            @elseif($previousEvaluation->academic_status == 'Probation')
                                                <span class="badge bg-danger status-badge">Probation</span>
                                            @elseif($previousEvaluation->academic_status == 'Shifting Out')
                                                <span class="badge bg-dark status-badge">Shifting Out</span>
                                            @elseif($previousEvaluation->academic_status == 'Residency Risk')
                                                <span class="badge bg-info text-dark status-badge">Residency Risk</span>
                                            @else
                                                <span class="badge bg-secondary status-badge">
                                                    {{ $previousEvaluation->academic_status }}
                                                </span>
                                            @endif

                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- RECENT EVALUATION -->
                        <div class="col-lg-6">

                            <div class="evaluation-card recent-card">

                                <div class="evaluation-card-header">
                                    <strong>
                                        <i class="fa fa-clock-o me-2"></i>
                                        Recent Evaluation
                                    </strong>
                                </div>

                                <div class="evaluation-card-body">

                                    @if($recentEvaluation->semester)

                                        <div class="evaluation-detail">
                                            <span class="evaluation-label">School Year</span>
                                            <span class="evaluation-value">
                                                {{ $recentEvaluation->semester->school_year }}
                                            </span>
                                        </div>

                                        <div class="evaluation-detail">
                                            <span class="evaluation-label">Semester</span>
                                            <span class="evaluation-value">
                                                {{ $recentEvaluation->semester->semester_name }}
                                            </span>
                                        </div>

                                    @endif

                                    <div class="evaluation-detail">
                                        <span class="evaluation-label">Failure</span>
                                        <span class="evaluation-value failure-number">
                                            {{ number_format($recentEvaluation->failure_percentage, 2) }}%
                                        </span>
                                    </div>

                                    <div class="evaluation-detail">
                                        <span class="evaluation-label">Status</span>
                                        <span class="evaluation-value">

                                            @if($recentEvaluation->academic_status == 'Below Treshold')
                                                <span class="badge bg-success status-badge">Below Treshold</span>
                                            @elseif($recentEvaluation->academic_status == 'Academic Warning')
                                                <span class="badge bg-warning text-dark status-badge">Academic Warning</span>
                                            @elseif($recentEvaluation->academic_status == 'Probation')
                                                <span class="badge bg-danger status-badge">Probation</span>
                                            @elseif($recentEvaluation->academic_status == 'Shifting Out')
                                                <span class="badge bg-dark status-badge">Shifting Out</span>
                                            @elseif($recentEvaluation->academic_status == 'Residency Risk')
                                                <span class="badge bg-info text-dark status-badge">Residency Risk</span>
                                            @else
                                                <span class="badge bg-secondary status-badge">
                                                    {{ $recentEvaluation->academic_status }}
                                                </span>
                                            @endif

                                        </span>
                                    </div>

                                   @if($priorityAlert)
    <div class="priority-indicator">
        <i class="fa fa-exclamation-triangle"></i>
        <div>
            <strong>Priority Alert</strong>
        </div>
    </div>
@endif

                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- PERFORMANCE RESULT -->
                    @php
                        $previousFailure = $previousEvaluation->failure_percentage;
                        $recentFailure = $recentEvaluation->failure_percentage;
                        $difference = $recentFailure - $previousFailure;
                    @endphp

                    <div class="result-card
                        @if($difference > 0)
                            result-danger
                        @elseif($difference == 0)
                            result-warning
                        @else
                            result-success
                        @endif
                    ">

                        @if($difference > 0)

                            <h6 class="fw-bold mb-0">
                                <i class="fa fa-arrow-up me-2"></i>
                                Performance Worsened
                            </h6>

                            <p>
                                Failure increased from
                                <strong>{{ number_format($previousFailure, 2) }}%</strong>
                                to
                                <strong>{{ number_format($recentFailure, 2) }}%</strong>
                                (+{{ number_format(abs($difference), 2) }} percentage points).
                            </p>

                        @elseif($difference == 0)

                            <h6 class="fw-bold mb-0">
                                <i class="fa fa-minus me-2"></i>
                                No Change
                            </h6>

                            <p>
                                Failure remained at
                                <strong>{{ number_format($recentFailure, 2) }}%</strong>.
                            </p>

                        @else

                            <h6 class="fw-bold mb-0">
                                <i class="fa fa-arrow-down me-2"></i>
                                Performance Improved
                            </h6>

                            <p>
                                Failure decreased from
                                <strong>{{ number_format($previousFailure, 2) }}%</strong>
                                to
                                <strong>{{ number_format($recentFailure, 2) }}%</strong>
                                (−{{ number_format(abs($difference), 2) }} percentage points).
                            </p>

                        @endif

                    </div>

                @elseif($recentEvaluation)

                    <div class="info-notice">
                        <i class="fa fa-info-circle"></i>
                        <div>
                            <strong>First Evaluation</strong>
                            <div>No previous evaluation is available for comparison.</div>
                        </div>
                    </div>

                @else

                    <div class="empty-state">
                        <i class="fa fa-folder-open"></i>
                        <strong>No Evaluation Available</strong>
                    </div>

                @endif

                <!-- COMPARATIVE ANALYSIS -->
                @php
                    /*
                    |--------------------------------------------------------------------------
                    | BUILD CHRONOLOGICAL COMPARISON
                    |--------------------------------------------------------------------------
                    */

                    $comparisonHistory = $evaluations
                        ->filter(function ($evaluation) {
                            return $evaluation->semester !== null;
                        })
                        ->groupBy('semester_id')
                        ->map(function ($semesterEvaluations) {
                            return $semesterEvaluations
                                ->sortByDesc('status_id')
                                ->first();
                        })
                        ->sortBy(function ($evaluation) {
                            return (int) $evaluation->semester_id;
                        })
                        ->values();

                    $comparisonTransitions = collect();

                    if ($comparisonHistory->count() >= 2) {
                        for (
                            $comparisonIndex = 1;
                            $comparisonIndex < $comparisonHistory->count();
                            $comparisonIndex++
                        ) {
                            $previousComparison =
                                $comparisonHistory->get($comparisonIndex - 1);

                            $currentComparison =
                                $comparisonHistory->get($comparisonIndex);

                            $previousFailure =
                                (float) $previousComparison->failure_percentage;

                            $currentFailure =
                                (float) $currentComparison->failure_percentage;

                            $comparisonTransitions->push([
                                'previous' => $previousComparison,
                                'current' => $currentComparison,
                                'difference' => $currentFailure - $previousFailure,
                            ]);
                        }
                    }

                    $firstComparison = $comparisonHistory->first();
                    $latestComparison = $comparisonHistory->last();

                    $overallComparisonDifference = null;

                    if (
                        $firstComparison &&
                        $latestComparison &&
                        $comparisonHistory->count() >= 2
                    ) {
                        $overallComparisonDifference =
                            (float) $latestComparison->failure_percentage -
                            (float) $firstComparison->failure_percentage;
                    }
                @endphp

                @if($comparisonHistory->count() >= 2)

                    <div class="analysis-card">

                        <div class="analysis-header">
                            <i class="fa fa-line-chart"></i>
                            <h5>Comparative Analysis</h5>
                        </div>

                        <div class="analysis-body">

                            <div class="row g-3 mb-4">

                                <div class="col-lg-4 col-md-6">
                                    <div class="metric-card">
                                        <span class="metric-label">First Evaluation</span>

                                        <span class="metric-value">
                                            {{ number_format($firstComparison->failure_percentage, 2) }}%
                                        </span>

                                        <div class="metric-period">
                                            {{ optional($firstComparison->semester)->school_year }}
                                            —
                                            {{ optional($firstComparison->semester)->semester_name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <div class="metric-card">
                                        <span class="metric-label">Latest Evaluation</span>

                                        <span class="metric-value">
                                            {{ number_format($latestComparison->failure_percentage, 2) }}%
                                        </span>

                                        <div class="metric-period">
                                            {{ optional($latestComparison->semester)->school_year }}
                                            —
                                            {{ optional($latestComparison->semester)->semester_name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-12">

                                    @if($overallComparisonDifference < 0)

                                        <div class="trend-card trend-improved">
                                            <strong>
                                                <i class="fa fa-arrow-down me-2"></i>
                                                Overall Improvement
                                            </strong>

                                            <p>
                                                Failure decreased by
                                                {{ number_format(abs($overallComparisonDifference), 2) }}
                                                percentage points.
                                            </p>
                                        </div>

                                    @elseif($overallComparisonDifference > 0)

                                        <div class="trend-card trend-worsened">
                                            <strong>
                                                <i class="fa fa-arrow-up me-2"></i>
                                                Overall Worsening
                                            </strong>

                                            <p>
                                                Failure increased by
                                                {{ number_format($overallComparisonDifference, 2) }}
                                                percentage points.
                                            </p>
                                        </div>

                                    @else

                                        <div class="trend-card trend-neutral">
                                            <strong>
                                                <i class="fa fa-minus me-2"></i>
                                                No Overall Change
                                            </strong>

                                            <p>
                                                Failure percentage remained the same.
                                            </p>
                                        </div>

                                    @endif

                                </div>

                            </div>

                            <!-- SEMESTER TRENDS LINE GRAPH -->
                            <div class="table-heading">
                                <h5>Semester Trends</h5>
                                <p>
                                    Track the student's failure percentage across evaluated semesters.
                                </p>
                            </div>

                            <div class="trend-chart-container">
                                <canvas
                                    id="semesterTrendChart"
                                    role="img"
                                    aria-label="Line graph showing failure percentage across evaluated semesters">
                                </canvas>
                            </div>

                            <!-- LINE GRAPH SCRIPT -->
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                            <script>
                                (function () {
                                    function initializeSemesterTrendChart() {
                                        const chartCanvas = document.getElementById(
                                            'semesterTrendChart'
                                        );

                                        if (
                                            !chartCanvas ||
                                            typeof Chart === 'undefined'
                                        ) {
                                            return;
                                        }

                                        const semesterLabels = @json(
                                            $comparisonHistory->map(function ($evaluation) {
                                                return optional($evaluation->semester)->school_year
                                                    . ' - ' .
                                                    optional($evaluation->semester)->semester_name;
                                            })->values()
                                        );

                                        const failurePercentages = @json(
                                            $comparisonHistory->map(function ($evaluation) {
                                                return (float) $evaluation->failure_percentage;
                                            })->values()
                                        );

                                        new Chart(chartCanvas, {
                                            type: 'line',

                                            data: {
                                                labels: semesterLabels,

                                                datasets: [{
                                                    label: 'Failure Percentage',

                                                    data: failurePercentages,

                                                    borderColor: '#198754',
                                                    backgroundColor: 'rgba(25, 135, 84, 0.12)',

                                                    pointBackgroundColor: '#198754',
                                                    pointBorderColor: '#ffffff',
                                                    pointBorderWidth: 2,

                                                    pointRadius: 5,
                                                    pointHoverRadius: 7,

                                                    borderWidth: 3,
                                                    tension: 0.3,
                                                    fill: true
                                                }]
                                            },

                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,

                                                interaction: {
                                                    mode: 'index',
                                                    intersect: false
                                                },

                                                plugins: {
                                                    legend: {
                                                        display: true,
                                                        position: 'top',

                                                        labels: {
                                                            usePointStyle: true,
                                                            padding: 18
                                                        }
                                                    },

                                                    tooltip: {
                                                        callbacks: {
                                                            label: function (context) {
                                                                return 'Failure Percentage: ' +
                                                                    Number(context.parsed.y)
                                                                        .toFixed(2) + '%';
                                                            }
                                                        }
                                                    }
                                                },

                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        suggestedMax: 100,

                                                        title: {
                                                            display: true,
                                                            text: 'Failure Percentage (%)'
                                                        },

                                                        ticks: {
                                                            callback: function (value) {
                                                                return value + '%';
                                                            }
                                                        },

                                                        grid: {
                                                            color: '#edf0f2'
                                                        }
                                                    },

                                                    x: {
                                                        title: {
                                                            display: true,
                                                            text: 'School Year and Semester'
                                                        },

                                                        grid: {
                                                            display: false
                                                        },

                                                        ticks: {
                                                            autoSkip: false,
                                                            maxRotation: 45,
                                                            minRotation: 0
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    }

                                    if (document.readyState === 'loading') {
                                        document.addEventListener(
                                            'DOMContentLoaded',
                                            initializeSemesterTrendChart
                                        );
                                    } else {
                                        initializeSemesterTrendChart();
                                    }
                                })();
                            </script>

                        </div>
                    </div>

                @endif

            </div>
        </div>

    </div>
</div>

@endsection