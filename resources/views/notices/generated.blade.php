<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Notice Generated</title>

    <style>

        body {
            margin: 0;
            padding: 0;
            background: #f4f6f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
        }

        .card {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .success {
            text-align: center;
            margin-bottom: 25px;
        }

        .success-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #198754;
            color: #ffffff;
            font-size: 36px;
            line-height: 60px;
            font-weight: bold;
        }

        .success h2 {
            margin: 0;
            color: #198754;
        }

        .success p {
            margin-top: 8px;
            color: #666;
        }

        .details {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 25px;
        }

        .detail-row {
            display: flex;
            border-bottom: 1px solid #ddd;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            width: 35%;
            padding: 13px 15px;
            background: #f8f9fa;
            font-weight: bold;
        }

        .detail-value {
            width: 65%;
            padding: 13px 15px;
        }

        .subjects {
            margin-top: 25px;
        }

        .subjects h3 {
            margin-bottom: 12px;
        }

        .subjects table {
            width: 100%;
            border-collapse: collapse;
        }

        .subjects th,
        .subjects td {
            border: 1px solid #ccc;
            padding: 9px;
            text-align: left;
        }

        .subjects th {
            background: #f1f1f1;
        }

        .note {
            margin-top: 25px;
            padding: 12px 15px;
            background: #d1e7dd;
            border: 1px solid #a3cfbb;
            border-radius: 6px;
            color: #0f5132;
            font-size: 14px;
        }

        @media (max-width: 600px) {

            .detail-row {
                display: block;
            }

            .detail-label,
            .detail-value {
                width: auto;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="success">

            <div class="success-icon">
                ✓
            </div>

            <h2>
                Academic Notice Generated Successfully
            </h2>

            <p>
                The notice has been generated and saved successfully.
            </p>

        </div>


        <div class="details">

            <div class="detail-row">

                <div class="detail-label">
                    Student No.
                </div>

                <div class="detail-value">
                    {{ $student->student_no ?? 'N/A' }}
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Student Name
                </div>

                <div class="detail-value">

                    {{ $student->last_name ?? '' }},
                    {{ $student->first_name ?? '' }}

                    @if(!empty($student->middle_name))

                        {{ strtoupper(substr(trim($student->middle_name), 0, 1)) }}.

                    @endif

                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Notice Type
                </div>

                <div class="detail-value">

                    {{ $notice->notice_type ?? 'Academic Notice' }}

                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Generated
                </div>

                <div class="detail-value">

                    @if(isset($notice->generated_at))

                        {{ \Carbon\Carbon::parse($notice->generated_at)->format('F d, Y h:i A') }}

                    @else

                        {{ now()->format('F d, Y h:i A') }}

                    @endif

                </div>

            </div>

        </div>


        @if(
            isset($subjects) &&
            is_array($subjects) &&
            count($subjects) > 0
        )

            <div class="subjects">

                <h3>
                    Priority Subjects
                </h3>

                <table>

                    <thead>

                        <tr>

                            <th style="width: 25%;">
                                Course Code
                            </th>

                            <th>
                                Subject Title
                            </th>

                            <th style="width: 15%;">
                                Units
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($subjects as $subject)

                            @if(!empty($subject['subject_code']))

                                <tr>

                                    <td>
                                        {{ $subject['subject_code'] }}
                                    </td>

                                    <td>
                                        {{ $subject['subject_title'] ?? '' }}
                                    </td>

                                    <td>
                                        {{ $subject['units'] ?? '' }}
                                    </td>

                                </tr>

                            @endif

                        @endforeach

                    </tbody>

                </table>


                @if(isset($totalUnits))

                    <p style="text-align: right; margin-top: 10px;">

                        <strong>
                            Total Units:
                            {{ $totalUnits }}
                        </strong>

                    </p>

                @endif

            </div>

        @endif


        <div class="note">

            <strong>Notice Generation Record:</strong>

            The system has recorded the date and time when this
            academic notice was generated. The record is associated
            with the student's specific academic semester and school
            year.

        </div>

    </div>

</div>

</body>

</html>