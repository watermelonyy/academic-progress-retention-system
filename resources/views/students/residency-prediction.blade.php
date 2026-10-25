
@extends('layouts.app')

@section('content')

<style>
    .residency-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 28px 16px 45px;
        color: #344054;
    }

    .residency-header {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        background: #f8f9fa;
        border: 1px solid #dee3e7;
        border-radius: 15px;
        padding: 24px 25px;
        margin-bottom: 22px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .08);
    }

    .residency-back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 13px;
        border: 1px solid #cfd6dc;
        border-radius: 8px;
        background: #ffffff;
        color: #475467;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: background-color .2s ease, border-color .2s ease,
                    color .2s ease, transform .2s ease;
    }

    .residency-back-link:hover {
        background: #eef2f5;
        border-color: #adb8c2;
        color: #25313c;
        transform: translateX(-2px);
    }

    .residency-heading-content {
        width: 100%;
    }

    .residency-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
    }

    .residency-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 11px;
        background: #e3f3e9;
        color: #198754;
        font-size: 19px;
    }

    .residency-title {
        margin: 0 0 6px;
        color: #198754;
        font-size: 25px;
        font-weight: 750;
    }

    .residency-subtitle {
        margin: 0;
        color: #667085;
        font-size: 14px;
        line-height: 1.7;
    }

    .residency-card {
        background: #f8f9fa;
        border: 1px solid #dfe4e8;
        border-radius: 14px;
        padding: 23px;
        margin-bottom: 20px;
        box-shadow: 0 4px 13px rgba(0, 0, 0, .06);
    }

    .residency-card-title {
        margin: 0 0 18px;
        font-size: 18px;
        font-weight: 750;
        color: #344054;
    }

    .residency-student-name {
        margin: 0 0 5px;
        font-size: 21px;
        font-weight: 750;
        color: #252a2e;
        overflow-wrap: anywhere;
    }

    .residency-student-number {
        color: #667085;
        font-size: 14px;
    }

    .residency-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
    }

    .residency-metric {
        min-height: 115px;
        padding: 18px;
        border: 1px solid #dce2e6;
        border-radius: 11px;
        background: #eef1f3;
        transition: border-color .2s ease, box-shadow .2s ease,
                    transform .2s ease;
    }

    .residency-metric:hover {
        border-color: #b9c8bf;
        box-shadow: 0 5px 14px rgba(25, 135, 84, .08);
        transform: translateY(-2px);
    }

    .residency-metric-label {
        display: block;
        margin-bottom: 9px;
        font-size: 12px;
        font-weight: 750;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .residency-metric-value {
        font-size: 23px;
        font-weight: 750;
        color: #252a2e;
        overflow-wrap: anywhere;
    }

    .residency-summary-note {
        margin: 16px 0 0;
        padding: 12px 14px;
        border-left: 4px solid #198754;
        border-radius: 7px;
        background: #edf6f0;
        color: #475467;
        font-size: 13px;
        line-height: 1.65;
    }

    .residency-result {
        padding: 18px 20px;
        border-radius: 11px;
        border: 1px solid;
        margin-top: 20px;
        line-height: 1.7;
    }

    .residency-result-risk {
        background: #fff4df;
        border-color: #edcf8a;
        color: #795500;
    }

    .residency-result-safe {
        background: #e8f6ed;
        border-color: #b7ddc5;
        color: #17643f;
    }

    .residency-result-unknown {
        background: #eef1f4;
        border-color: #d3d9df;
        color: #475467;
    }

    .residency-reason-list {
        padding-left: 22px;
        margin: 0;
    }

    .residency-reason-list li {
        margin-bottom: 12px;
        line-height: 1.7;
    }

    .residency-reason-list li:last-child {
        margin-bottom: 0;
    }

    .residency-subject-list {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 14px;
    }

    .residency-subject {
        display: inline-block;
        padding: 7px 11px;
        border-radius: 7px;
        background: #fff0dc;
        border: 1px solid #efd0a5;
        color: #8a4b08;
        font-size: 13px;
        font-weight: 650;
        overflow-wrap: anywhere;
    }

    .residency-note {
        color: #667085;
        font-size: 13px;
        line-height: 1.7;
        margin: 14px 0 0;
    }

    .residency-page .btn {
        border-radius: 8px;
        font-weight: 650;
        padding: 9px 14px;
    }

    @media (max-width: 768px) {
        .residency-metrics {
            grid-template-columns: 1fr;
        }

        .residency-header {
            padding: 18px;
        }

        .residency-title {
            font-size: 21px;
        }

        .residency-title-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
        }

        .residency-back-link {
            max-width: 100%;
        }

        .residency-card {
            padding: 18px;
        }

        .residency-metric-value {
            font-size: 21px;
        }
    }
</style>

<div class="residency-page">

    {{-- PAGE HEADER --}}

    <div class="residency-header">

        <a
            href="{{ route('students.show', $student->student_id) }}"
            class="residency-back-link"
        >
            <i class="fa fa-arrow-left"></i>
            Back to Student Record
        </a>

        <div class="residency-heading-content">

            <div class="residency-title-row">

                <span class="residency-title-icon">
                    <i class="fa fa-clock"></i>
                </span>

                <h1 class="residency-title">
                    Residency Prediction
                </h1>

            </div>

        </div>

    </div>


    {{-- STUDENT INFORMATION --}}

    <div class="residency-card">

        <h5 class="residency-card-title">
            <i class="fa fa-user-graduate me-2 text-success"></i>
            Student Information
        </h5>

        <h2 class="residency-student-name">

            {{ $student->last_name }},
            {{ $student->first_name }}

            @if($student->middle_name)
                {{ $student->middle_name }}
            @endif

            @if($residencyRisk)
                <span
                    class="text-warning ms-1"
                    title="Projected residency reaches or exceeds the six-year limit"
                >
                    <i class="fa fa-clock"></i>
                </span>
            @endif

        </h2>

        <div class="residency-student-number">
            Student No.:
            <strong>{{ $student->student_no }}</strong>
        </div>

        <div class="mt-2">

            <span class="text-muted">Admission Year:</span>
            <strong>{{ $student->admission_year }}</strong>

            <span class="mx-2 text-muted">|</span>

            <span class="text-muted">Year Level:</span>
            <strong>{{ $student->current_year_level }}</strong>

        </div>

    </div>


    {{-- RESIDENCY SUMMARY --}}

    <div class="residency-card">

        <h5 class="residency-card-title">
            <i class="fa fa-chart-line me-2 text-success"></i>
            Residency Summary
        </h5>

        <div class="residency-metrics">

            <div class="residency-metric">

                <span class="residency-metric-label">
                    Projected Residency
                </span>

                <div class="residency-metric-value">

                    @if($projectedResidencyYears !== null)

                        {{ $projectedResidencyYears }}

                        {{ $projectedResidencyYears == 1
                            ? 'school year'
                            : 'school years' }}

                    @else

                        Not Available

                    @endif

                </div>

            </div>


            <div class="residency-metric">

                <span class="residency-metric-label">
                    Six-Year Residency Limit
                </span>

                <div class="residency-metric-value">
                    6 School Years
                </div>

            </div>


            <div class="residency-metric">

                <span class="residency-metric-label">
                    Years Spent
                </span>

                <div class="residency-metric-value">

                    @if($yearsSpent !== null)

                        {{ $yearsSpent }}

                        {{ $yearsSpent == 1
                            ? 'school year'
                            : 'school years' }}

                    @else

                        Not Available

                    @endif

                </div>

            </div>

        </div>




        {{-- PREDICTION RESULT --}}

        @if($projectedResidencyYears === null)

            <div class="residency-result residency-result-unknown">

                <strong>
                    <i class="fa fa-info-circle me-2"></i>
                    Prediction Unavailable
                </strong>

                <div>
                    A residency prediction cannot be calculated because
                    the student has no available evaluated semester.
                </div>

            </div>

        @elseif($residencyRisk)

            <div class="residency-result residency-result-risk">

                <strong>
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    Predicted to Reach or Exceed Six Years
                </strong>

                <div>

                    The existing residency calculation projects

                    <strong>
                        {{ $projectedResidencyYears }} school years.
                    </strong>

                    This reaches or exceeds the six-year residency limit.

                </div>

                @if($residencyExceeded)

                    <div class="mt-2">
                        The student has already exceeded six school years
                        based on the latest evaluated school year.
                    </div>

                @else

                    <div class="mt-2">
                        The student has not yet exceeded six school years.
                    </div>

                @endif

            </div>

        @else

            <div class="residency-result residency-result-safe">

                <strong>
                    <i class="fa fa-check-circle me-2"></i>
                    Not Predicted to Reach Six Years
                </strong>

                <div>
                    Based on the existing calculation, the student's
                    projected residency is below the six-year limit.
                </div>

            </div>

        @endif

    </div>


    

</div>

@endsection