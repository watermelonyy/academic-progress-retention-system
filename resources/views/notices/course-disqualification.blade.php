@php

    /*
    |--------------------------------------------------------------------------
    | STUDENT NAME
    |--------------------------------------------------------------------------
    */

    $middleInitial = '';

    if (!empty($student->middle_name)) {

        $middleInitial = strtoupper(
            substr(trim($student->middle_name), 0, 1)
        ) . '.';

    }

    $fullName = trim(
        ($student->first_name ?? '') . ' ' .
        ($middleInitial ? $middleInitial . ' ' : '') .
        ($student->last_name ?? '')
    );


    /*
    |--------------------------------------------------------------------------
    | YEAR LEVEL
    |--------------------------------------------------------------------------
    */

    $yearLevel = $student->current_year_level ?? '';

    switch ((string) $yearLevel) {

        case '1':
        case '1st':
        case '1st Year':
            $yearLevelText = '1st Year';
            break;

        case '2':
        case '2nd':
        case '2nd Year':
            $yearLevelText = '2nd Year';
            break;

        case '3':
        case '3rd':
        case '3rd Year':
            $yearLevelText = '3rd Year';
            break;

        case '4':
        case '4th':
        case '4th Year':
            $yearLevelText = '4th Year';
            break;

        default:
            $yearLevelText = $yearLevel ?: 'Year Level';
            break;
    }


    /*
    |--------------------------------------------------------------------------
    | LATEST STATUS
    |--------------------------------------------------------------------------
    */

    $latestStatus = $student->latestStatus ?? null;

    $noticeSemester = null;

    if ($latestStatus) {

        $noticeSemester = $latestStatus->semester ?? null;

    }


    /*
    |--------------------------------------------------------------------------
    | SEMESTER
    |--------------------------------------------------------------------------
    */

    $semesterName = $noticeSemester->semester_name
        ?? $noticeSemester->name
        ?? 'Semester';


    /*
    |--------------------------------------------------------------------------
    | SCHOOL YEAR
    |--------------------------------------------------------------------------
    */

    $schoolYear = $noticeSemester->school_year
        ?? 'School Year';


    /*
    |--------------------------------------------------------------------------
    | DISQUALIFICATION REASONS
    |--------------------------------------------------------------------------
    |
    | NoticeController sends:
    |
    | - Three-Strike Rule
    | - Permanent Debarment
    | - Maximum Residency
    |
    | More than one reason may be checked.
    |
    */

    $disqualificationReasons =
        is_array($disqualificationReasons ?? null)
            ? $disqualificationReasons
            : [];


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE REASONS
    |--------------------------------------------------------------------------
    */

    $normalizedReasons = collect($disqualificationReasons)
        ->map(function ($reason) {

            $reason = strtolower(
                trim((string) $reason)
            );

            $reason = str_replace(
                ['_', '-'],
                ' ',
                $reason
            );

            $reason = preg_replace(
                '/\s+/',
                ' ',
                $reason
            );

            return trim($reason);

        })
        ->values()
        ->toArray();


    /*
    |--------------------------------------------------------------------------
    | THREE-STRIKE RULE
    |--------------------------------------------------------------------------
    */

    $isThreeStrike = false;

    foreach ($normalizedReasons as $reason) {

        if (
            str_contains($reason, 'three strike') ||
            str_contains($reason, 'three time') ||
            str_contains($reason, '3rd attempt') ||
            str_contains($reason, 'third attempt') ||
            str_contains($reason, 'three attempts')
        ) {

            $isThreeStrike = true;

            break;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PERMANENT DEBARMENT
    |--------------------------------------------------------------------------
    |
    | This is automatically checked when the student failed ALL
    | subjects in the evaluated semester.
    |
    */

    $isPermanentDebarment = false;

    foreach ($normalizedReasons as $reason) {

        if (
            str_contains($reason, 'permanent debarment') ||
            str_contains($reason, 'failed all subjects') ||
            str_contains($reason, 'all subjects')
        ) {

            $isPermanentDebarment = true;

            break;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MAXIMUM RESIDENCY
    |--------------------------------------------------------------------------
    */

    $isMaximumResidency = false;

    foreach ($normalizedReasons as $reason) {

        if (
            str_contains($reason, 'maximum residency') ||
            str_contains($reason, 'exceeded maximum residency') ||
            str_contains($reason, 'six year') ||
            str_contains($reason, '6 year') ||
            str_contains($reason, 'residency limit') ||
            str_contains($reason, 'residency')
        ) {

            $isMaximumResidency = true;

            break;
        }
    }

@endphp


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Notice of Course Disqualification</title>


    <style>

        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        |
        | 8.5 x 13 inch paper.
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

            font-family: Arial, Helvetica, sans-serif;

            font-size: 12pt;

            color: #000;

            line-height: 1.5;

        }


        /*
        |--------------------------------------------------------------------------
        | NOTICE CONTENT
        |--------------------------------------------------------------------------
        */

        .notice-content {

            margin-left: 1in;

            margin-right: 1in;

        }


        /*
        |--------------------------------------------------------------------------
        | NOTICE TITLE
        |--------------------------------------------------------------------------
        */

        .notice-title {

            text-align: center;

            font-family: "Arial Black",
                         Arial,
                         Helvetica,
                         sans-serif;

            font-size: 12pt;

            font-weight: bold;

            line-height: 1;

            margin-top: 24px;

            margin-bottom: 26px;

        }


        /*
        |--------------------------------------------------------------------------
        | DATE
        |--------------------------------------------------------------------------
        */

        .notice-date {

            margin: 0 0 22px 0;

            font-size: 12pt;

            line-height: 1.15;

        }


        /*
        |--------------------------------------------------------------------------
        | RECIPIENT
        |--------------------------------------------------------------------------
        */

        .recipient {

            margin: 0 0 22px 0;

            font-size: 12pt;

            line-height: 1.15;

        }


        .recipient-row {

            margin: 0;

            padding: 0;

        }


        .recipient-name {

            font-weight: bold;

            text-transform: uppercase;

        }


        /*
        |--------------------------------------------------------------------------
        | GREETING
        |--------------------------------------------------------------------------
        */

        .greeting {

            margin: 0 0 16px 0;

            font-size: 12pt;

            line-height: 1.15;

        }


        /*
        |--------------------------------------------------------------------------
        | BODY TEXT
        |--------------------------------------------------------------------------
        */

        .body-text {

            width: 100%;

            font-size: 12pt;

            line-height: 1.15;

            text-align: justify;

        }


        .body-text p {

            margin: 0 0 14px 0;

            padding: 0;

            text-align: justify;

        }


        /*
        |--------------------------------------------------------------------------
        | REASON TABLE
        |--------------------------------------------------------------------------
        */

        .reason-table {

            width: 100%;

            border-collapse: collapse;

            margin: 5px 0 17px 0;

        }


        .reason-table tr {

            page-break-inside: avoid;

        }


        .reason-table td {

            vertical-align: top;

            padding-top: 4px;

            padding-bottom: 4px;

            font-size: 12pt;

            line-height: 1.15;

        }


        /*
        |--------------------------------------------------------------------------
        | CHECKBOX CELL
        |--------------------------------------------------------------------------
        */

        .checkbox-cell {

            width: 25px;

            min-width: 25px;

            padding-right: 6px;

            text-align: left;

            vertical-align: top;

        }


        /*
        |--------------------------------------------------------------------------
        | SMALL BOX
        |--------------------------------------------------------------------------
        */

        .checkbox {

            display: inline-block;

            width: 12px;

            height: 12px;

            border: 1px solid #000;

            text-align: center;

            vertical-align: middle;

            line-height: 10px;

            font-family: Arial, Helvetica, sans-serif;

            font-size: 10px;

            font-weight: bold;

        }


        /*
        |--------------------------------------------------------------------------
        | REASON TEXT
        |--------------------------------------------------------------------------
        */

        .reason-text {

            text-align: justify;

            line-height: 1.15;

            padding-left: 2px;

        }


        .reason-title {

            font-weight: bold;

        }


        /*
        |--------------------------------------------------------------------------
        | FINAL NOTICE
        |--------------------------------------------------------------------------
        */

        .final-notice {

            width: 100%;

            font-size: 12pt;

            line-height: 1.15;

            text-align: justify;

        }


        .final-notice p {

            margin: 0 0 14px 0;

            padding: 0;

            text-align: justify;

        }


        /*
        |--------------------------------------------------------------------------
        | CLOSING
        |--------------------------------------------------------------------------
        */

        .closing {

            margin-top: 28px;

            margin-bottom: 0;

            font-size: 12pt;

            line-height: 1.15;

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE
        |--------------------------------------------------------------------------
        */

        .signature {

            margin-top: 42px;

            width: 100%;

        }


        .signature-name {

            font-weight: bold;

            font-size: 12pt;

            text-transform: uppercase;

            margin: 0;

            line-height: 1.15;

        }


        .signature-position {

            font-size: 12pt;

            margin: 2px 0 0 0;

            line-height: 1.15;

        }

    </style>

</head>


<body>


    <!-- =========================================================
         OFFICIAL HEADER
         ========================================================== -->

    @include('notices.partials.header')


    <!-- =========================================================
         NOTICE CONTENT
         ========================================================== -->

    <div class="notice-content">


        <!-- =====================================================
             NOTICE TITLE
             ====================================================== -->

        <div class="notice-title">

            NOTICE OF COURSE DISQUALIFICATION

        </div>


        <!-- =====================================================
             DATE
             ====================================================== -->

        <div class="notice-date">

            {{ now()->format('F d, Y') }}

        </div>


        <!-- =====================================================
             RECIPIENT
             ====================================================== -->

        <div class="recipient">

            <div class="recipient-row">

                <strong>To:</strong>

                <span class="recipient-name">

                    {{ $fullName }}

                </span>

            </div>


            <div class="recipient-row">

                <strong>ID Number:</strong>

                {{ $student->student_no }}

            </div>


            <div class="recipient-row">

                <strong>Year Level:</strong>

                BSIT – {{ $yearLevelText }}

            </div>

        </div>


        <!-- =====================================================
             GREETING
             ====================================================== -->

        <div class="greeting">

            Dear Student,

        </div>


        <!-- =====================================================
             INTRODUCTION
             ====================================================== -->

        <div class="body-text">

            <p>

                We regret to inform you that based on the evaluation of
                your academic records and in accordance with the academic
                retention policies of the Information Technology Education
                Department, you have incurred an academic deficiency that
                requires further action.

            </p>


            <p>

                Based on your academic record, the applicable reason for
                your disqualification is indicated below:

            </p>

        </div>


        <!-- =====================================================
             DISQUALIFICATION REASONS
             ====================================================== -->

        <table class="reason-table">


            <!-- =================================================
                 THREE-STRIKE RULE
                 ================================================== -->

            <tr>

                <td class="checkbox-cell">

                    <span class="checkbox">

                        @if($isThreeStrike)
                            ✓
                        @endif

                    </span>

                </td>


                <td class="reason-text">

                    <span class="reason-title">

                        Three-Strike Rule:

                    </span>

                    Failed the same major subject three (3) times.

                </td>

            </tr>


            <!-- =================================================
                 PERMANENT DEBARMENT
                 ================================================== -->

            <tr>

                <td class="checkbox-cell">

                    <span class="checkbox">

                        @if($isPermanentDebarment)
                            ✓
                        @endif

                    </span>

                </td>


                <td class="reason-text">

                    <span class="reason-title">

                        Permanent Debarment:

                    </span>

                    Failed all subjects in the evaluated academic load.

                </td>

            </tr>


            <!-- =================================================
                 MAXIMUM RESIDENCY
                 ================================================== -->

            <tr>

                <td class="checkbox-cell">

                    <span class="checkbox">

                        @if($isMaximumResidency)
                            ✓
                        @endif

                    </span>

                </td>


                <td class="reason-text">

                    <span class="reason-title">

                        Maximum Residency:

                    </span>

                    Exceeded the maximum residency period of six (6) years.

                </td>

            </tr>


        </table>


        <!-- =====================================================
             FINAL NOTICE
             ====================================================== -->

        <div class="final-notice">


            <p>

                In line with the College Retention Policy, you are hereby

                <strong>

                    DISQUALIFIED

                </strong>

                from continuing the

                <strong>

                    Bachelor of Science in Information Technology (BSIT)

                </strong>

                program effective immediately.

            </p>


            <p>

                We advise you to proceed to the Guidance Office to discuss
                shifting to another program where your skills and aptitudes
                may be better utilized. We wish you the best in your future
                endeavors.

            </p>


        </div>


        <!-- =====================================================
             CLOSING
             ====================================================== -->

        <div class="closing">

            Respectfully,

        </div>


        <!-- =====================================================
             SIGNATURE
             ====================================================== -->

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