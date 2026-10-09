<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <title>{{ $reportTitle }}</title>

    <style>

        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        |
        | 8.5 x 13 inch paper.
        | Matches the paper size and margin setup of the sample PDF.
        |
        |--------------------------------------------------------------------------
        */

        @page {

            size: 8.5in 13in;

            margin: 0;

        }


        /*
        |--------------------------------------------------------------------------
        | GLOBAL
        |--------------------------------------------------------------------------
        */

        * {

            box-sizing: border-box;

        }


        html,
        body {

            margin: 0;

            padding: 0;

        }


        body {

            font-family: DejaVu Sans, sans-serif;

            font-size: 11px;

            color: #000;

        }


        /*
        |--------------------------------------------------------------------------
        | REPORT CONTENT
        |--------------------------------------------------------------------------
        |
        | Same 1-inch left and right content margins as the sample PDF.
        |
        |--------------------------------------------------------------------------
        */

        .report-content {

            margin-left: 1in;

            margin-right: 1in;

        }


        /*
        |--------------------------------------------------------------------------
        | REPORT TITLE
        |--------------------------------------------------------------------------
        */

        .report-title {

            text-align: center;

            margin-top: 18px;

            margin-bottom: 4px;

        }


        .report-title h2 {

            margin: 0;

            font-size: 17px;

            font-weight: bold;

        }


        /*
        |--------------------------------------------------------------------------
        | REPORT SUBTITLE
        |--------------------------------------------------------------------------
        */

        .report-subtitle {

            text-align: center;

            font-size: 11px;

            margin-bottom: 16px;

        }


        /*
        |--------------------------------------------------------------------------
        | REPORT INFORMATION
        |--------------------------------------------------------------------------
        */

        .report-info {

            width: 100%;

            margin-bottom: 12px;

        }


        .report-info td {

            font-size: 10px;

            vertical-align: top;

        }


        .report-info .right {

            text-align: right;

        }


        /*
        |--------------------------------------------------------------------------
        | STUDENT TABLE
        |--------------------------------------------------------------------------
        */

        table.student-table {

            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;

        }


        table.student-table th,
        table.student-table td {

            border: 1px solid #000;

            padding: 7px 6px;

            vertical-align: middle;

        }


        table.student-table th {

            text-align: center;

            font-weight: bold;

            background-color: #eeeeee;

        }


        table.student-table td {

            font-size: 10px;

        }


        /*
        |--------------------------------------------------------------------------
        | TABLE COLUMNS
        |--------------------------------------------------------------------------
        */

        .no {

            width: 7%;

            text-align: center;

        }


        .name {

            width: 38%;

        }


        .year {

            width: 15%;

            text-align: center;

        }


        .status {

            width: 25%;

            text-align: center;

        }


        .percentage {

            width: 15%;

            text-align: center;

        }


        /*
        |--------------------------------------------------------------------------
        | EMPTY RESULT
        |--------------------------------------------------------------------------
        */

        .empty {

            text-align: center;

            padding: 18px !important;

        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER NOTE
        |--------------------------------------------------------------------------
        */

        .footer-note {

            margin-top: 15px;

            font-size: 9px;

        }


        /*
        |--------------------------------------------------------------------------
        | PAGE BREAK
        |--------------------------------------------------------------------------
        */

        .page-break {

            page-break-after: always;

        }

    </style>

</head>

<body>

    {{-- =========================================================
         EXISTING REPORT HEADER
         ========================================================= --}}
    @include('notices.partials.header')


    {{-- =========================================================
         REPORT CONTENT
         ========================================================= --}}
    <div class="report-content">


        {{-- =====================================================
             REPORT TITLE
             ====================================================== --}}

        <div class="report-title">

            <h2>
                {{ $reportTitle }}
            </h2>

        </div>


        {{-- =====================================================
             REPORT PERIOD
             ====================================================== --}}

        <div class="report-subtitle">

            {{ $reportSubtitle }}

        </div>


        {{-- =====================================================
             REPORT INFORMATION
             ====================================================== --}}

        <table class="report-info">

            <tr>

                <td>

                    <strong>Total Students:</strong>

                    {{ $records->count() }}

                </td>


                <td class="right">

                    <strong>Date Generated:</strong>

                    {{ $generatedAt->format('F d, Y h:i A') }}

                </td>

            </tr>

        </table>


        {{-- =====================================================
             STUDENT LIST
             ====================================================== --}}

        <table class="student-table">

            <thead>

                <tr>

                    <th class="no">
                        No.
                    </th>

                    <th class="name">
                        Complete Name
                    </th>

                    <th class="year">
                        Year Level
                    </th>

                    <th class="status">
                        Academic Standing / Status
                    </th>

                    <th class="percentage">
                        Failure Percentage
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($records as $index => $record)

                    @php

                        $student = $record->student;


                        /*
                        |--------------------------------------------------------------------------
                        | COMPLETE NAME
                        |--------------------------------------------------------------------------
                        */

                        $completeName = '';

                        if ($student) {

                            $completeName = trim(

                                ($student->last_name ?? '') .
                                ', ' .
                                ($student->first_name ?? '') .
                                ' ' .
                                ($student->middle_name ?? '')

                            );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | YEAR LEVEL
                        |--------------------------------------------------------------------------
                        */

                        $yearLevel = $student
                            ? ($student->current_year_level ?? '—')
                            : '—';


                        /*
                        |--------------------------------------------------------------------------
                        | ACADEMIC STATUS
                        |--------------------------------------------------------------------------
                        */

                        $status = $record->academic_status ?? '—';


                        /*
                        |--------------------------------------------------------------------------
                        | FAILURE PERCENTAGE
                        |--------------------------------------------------------------------------
                        */

                        $failurePercentage = $record->failure_percentage !== null

                            ? number_format(

                                (float) $record->failure_percentage,

                                2

                            ) . '%'

                            : '—';

                    @endphp


                    <tr>

                        <td class="no">

                            {{ $index + 1 }}

                        </td>


                        <td class="name">

                            {{ $completeName ?: '—' }}

                        </td>


                        <td class="year">

                            {{ $yearLevel }}

                        </td>


                        <td class="status">

                            {{ $status }}

                        </td>


                        <td class="percentage">

                            {{ $failurePercentage }}

                        </td>

                    </tr>


                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="empty"
                        >

                            No students were found for the selected report criteria.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>



    </div>

</body>
</html>