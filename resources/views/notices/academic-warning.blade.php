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
    | FAILURE PERCENTAGE
    |--------------------------------------------------------------------------
    */

    $failurePercentage = $latestStatus->failure_percentage
        ?? 25;


    /*
    |--------------------------------------------------------------------------
    | FAILED SUBJECTS
    |--------------------------------------------------------------------------
    |
    | Academic Warning should show the failed subjects belonging to
    | the semester represented by the latest academic status.
    |
    |--------------------------------------------------------------------------
    */

    $warningFailedSubjects = collect();

    if (isset($failedSubjects)) {

        $warningFailedSubjects = collect($failedSubjects);

    } elseif (
        method_exists($student, 'academicGrades')
    ) {

        $warningFailedSubjects = collect(
            $student->academicGrades ?? []
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER FAILED SUBJECTS
    |--------------------------------------------------------------------------
    */

    $warningFailedSubjects = $warningFailedSubjects
        ->filter(function ($grade) use ($noticeSemester) {

            /*
            | Only failed grades.
            | Passing grade is 75 or above.
            */

            if (
                !isset($grade->grade) ||
                (float) $grade->grade >= 75
            ) {
                return false;
            }


            /*
            | If the notice has a semester, only include
            | subjects from that semester.
            */

            if (
                $noticeSemester &&
                isset($grade->semester_id)
            ) {

                return (int) $grade->semester_id ===
                    (int) $noticeSemester->semester_id;
            }

            return true;
        })
        ->values();

@endphp


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Notice of Academic Warning</title>


    <style>

        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        |
        | 8.5 x 13 inch paper.
        |
        | The page itself has no CSS margin so DomPDF will render the page
        | correctly. The actual notice content receives a fixed 1-inch
        | left and right margin below.
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
        | NOTICE CONTENT MARGINS
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
        | BODY
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
        | FAILED SUBJECTS
        |--------------------------------------------------------------------------
        */

        .failed-subjects-title {

            font-weight: bold;

            margin-top: 4px;

            margin-bottom: 4px;

            text-align: left;

        }


        .failed-subjects {

            margin: 0 0 14px 30px;

            padding: 0;

        }


        .failed-subjects li {

            margin: 0 0 2px 0;

            padding: 0;

            line-height: 1.15;

        }


        /*
        |--------------------------------------------------------------------------
        | ADVISORY LIST
        |--------------------------------------------------------------------------
        */

        .advisory-list {

            margin: 0 0 14px 30px;

            padding: 0;

        }


        .advisory-list li {

            margin: 0 0 2px 0;

            padding: 0;

            line-height: 1.15;

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


        /*
        |--------------------------------------------------------------------------
        | ACKNOWLEDGMENT
        |--------------------------------------------------------------------------
        */

        .acknowledgment {

            margin-top: 38px;

            font-size: 12pt;

            line-height: 1.15;

        }


        .acknowledgment-title {

            font-weight: bold;

            margin-bottom: 5px;

        }


        .acknowledgment-text {

            margin: 0;

            text-align: justify;

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE LINES
        |--------------------------------------------------------------------------
        */

        .signature-lines {

            width: 100%;

            margin-top: 42px;

        }


        /*
        |--------------------------------------------------------------------------
        | EACH SIGNATURE BLOCK
        |--------------------------------------------------------------------------
        */

        .signature-line {

            width: 55%;

            display: block;

            vertical-align: top;

            text-align: center;

            margin-left: 0;

        }


        /*
        |--------------------------------------------------------------------------
        | SPACE BETWEEN STUDENT AND PARENT/GUARDIAN
        |--------------------------------------------------------------------------
        */

        .signature-line.student-signature {

            margin-bottom: 38px;

        }


        /*
        |--------------------------------------------------------------------------
        | PARENT/GUARDIAN SIGNATURE
        |--------------------------------------------------------------------------
        */

        .signature-line.parent-signature {

            margin-bottom: 0;

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

            margin-bottom: 4px;

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE LABEL
        |--------------------------------------------------------------------------
        */

        .signature-label {

            font-size: 12pt;

            line-height: 1.15;

            text-align: center;

        }


        /*
        |--------------------------------------------------------------------------
        | CLEARFIX
        |--------------------------------------------------------------------------
        */

        .clearfix {

            clear: both;

        }


        /*
        |--------------------------------------------------------------------------
        | KEEP BLOCKS TOGETHER
        |--------------------------------------------------------------------------
        */

        .no-break {

            page-break-inside: avoid;

        }

    </style>

</head>


<body>


    <!-- =========================================================
         NEW NOTICE HEADER
    ========================================================== -->

    @include('notices.partials.header')


    <!-- =========================================================
         NOTICE CONTENT
         1-INCH LEFT AND RIGHT MARGINS
    ========================================================== -->

    <div class="notice-content">


        <!-- =========================================================
             NOTICE TITLE
        ========================================================== -->

        <div class="notice-title">

            NOTICE OF ACADEMIC WARNING

        </div>


        <!-- =========================================================
             DATE
        ========================================================== -->

        <div class="notice-date">

            {{ now()->format('F d, Y') }}

        </div>


        <!-- =========================================================
             RECIPIENT
        ========================================================== -->

        <div class="recipient">

            <div class="recipient-row">

                <strong>To:</strong>

                <span class="recipient-name">

                    {{ $fullName }}

                </span>

            </div>


            <div class="recipient-row">

                <strong>    ID Number:</strong>

                {{ $student->student_no }}

            </div>


            <div class="recipient-row">

                <strong>    Year Level:</strong>

                BSIT – {{ $yearLevelText }}

            </div>

        </div>


        <!-- =========================================================
             GREETING
        ========================================================== -->

        <div class="greeting">

            Dear Student,

        </div>


        <!-- =========================================================
             MAIN BODY
        ========================================================== -->

        <div class="body-text">


            <!-- =====================================================
                 ACADEMIC PERFORMANCE
            ====================================================== -->

            <p>

                A review of your academic performance for the
                {{ $semesterName }},
                S.Y. {{ $schoolYear }}
                shows that you have obtained failing grades in
                {{ rtrim(rtrim(number_format((float) $failurePercentage, 2), '0'), '.') }}%
                of your total academic load.

            </p>


            <!-- =====================================================
                 FAILED SUBJECTS
            ====================================================== -->

            <p class="failed-subjects-title">

                Failed Subjects:

            </p>


            @if($warningFailedSubjects->count() > 0)

                <ul class="failed-subjects">

                    @foreach($warningFailedSubjects as $grade)

                        @php

                            $subject = $grade->subject ?? null;

                            $courseCode =
                                $subject->subject_code
                                ?? $subject->course_code
                                ?? 'Course No';

                            $subjectTitle =
                                $subject->subject_title
                                ?? $subject->descriptive_title
                                ?? $subject->title
                                ?? 'Descriptive Title';

                        @endphp

                        <li>

                            {{ $courseCode }}
                            –
                            {{ $subjectTitle }}
                            (Grade: {{ number_format((float) $grade->grade, 0) }})

                        </li>

                    @endforeach

                </ul>

            @else

                <ul class="failed-subjects">

                    <li>

                        No failed subjects recorded for this semester.

                    </li>

                </ul>

            @endif


            <!-- =====================================================
                 RETENTION POLICY
            ====================================================== -->

            <p>

                Pursuant to the Department Retention Policy, you are hereby
                placed under <strong>ACADEMIC WARNING</strong> status.
                We are concerned about your progress. Please be advised to:

            </p>


            <!-- =====================================================
                 ADVISORY
            ====================================================== -->

            <ul class="advisory-list">

                <li>
                    See the Department Head for academic advising.
                </li>

                <li>
                    Seek guidance counseling to address any personal
                    or study habit issues.
                </li>

                <li>
                    Prioritize passing these subjects in the next term.
                </li>

            </ul>


            <!-- =====================================================
                 CONSEQUENCE
            ====================================================== -->

            <p>

                Failure to improve your standing may result in
                Probation or Disqualification from the program.

            </p>


        </div>


        <!-- =========================================================
             CLOSING
        ========================================================== -->

        <div class="closing">

            Sincerely,

        </div>


        <!-- =========================================================
             SIGNATURE
        ========================================================== -->

        <div class="signature">

            <div class="signature-name">

                RODOLFO A. LEAL JR., MIT

            </div>


            <div class="signature-position">

                Head, ITE Department

            </div>

        </div>


        <!-- =========================================================
             ACKNOWLEDGMENT
        ========================================================== -->

        <div class="acknowledgment">

            <div class="acknowledgment-title">

                ACKNOWLEDGMENT:

            </div>


            <p class="acknowledgment-text">

                I retain a copy of this notice and understand the
                implications of my academic status.

            </p>

        </div>


        <!-- =========================================================
             SIGNATURE LINES
        ========================================================== -->

        <div class="signature-lines">


            <!-- =====================================================
                 STUDENT SIGNATURE
            ====================================================== -->

            <div class="signature-line student-signature">

                <div class="line"></div>

                <div class="signature-label">

                    Student's Name and Signature

                </div>

            </div>



            <!-- =====================================================
                 PARENT/GUARDIAN SIGNATURE
            ====================================================== -->

            <div class="signature-line parent-signature">

                <div class="line"></div>

                <div class="signature-label">

                    Parent/Guardian's Name and Signature

                </div>

            </div>


        </div>


    </div>


</body>

</html>