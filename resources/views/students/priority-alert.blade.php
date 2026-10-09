@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <a href="{{ route('students.index') }}"
               class="btn btn-light">

                <i class="fa fa-arrow-left"></i>

                Back

            </a>

            <p></p>

    <!-- HEADER -->
    <div class="card shadow border-0">

        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">

            <h4 class="mb-0">

                <i class="fa fa-exclamation-triangle"></i>

                Priority Alert

            </h4>


        </div>


        <div class="card-body">

            <!-- STUDENT INFORMATION -->

            <div class="alert alert-light border">

                <h5 class="fw-bold mb-2">

                    {{ $student->last_name }},
                    {{ $student->first_name }}

                    @if($student->middle_name)
                        {{ $student->middle_name }}
                    @endif

                </h5>

                <div>

                    <strong>Student No:</strong>

                    {{ $student->student_no }}

                </div>

                <div>

                    <strong>Admission Year:</strong>

                    {{ $student->admission_year }}

                </div>

                <div>

                    <strong>Year Level:</strong>

                    {{ $student->current_year_level }}

                </div>

            </div>


            


            <!-- ====================================================== -->
            <!-- PREVIOUS VS RECENT EVALUATION -->
            <!-- ====================================================== -->

            @if($previousEvaluation && $recentEvaluation)

                <h5 class="fw-bold mt-4 mb-3">

                    Performance Comparison

                </h5>


                <div class="row">

                    <!-- PREVIOUS -->

                    <div class="col-md-6 mb-3">

                        <div class="card border shadow-sm h-100">

                            <div class="card-header bg-secondary text-white">

                                <strong>

                                    Previous Evaluation

                                </strong>

                            </div>


                            <div class="card-body">

                                @if($previousEvaluation->semester)

                                    <p>

                                        <strong>School Year:</strong>

                                        {{ $previousEvaluation->semester->school_year }}

                                    </p>

                                    <p>

                                        <strong>Semester:</strong>

                                        {{ $previousEvaluation->semester->semester_name }}

                                    </p>

                                @endif


                                <p>

                                    <strong>Failure:</strong>

                                    {{ number_format(
                                        $previousEvaluation->failure_percentage,
                                        2
                                    ) }}%

                                </p>


                                <p>

                                    <strong>Status:</strong>

                                    @if(
                                        $previousEvaluation->academic_status
                                        == 'Regular'
                                    )

                                        <span class="badge bg-success">

                                            Regular

                                        </span>

                                    @elseif(
                                        $previousEvaluation->academic_status
                                        == 'Academic Warning'
                                    )

                                        <span class="badge bg-warning text-dark">

                                            Academic Warning

                                        </span>

                                    @elseif(
                                        $previousEvaluation->academic_status
                                        == 'Probation'
                                    )

                                        <span class="badge bg-danger">

                                            Probation

                                        </span>

                                    @elseif(
                                        $previousEvaluation->academic_status
                                        == 'Shifting Out'
                                    )

                                        <span class="badge bg-dark">

                                            Shifting Out

                                        </span>

                                    @elseif(
                                        $previousEvaluation->academic_status
                                        == 'Residency Risk'
                                    )

                                        <span class="badge bg-info">

                                            Residency Risk

                                        </span>

                                    @else

                                        <span class="badge bg-secondary">

                                            {{ $previousEvaluation->academic_status }}

                                        </span>

                                    @endif

                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- ARROW -->

                    <div class="col-md-12 text-center d-md-none mb-3">

                        <i class="fa fa-arrow-down fa-2x text-danger"></i>

                    </div>


                    <!-- RECENT -->

                    <div class="col-md-6 mb-3">

                        <div class="card border-danger shadow-sm h-100">

                            <div class="card-header bg-danger text-white">

                                <strong>

                                    Recent Evaluation

                                </strong>

                            </div>


                            <div class="card-body">

                                @if($recentEvaluation->semester)

                                    <p>

                                        <strong>School Year:</strong>

                                        {{ $recentEvaluation->semester->school_year }}

                                    </p>

                                    <p>

                                        <strong>Semester:</strong>

                                        {{ $recentEvaluation->semester->semester_name }}

                                    </p>

                                @endif


                                <p>

                                    <strong>Failure:</strong>

                                    {{ number_format(
                                        $recentEvaluation->failure_percentage,
                                        2
                                    ) }}%

                                </p>


                                <p>

                                    <strong>Status:</strong>

                                    @if(
                                        $recentEvaluation->academic_status
                                        == 'Regular'
                                    )

                                        <span class="badge bg-success">

                                            Regular

                                        </span>

                                    @elseif(
                                        $recentEvaluation->academic_status
                                        == 'Academic Warning'
                                    )

                                        <span class="badge bg-warning text-dark">

                                            Academic Warning

                                        </span>

                                    @elseif(
                                        $recentEvaluation->academic_status
                                        == 'Probation'
                                    )

                                        <span class="badge bg-danger">

                                            Probation

                                        </span>

                                    @elseif(
                                        $recentEvaluation->academic_status
                                        == 'Shifting Out'
                                    )

                                        <span class="badge bg-dark">

                                            Shifting Out

                                        </span>

                                    @elseif(
                                        $recentEvaluation->academic_status
                                        == 'Residency Risk'
                                    )

                                        <span class="badge bg-info">

                                            Residency Risk

                                        </span>

                                    @else

                                        <span class="badge bg-secondary">

                                            {{ $recentEvaluation->academic_status }}

                                        </span>

                                    @endif

                                </p>


                                @if($recentEvaluation->priority_alert)

                                    <div class="alert alert-danger mt-3 mb-0">

                                        <i class="fa fa-exclamation-triangle"></i>

                                        <strong>
                                            Priority Alert
                                        </strong>

                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- SIMPLE PERFORMANCE RESULT -->
                <!-- ================================================== -->

                @php

                    $previousFailure =
                        $previousEvaluation->failure_percentage;

                    $recentFailure =
                        $recentEvaluation->failure_percentage;

                    $difference =
                        $recentFailure - $previousFailure;

                @endphp


                <div class="card mt-3 border-0 shadow-sm">

                    <div class="card-body text-center">

                        @if($difference > 0)

                            <div class="alert alert-danger mb-0">

                                <i class="fa fa-arrow-up"></i>

                                <strong>

                                    Academic performance worsened.

                                </strong>

                                <br>

                                Failure increased from

                                <strong>
                                    {{ number_format(
                                        $previousFailure,
                                        2
                                    ) }}%
                                </strong>

                                to

                                <strong>
                                    {{ number_format(
                                        $recentFailure,
                                        2
                                    ) }}%
                                </strong>.

                            </div>

                        @elseif($difference == 0)

                            <div class="alert alert-warning mb-0">

                                <i class="fa fa-minus"></i>

                                <strong>

                                    No improvement.

                                </strong>

                                <br>

                                Failure remained at

                                <strong>

                                    {{ number_format(
                                        $recentFailure,
                                        2
                                    ) }}%

                                </strong>.

                            </div>

                        @else

                            <div class="alert alert-success mb-0">

                                <i class="fa fa-arrow-down"></i>

                                <strong>

                                    Academic performance improved.

                                </strong>

                                <br>

                                Failure decreased from

                                <strong>

                                    {{ number_format(
                                        $previousFailure,
                                        2
                                    ) }}%

                                </strong>

                                to

                                <strong>

                                    {{ number_format(
                                        $recentFailure,
                                        2
                                    ) }}%

                                </strong>.

                            </div>

                        @endif

                    </div>

                </div>

            @elseif($recentEvaluation)

                <div class="alert alert-info mt-4">

                    <i class="fa fa-info-circle"></i>

                    This is the student's first recorded evaluation.
                    There is no previous evaluation available for comparison.

                </div>

            @else

                <div class="alert alert-info">

                    No evaluation history is available.

                </div>

            @endif


            <!-- ====================================================== -->
            <!-- ALL EVALUATIONS -->
            <!-- ====================================================== -->

            @if($evaluations->count() > 0)

                <hr class="my-4">

                <h5 class="fw-bold mb-3">

                    Complete Evaluation History

                </h5>


                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-dark">

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    School Year
                                </th>

                                <th>
                                    Semester
                                </th>

                                <th>
                                    Failure
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Priority Alert
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach(
                                $evaluations
                                ->sortByDesc(function ($evaluation) {

                                    return optional(
                                        $evaluation->semester
                                    )->semester_id;

                                })
                                as $index => $evaluation
                            )

                                <tr>

                                    <td>

                                        {{ $loop->iteration }}

                                    </td>


                                    <td>

                                        {{ optional(
                                            $evaluation->semester
                                        )->school_year ?? 'N/A' }}

                                    </td>


                                    <td>

                                        {{ optional(
                                            $evaluation->semester
                                        )->semester_name ?? 'N/A' }}

                                    </td>


                                    <td>

                                        {{ number_format(
                                            $evaluation->failure_percentage,
                                            2
                                        ) }}%

                                    </td>


                                    <td>

                                        @if(
                                            $evaluation->academic_status
                                            == 'Regular'
                                        )

                                            <span class="badge bg-success">

                                                Regular

                                            </span>

                                        @elseif(
                                            $evaluation->academic_status
                                            == 'Academic Warning'
                                        )

                                            <span class="badge bg-warning text-dark">

                                                Academic Warning

                                            </span>

                                        @elseif(
                                            $evaluation->academic_status
                                            == 'Probation'
                                        )

                                            <span class="badge bg-danger">

                                                Probation

                                            </span>

                                        @elseif(
                                            $evaluation->academic_status
                                            == 'Shifting Out'
                                        )

                                            <span class="badge bg-dark">

                                                Shifting Out

                                            </span>

                                        @elseif(
                                            $evaluation->academic_status
                                            == 'Residency Risk'
                                        )

                                            <span class="badge bg-info">

                                                Residency Risk

                                            </span>

                                        @else

                                            <span class="badge bg-secondary">

                                                {{ $evaluation->academic_status }}

                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        @if($evaluation->priority_alert)

                                            <span class="badge bg-danger">

                                                <i class="fa fa-exclamation-triangle"></i>

                                                YES

                                            </span>

                                        @else

                                            <span class="badge bg-success">

                                                NO

                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif


        </div>

    </div>

</div>

@endsection