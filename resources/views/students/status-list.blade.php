@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h4>

                {{ $status }} Students

            </h4>

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <thead class="table-dark">

                    <tr>

                        <th>Student Number</th>

                        <th>Name</th>

                        <th>Year Level</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($students as $student)

                    <tr>

                        <td>

                            {{ $student->student_no }}

                        </td>

                        <td>

                            {{ $student->last_name }},
                            {{ $student->first_name }}

                        </td>

                        <td>

                            {{ $student->current_year_level }}

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="3"
                            class="text-center">

                            No students found.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection