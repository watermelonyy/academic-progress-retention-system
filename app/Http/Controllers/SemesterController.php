<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SemesterController extends Controller
{
    /**
     * Display all school years.
     */
    public function index()
    {
        $schoolYears = Semester::query()
            ->select('school_year')
            ->groupBy('school_year')
            ->orderByRaw(
                "CAST(SPLIT_PART(school_year, '-', 1) AS INTEGER) DESC"
            )
            ->get()
            ->map(function ($item) {
                return [
                    'school_year' => $item->school_year,
                    'semesters' => Semester::where(
                        'school_year',
                        $item->school_year
                    )
                        ->orderBy('semester_id')
                        ->get()
                ];
            });

        return view('semesters.index', compact('schoolYears'));
    }

    /**
     * Add a school year and automatically create
     * its first and second semesters.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'school_year' => [
                    'required',
                    'string',
                    'regex:/^\d{4}-\d{4}$/',
                    function ($attribute, $value, $fail) {
                        [$start, $end] = array_map(
                            'intval',
                            explode('-', $value)
                        );

                        if ($end !== $start + 1) {
                            $fail(
                                'The school year must contain consecutive years, such as 2026-2027.'
                            );
                        }
                    },
                    Rule::unique('semesters', 'school_year'),
                ],
            ],
            [
                'school_year.required' =>
                    'Please enter a school year.',

                'school_year.string' =>
                    'The school year must contain numbers only in the format YYYY-YYYY.',

                'school_year.regex' =>
                    'Use the format YYYY-YYYY, such as 2026-2027.',

                'school_year.unique' =>
                    'This school year already exists.',
            ]
        );

        DB::transaction(function () use ($validated) {
            foreach (
                [
                    'First Semester',
                    'Second Semester'
                ] as $semesterName
            ) {
                Semester::create([
                    'school_year' => $validated['school_year'],
                    'semester_name' => $semesterName,
                ]);
            }
        });

        return redirect()
            ->route('semesters.index')
            ->with(
                'success',
                'School year ' . $validated['school_year'] .
                ' and its two semesters have been added successfully.'
            );
    }

    /**
     * Update an existing school year.
     */
    public function update(Request $request, string $schoolYear)
    {
        $exists = Semester::where(
            'school_year',
            $schoolYear
        )->exists();

        if (!$exists) {
            return redirect()
                ->route('semesters.index')
                ->with(
                    'error',
                    'The selected school year was not found.'
                );
        }

        $validated = $request->validate(
            [
                'school_year' => [
                    'required',
                    'string',
                    'regex:/^\d{4}-\d{4}$/',
                    function ($attribute, $value, $fail) {
                        [$start, $end] = array_map(
                            'intval',
                            explode('-', $value)
                        );

                        if ($end !== $start + 1) {
                            $fail(
                                'The school year must contain consecutive years, such as 2026-2027.'
                            );
                        }
                    },
                    function ($attribute, $value, $fail) use ($schoolYear) {
                        if (
                            $value !== $schoolYear &&
                            Semester::where(
                                'school_year',
                                $value
                            )->exists()
                        ) {
                            $fail(
                                'This school year already exists.'
                            );
                        }
                    },
                ],
            ],
            [
                'school_year.required' =>
                    'Please enter a school year.',

                'school_year.string' =>
                    'The school year must contain numbers only in the format YYYY-YYYY.',

                'school_year.regex' =>
                    'Use the format YYYY-YYYY, such as 2026-2027.',
            ]
        );

        DB::transaction(function () use ($schoolYear, $validated) {
            Semester::where('school_year', $schoolYear)
                ->update([
                    'school_year' => $validated['school_year'],
                ]);
        });

        return redirect()
            ->route('semesters.index')
            ->with(
                'success',
                'School year ' . $schoolYear .
                ' has been updated to ' .
                $validated['school_year'] .
                '.'
            );
    }
}