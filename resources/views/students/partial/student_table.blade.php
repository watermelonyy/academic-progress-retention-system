<tbody>

@forelse($students as $student)

    @php
        $status =
        optional($student->latestStatus)->academic_status
        ?? 'Regular';
    @endphp

    <tr>

        <td>{{ $student->student_no }}</td>

        <td>
            {{ $student->last_name }},
            {{ $student->first_name }}
        </td>

        <td>{{ $student->admission_year }}</td>

        <td>{{ $student->current_year_level }}</td>

        <td>

            @if($status == 'Regular')

                <span class="badge bg-success">
                    Regular
                </span>

            @elseif($status == 'Academic Warning')

                <span class="badge bg-warning text-dark">
                    Academic Warning
                </span>

            @elseif($status == 'Probation')

                <span class="badge bg-danger">
                    Probation
                </span>

            @elseif($status == 'Shifting Out')

                <span class="badge bg-dark">
                    Shifting Out
                </span>

            @else

                <span class="badge bg-info">
                    Residency Risk
                </span>

            @endif

        </td>

        <td>

            <a href="{{ route('students.show',$student->student_id) }}"
               class="btn btn-info btn-sm">

                <i class="fa fa-eye"></i>

            </a>

            <a href="{{ route('students.edit',$student->student_id) }}"
               class="btn btn-warning btn-sm">

                <i class="fa fa-edit"></i>

            </a>

        </td>

    </tr>

@empty

<tr>

    <td colspan="6" class="text-center">

        No students found.

    </td>

</tr>

@endforelse

</tbody>