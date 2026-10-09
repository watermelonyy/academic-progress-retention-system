@php

    /*
    |--------------------------------------------------------------------------
    | STUDENT NAME
    |--------------------------------------------------------------------------
    */

    $middleInitial = '';

    if (!empty($student->middle_name)) {

        $middleInitial =
            strtoupper(
                substr(
                    trim($student->middle_name),
                    0,
                    1
                )
            ) . '.';

    }


    $fullName = trim(

        ($student->first_name ?? '') . ' ' .

        (
            $middleInitial
                ? $middleInitial . ' '
                : ''
        ) .

        ($student->last_name ?? '')

    );


    /*
    |--------------------------------------------------------------------------
    | YEAR LEVEL
    |--------------------------------------------------------------------------
    */

    $yearLevel =
        $student->current_year_level ?? '';


    switch ((string) $yearLevel) {

        case '1':
        case '1st':
        case '1st Year':

            $yearLevelText =
                '1st Year';

            break;


        case '2':
        case '2nd':
        case '2nd Year':

            $yearLevelText =
                '2nd Year';

            break;


        case '3':
        case '3rd':
        case '3rd Year':

            $yearLevelText =
                '3rd Year';

            break;


        case '4':
        case '4th':
        case '4th Year':

            $yearLevelText =
                '4th Year';

            break;


        default:

            $yearLevelText =
                $yearLevel ?: 'Year Level';

            break;

    }


    /*
    |--------------------------------------------------------------------------
    | LATEST STATUS
    |--------------------------------------------------------------------------
    */

    $latestStatus =
        $latestStatus
        ?? ($student->latestStatus ?? null);


    /*
    |--------------------------------------------------------------------------
    | SEMESTER
    |--------------------------------------------------------------------------
    */

    $noticeSemester = null;


    if ($latestStatus) {

        $noticeSemester =
            $latestStatus->semester ?? null;

    }


    $semesterName =
        $noticeSemester->semester_name
        ?? $noticeSemester->name
        ?? 'Semester';


    /*
    |--------------------------------------------------------------------------
    | SCHOOL YEAR
    |--------------------------------------------------------------------------
    */

    $schoolYear =
        $noticeSemester->school_year
        ?? 'School Year';


    /*
    |--------------------------------------------------------------------------
    | SUBJECTS
    |--------------------------------------------------------------------------
    */

    $subjects =
        collect($subjects ?? []);


    /*
    |--------------------------------------------------------------------------
    | TOTAL UNITS
    |--------------------------------------------------------------------------
    */

    $totalUnits =
        (float) ($totalUnits ?? 0);

@endphp


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Probationary Agreement Contract
    </title>


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

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 11pt;

            color: #000;

            line-height: 1.3;

        }


        /*
        |--------------------------------------------------------------------------
        | REMOVE HEADER HORIZONTAL LINE
        |--------------------------------------------------------------------------
        */

        hr {

            display: none;

            border: 0;

            margin: 0;

            padding: 0;

        }


        /*
        |--------------------------------------------------------------------------
        | MAIN CONTENT
        |--------------------------------------------------------------------------
        */

        .notice-content {

            margin-left: 1in;

            margin-right: 1in;

        }


        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        .notice-title {

            text-align: center;

            font-family:
                "Arial Black",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12pt;

            font-weight: bold;

            line-height: 1;

            margin-top: 18px;

            margin-bottom: 18px;

        }


        /*
        |--------------------------------------------------------------------------
        | DATE
        |--------------------------------------------------------------------------
        */

        .notice-date {

            margin: 0 0 14px 0;

            font-size: 11pt;

            line-height: 1.1;

        }


        /*
        |--------------------------------------------------------------------------
        | STUDENT INFORMATION
        |--------------------------------------------------------------------------
        */

        .student-information {

            width: 100%;

            margin: 0 0 14px 0;

            border-collapse: collapse;

        }


        .student-information td {

            padding: 0 0 3px 0;

            vertical-align: top;

            font-size: 11pt;

            line-height: 1.1;

        }


        .student-information .label {

            width: 145px;

            font-weight: bold;

        }


        /*
        |--------------------------------------------------------------------------
        | BODY TEXT
        |--------------------------------------------------------------------------
        */

        .body-text {

            width: 100%;

            font-size: 11pt;

            line-height: 1.1;

            text-align: justify;

        }


        .body-text p {

            margin: 0 0 9px 0;

            padding: 0;

            text-align: justify;

        }


        /*
        |--------------------------------------------------------------------------
        | CONDITIONS
        |--------------------------------------------------------------------------
        */

        .conditions {

            margin: 0 0 10px 25px;

            padding: 0;

        }


        .conditions li {

            margin: 0 0 4px 0;

            padding: 0;

            line-height: 1.1;

            text-align: justify;

        }


        /*
        |--------------------------------------------------------------------------
        | STUDY LOAD TITLE
        |--------------------------------------------------------------------------
        */

        .study-load-title {

            font-weight: bold;

            margin-top: 10px;

            margin-bottom: 4px;

            font-size: 11pt;

            line-height: 1.1;

        }


        /*
        |--------------------------------------------------------------------------
        | STUDY LOAD TABLE
        |--------------------------------------------------------------------------
        */

        .study-table {

            width: 100%;

            border-collapse: collapse;

            border-spacing: 0;

            margin-top: 3px;

            margin-bottom: 8px;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 10pt;

            table-layout: fixed;

        }


        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER
        |--------------------------------------------------------------------------
        */

        .study-table th {

            background: #222;

            color: #fff;

            border: 1px solid #000;

            padding: 3px 5px;

            text-align: center;

            font-weight: bold;

            vertical-align: middle;

            line-height: 1.0;

        }


        /*
        |--------------------------------------------------------------------------
        | TABLE CELLS
        |--------------------------------------------------------------------------
        */

        .study-table td {

            border: 1px solid #000;

            padding: 3px 5px;

            vertical-align: middle;

            color: #000;

            line-height: 1.0;

        }


        /*
        |--------------------------------------------------------------------------
        | COLUMN WIDTHS
        |--------------------------------------------------------------------------
        */

        .study-table .number {

            width: 35px;

            text-align: center;

        }


        .study-table .course {

            width: 115px;

            text-align: left;

        }


        .study-table .description {

            text-align: left;

            width: auto;

        }


        .study-table .units {

            width: 55px;

            text-align: center;

        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL ROW
        |--------------------------------------------------------------------------
        */

        .study-table .total-row td {

            font-weight: bold;

            padding-top: 4px;

            padding-bottom: 4px;

        }


        /*
        |--------------------------------------------------------------------------
        | AGREEMENT
        |--------------------------------------------------------------------------
        */

        .agreement {

            margin-top: 10px;

            margin-bottom: 0;

            font-size: 11pt;

            line-height: 1.1;

            text-align: justify;

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE SECTION
        |--------------------------------------------------------------------------
        */

        .signature-lines {

            width: 100%;

            margin-top: 25px;

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE BLOCK
        |--------------------------------------------------------------------------
        */

        .signature-line {

            width: 55%;

            text-align: center;

            margin-left: 0;

        }


        /*
        |--------------------------------------------------------------------------
        | STUDENT SIGNATURE
        |--------------------------------------------------------------------------
        */

        .student-signature {

            margin-bottom: 60px;

        }


        /*
        |--------------------------------------------------------------------------
        | PARENT/GUARDIAN SIGNATURE
        |--------------------------------------------------------------------------
        */

        .parent-signature {

            margin-bottom: 50px;

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE LINE
        |--------------------------------------------------------------------------
        */

        .line {

            border-top: 1px solid #000;

            width: 100%;

            height: 1px;

            margin-bottom: 3px;

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE LABEL
        |--------------------------------------------------------------------------
        */

        .signature-label {

            font-size: 10.5pt;

            line-height: 1.1;

            text-align: center;

        }


        /*
        |--------------------------------------------------------------------------
        | APPROVAL
        |--------------------------------------------------------------------------
        */

        .approval {

            margin-top: 22px;

            font-size: 11pt;

            line-height: 1.1;

        }


        .approval-name {

            font-weight: bold;

            font-size: 11pt;

            text-transform: uppercase;

            margin: 0;

            line-height: 1.1;

        }


        .approval-position {

            font-size: 11pt;

            margin: 2px 0 0 0;

            line-height: 1.1;

        }


        /*
        |--------------------------------------------------------------------------
        | PREVENT BREAKING
        |--------------------------------------------------------------------------
        */

        .no-break {

            page-break-inside: avoid;

        }

    </style>

</head>


<body>


    <!-- =========================================================
         OFFICIAL HEADER
    ========================================================== -->

    @include('notices.partials.header')


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <div class="notice-content">


        <!-- =====================================================
             TITLE
        ====================================================== -->

        <div class="notice-title">

            PROBATIONARY AGREEMENT CONTRACT

        </div>


        <!-- =====================================================
             DATE
        ====================================================== -->

        <div class="notice-date">

            {{ now()->format('F d, Y') }}

        </div>


        <!-- =====================================================
             STUDENT INFORMATION
        ====================================================== -->

        <table class="student-information">


            <tr>

                <td class="label">

                    Student Name

                </td>

                <td>

                    :

                    {{ strtoupper($fullName) }}

                </td>

            </tr>


            <tr>

                <td class="label">

                    Year Level

                </td>

                <td>

                    :

                    BSIT – {{ $yearLevelText }}

                </td>

            </tr>


            <tr>

                <td class="label">

                    Semester/Year

                </td>

                <td>

                    :

                    {{ $semesterName }}

                    /

                    S.Y.

                    {{ $schoolYear }}

                </td>

            </tr>


        </table>


        <!-- =====================================================
             INTRODUCTION AND CONDITIONS
        ====================================================== -->

        <div class="body-text">


            <p>

                I, the undersigned student, acknowledge that I am
                currently under <strong>PROBATIONARY STATUS</strong>
                due to my academic performance and failure to meet
                the required academic standards prescribed by the
                Information Technology Education Department.

            </p>


            <p>

                In accordance with the Department Retention Policy,
                I agree to comply with the following conditions for
                my continued enrollment and readmission:

            </p>


            <ol class="conditions">


                <li>

                    <strong>
                        Limited Load:
                    </strong>

                    I am allowed to enroll in a maximum of
                    <strong>
                        fifteen (15) academic units
                    </strong>
                    only.

                </li>


                <li>

                    <strong>
                        Priority Subjects:
                    </strong>

                    I must prioritize re-enrolling in the subjects
                    that I previously failed before enrolling in
                    new subjects.

                </li>


                <li>

                    <strong>
                        Academic Performance:
                    </strong>

                    I must pass <strong>ALL</strong> subjects enrolled
                    during the semester. Failure in any subject may
                    result in further academic sanctions based on
                    the retention policy.

                </li>


                <li>

                    <strong>
                        Attendance:
                    </strong>

                    I will maintain regular attendance and comply
                    with the academic requirements of all enrolled
                    subjects.

                </li>


                <li>

                    <strong>
                        Counseling:
                    </strong>

                    I agree to coordinate with the appropriate
                    academic personnel or Guidance Office whenever
                    necessary.

                </li>


            </ol>


        </div>


        <!-- =====================================================
             STUDY LOAD TITLE
        ====================================================== -->

        <div class="study-load-title">

            Approved Study Load for this Semester:

        </div>


        <!-- =====================================================
             STUDY LOAD TABLE
        ====================================================== -->

        <table class="study-table">


            <thead>

                <tr>

                    <th class="number">

                        #

                    </th>


                    <th class="course">

                        Course No.

                    </th>


                    <th class="description">

                        Description

                    </th>


                    <th class="units">

                        Units

                    </th>

                </tr>

            </thead>


            <tbody>


                @foreach($subjects as $index => $subject)


                    <tr>


                        <td class="number">

                            {{ $index + 1 }}

                        </td>


                        <td class="course">

                            {{ $subject['subject_code'] ?? '' }}

                        </td>


                        <td class="description">

                            {{ $subject['subject_title'] ?? '' }}

                        </td>


                        <td class="units">

                            @if(
                                isset($subject['units'])
                                &&
                                $subject['units'] !== ''
                                &&
                                $subject['units'] !== null
                            )

                                {{ number_format(
                                    (float) $subject['units'],
                                    2
                                ) }}

                            @endif

                        </td>


                    </tr>


                @endforeach


                <!-- TOTAL -->

                <tr class="total-row">

                    <td colspan="3">

                        TOTAL

                    </td>


                    <td class="units">

                        {{ number_format(
                            $totalUnits,
                            2
                        ) }}

                    </td>

                </tr>


            </tbody>

        </table>


        <!-- =====================================================
             AGREEMENT
        ====================================================== -->

        <p class="agreement">

            I understand that violation of this contract will result
            in my <strong>Course Disqualification</strong> or
            <strong>Debarment</strong> from the College.

        </p>
<p></p>
<p></p>
<p></p>
        <!-- =====================================================
             SIGNATURES
        ====================================================== -->

        <div class="signature-lines">


            <!-- STUDENT -->

            <div class="signature-line student-signature">

                <div class="line"></div>

                <div class="signature-label">

                    Student's Name and Signature

                </div>

            </div>


            <!-- PARENT/GUARDIAN -->

            <div class="signature-line parent-signature">

                <div class="line"></div>

                <div class="signature-label">

                    Parent/Guardian's Name and Signature

                </div>

            </div>


        </div>


        <!-- =====================================================
             APPROVAL
        ====================================================== -->

        <div class="approval">

            Approved by:

            <br>
            <br>

            <div class="approval-name">

                RODOLFO A. LEAL JR., MIT

            </div>

            <div class="approval-position">

                Head, ITE Department

            </div>

        </div>


    </div>


</body>

</html>