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
        */

        @page {
            size: 8.5in 13in;
            margin: 0;
        }

        /* GLOBAL */
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

        /* REPORT CONTENT */
        .report-content {
            margin-left: 1in;
            margin-right: 1in;
        }

        /* REPORT TITLE */
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

        /* REPORT SUBTITLE */
        .report-subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 16px;
        }

        /* REPORT INFORMATION */
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

        /* YEAR LEVEL SECTION */
        .year-level-section {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .year-level-heading {
            margin: 0 0 7px 0;
            padding: 7px 8px;
            font-size: 12px;
            font-weight: bold;
            text-align: left;
            background-color: #e8e8e8;
            border: 1px solid #000;
        }

        /* STUDENT TABLE */
        table.student-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 14px;
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

        /* TABLE COLUMNS */
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

        /* EMPTY RESULT */
        .empty {
            text-align: center;
            padding: 18px !important;
        }

        /* FOOTER NOTE */
        .footer-note {
            margin-top: 15px;
            font-size: 9px;
        }

        /* CLOSING AND DEPARTMENT HEAD SIGNATURE */
        .closing {
            margin-top: 35px;
            margin-bottom: 35px;
            font-size: 11px;
        }

        .signature {
            width: 100%;
            margin-top: 0;
            page-break-inside: avoid;
        }

        .signature-name {
            font-size: 11px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .signature-position {
            font-size: 11px;
        }

        /* PAGE BREAK */
        .page-break {
            page-break-after: always;
        }

    </style>

</head>

<body>

    {{-- EXISTING REPORT HEADER --}}
    @include('notices.partials.header')

    <div class="report-content">

        {{-- REPORT TITLE --}}
        <div class="report-title">
            <h2>{{ $reportTitle }}</h2>
        </div>

        {{-- REPORT PERIOD --}}
        <div class="report-subtitle">
            {{ $reportSubtitle }}
        </div>

        {{-- REPORT INFORMATION --}}
        <table class="report-info">
            <tr>
                <td>
                    <strong>Total Students:</strong>
                    {{ $records->count() }}
                </td>
                <td class="right">
                    <strong>Date:</strong>
                    {{ $generatedAt->format('F d, Y') }}
                </td>
            </tr>
        </table>

        @php
            /*
            |--------------------------------------------------------------------------
            | GROUP STUDENTS BY YEAR LEVEL AND SORT YEAR LEVELS
            |--------------------------------------------------------------------------
            */

            $yearLevelGroups = $records->groupBy(function ($record) {
                $student = $record->student;
                $yearLevel = $student ? trim((string) ($student->current_year_level ?? '')) : '';

                return $yearLevel !== '' ? $yearLevel : 'Unspecified Year Level';
            });

            $yearLevelGroups = $yearLevelGroups->sortBy(function ($group, $yearLevel) {
                $normalizedYearLevel = strtolower(trim((string) $yearLevel));

                if (
                    str_contains($normalizedYearLevel, '1st') ||
                    str_contains($normalizedYearLevel, 'first') ||
                    preg_match('/(^|\D)1(\D|$)/', $normalizedYearLevel)
                ) {
                    return 1;
                }

                if (
                    str_contains($normalizedYearLevel, '2nd') ||
                    str_contains($normalizedYearLevel, 'second') ||
                    preg_match('/(^|\D)2(\D|$)/', $normalizedYearLevel)
                ) {
                    return 2;
                }

                if (
                    str_contains($normalizedYearLevel, '3rd') ||
                    str_contains($normalizedYearLevel, 'third') ||
                    preg_match('/(^|\D)3(\D|$)/', $normalizedYearLevel)
                ) {
                    return 3;
                }

                if (
                    str_contains($normalizedYearLevel, '4th') ||
                    str_contains($normalizedYearLevel, 'fourth') ||
                    preg_match('/(^|\D)4(\D|$)/', $normalizedYearLevel)
                ) {
                    return 4;
                }

                return 99;
            });
        @endphp

        {{-- STUDENT TABLES: ONE TABLE PER YEAR LEVEL --}}
        @forelse ($yearLevelGroups as $yearLevel => $groupRecords)

            <div class="year-level-section">

                <div class="year-level-heading">
                    {{ $yearLevel }}
                </div>

                <table class="student-table">

                    <thead>
                        <tr>
                            <th class="no">No.</th>
                            <th class="name">Complete Name</th>
                            <th class="year">Year Level</th>
                            <th class="status">Academic Standing / Status</th>
                            <th class="percentage">Failure Percentage</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($groupRecords->values() as $index => $record)

                            @php
                                $student = $record->student;

                                /* COMPLETE NAME */
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

                                /* YEAR LEVEL */
                                $studentYearLevel = $student
                                    ? ($student->current_year_level ?? '—')
                                    : '—';

                                /* ACADEMIC STATUS */
                                $status = $record->academic_status ?? '—';

                                /* FAILURE PERCENTAGE */
                                $failurePercentage = $record->failure_percentage !== null
                                    ? number_format((float) $record->failure_percentage, 2) . '%'
                                    : '—';
                            @endphp

                            <tr>
                                <td class="no">{{ $index + 1 }}</td>
                                <td class="name">{{ $completeName ?: '—' }}</td>
                                <td class="year">{{ $studentYearLevel }}</td>
                                <td class="status">{{ $status }}</td>
                                <td class="percentage">{{ $failurePercentage }}</td>
                            </tr>

                        @endforeach
                    </tbody>

                </table>

            </div>

        @empty

            <table class="student-table">
                <thead>
                    <tr>
                        <th class="no">No.</th>
                        <th class="name">Complete Name</th>
                        <th class="year">Year Level</th>
                        <th class="status">Academic Standing / Status</th>
                        <th class="percentage">Failure Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="empty">
                            No students were found for the selected report criteria.
                        </td>
                    </tr>
                </tbody>
            </table>

        @endforelse
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
        {{-- CLOSING --}}
        <div class="closing">
            Sincerely,
        </div>

        <p></p>
        <p></p>
        {{-- DEPARTMENT HEAD SIGNATURE --}}
        <div class="signature">
            <div class="signature-name">
                RODOLFO A. LEAL JR., MIT
            </div>

            <div class="signature-position">
                Head, ITE Department
            </div>
        </div>

    </div>

</body>
</html>
