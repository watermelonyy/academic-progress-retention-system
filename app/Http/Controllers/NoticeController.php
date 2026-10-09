<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AcademicGrade;
use App\Models\StudentStatusHistory;
use App\Models\AcademicNotice;
use App\Models\Prospectus;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class NoticeController extends Controller
{
    public function generate(int $id)
    {
        $student = Student::findOrFail($id);

        $latestStatus = StudentStatusHistory::with('semester')
            ->where('student_id', $student->student_id)
            ->latest('created_at')
            ->first();

        $status = optional($latestStatus)->academic_status ?? 'Regular';

        $failedSubjects = AcademicGrade::with([
            'subject',
            'semester'
        ])
            ->where('student_id', $student->student_id)
            ->where('grade', '<=', 74)
            ->orderByDesc('date_encoded')
            ->get();

        $logos = $this->getLogos();

        if ($status === 'Probation') {
            $prospectusSubjects = Prospectus::orderBy(
                'subject_code',
                'asc'
            )->get();

            return view(
                'notices.probation-form',
                compact(
                    'student',
                    'latestStatus',
                    'prospectusSubjects'
                )
            );
        }

        if ($status === 'Academic Warning') {
            $pdf = Pdf::loadView(
                'notices.academic-warning',
                compact(
                    'student',
                    'latestStatus',
                    'failedSubjects',
                    'logos'
                )
            );

            $pdf->setPaper('A4', 'portrait');

            $notice = $this->createNotice(
                $student,
                $latestStatus,
                'Academic Warning',
                $pdf
            );

            /*
             * Open the generated notice directly in the
             * browser PDF viewer.
             */
            $filePath = Storage::disk('public')->path(
                $notice->pdf_path
            );

            return response()->file(
                $filePath,
                [
                    'Content-Type' =>
                        'application/pdf',

                    'Content-Disposition' =>
                        'inline; filename="' .
                        basename($filePath) .
                        '"',
                ]
            );
        }

        if ($status === 'Shifting Out') {
            $disqualificationReasons =
                $this->getDisqualificationReasons(
                    $student,
                    $latestStatus
                );

            $pdf = Pdf::loadView(
                'notices.course-disqualification',
                compact(
                    'student',
                    'latestStatus',
                    'disqualificationReasons',
                    'logos'
                )
            );

            $pdf->setPaper('A4', 'portrait');

            $notice = $this->createNotice(
                $student,
                $latestStatus,
                'Notice of Course Disqualification',
                $pdf,
                [
                    'reasons' => $disqualificationReasons,
                ]
            );

            /*
             * Open the generated notice directly in the
             * browser PDF viewer.
             */
            $filePath = Storage::disk('public')->path(
                $notice->pdf_path
            );

            return response()->file(
                $filePath,
                [
                    'Content-Type' =>
                        'application/pdf',

                    'Content-Disposition' =>
                        'inline; filename="' .
                        basename($filePath) .
                        '"',
                ]
            );
        }

        return redirect()
            ->route('students.show', $student->student_id)
            ->with(
                'error',
                'This student does not currently require an academic notice.'
            );
    }

    public function previewProbation(
        Request $request,
        int $id
    ) {
        $student = Student::findOrFail($id);

        $latestStatus = $student
            ->latestStatus()
            ->with('semester')
            ->first();

        if (
            !$latestStatus ||
            $latestStatus->academic_status !== 'Probation'
        ) {
            return redirect()
                ->route('students.show', $student->student_id)
                ->with(
                    'error',
                    'This student is not currently under Probation status.'
                );
        }

        $request->validate([
            'subject_code' => [
                'required',
                'array',
                'max:5',
            ],

            'subject_code.*' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $codes = $request->input('subject_code', []);

        $prospectusSubjects = Prospectus::all();

        $subjects = [];

        $totalUnits = 0;

        for ($index = 0; $index < 5; $index++) {
            $code = trim(
                (string) ($codes[$index] ?? '')
            );

            if ($code === '') {
                continue;
            }

            $normalizedCode = strtoupper(
                preg_replace(
                    '/[\s\-]+/',
                    '',
                    $code
                )
            );

            $prospectusSubject =
                $prospectusSubjects->first(
                    function ($subject) use ($normalizedCode) {
                        $subjectCode = strtoupper(
                            preg_replace(
                                '/[\s\-]+/',
                                '',
                                trim(
                                    (string) $subject->subject_code
                                )
                            )
                        );

                        return $subjectCode === $normalizedCode;
                    }
                );

            if (!$prospectusSubject) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'subject_code.' . $index =>
                            'Course code "' .
                            $code .
                            '" was not found in the Prospectus.',
                    ]);
            }

            $units = (float) $prospectusSubject->units;

            $subjects[] = [
                'subject_code' =>
                    $prospectusSubject->subject_code,

                'subject_title' =>
                    $prospectusSubject->subject_title,

                'units' =>
                    $units,
            ];

            $totalUnits += $units;
        }

        if (count($subjects) === 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'subject_code' =>
                        'Please enter at least one course code.',
                ]);
        }

        if ($totalUnits > 15) {
            return back()
                ->withInput()
                ->withErrors([
                    'subject_code' =>
                        'The approved study load cannot exceed 15 academic units.',
                ]);
        }

        while (count($subjects) < 5) {
            $subjects[] = [
                'subject_code' => '',
                'subject_title' => '',
                'units' => '',
            ];
        }

        $logos = $this->getLogos();

        $pdf = Pdf::loadView(
            'notices.probation-pdf',
            [
                'student' => $student,
                'latestStatus' => $latestStatus,
                'subjects' => $subjects,
                'totalUnits' => $totalUnits,
                'logos' => $logos,
            ]
        )->setPaper('A4', 'portrait');

        $notice = $this->createNotice(
            $student,
            $latestStatus,
            'Probationary Agreement Contract',
            $pdf,
            [
                'subjects' => $subjects,
                'total_units' => $totalUnits,
            ]
        );

        /*
         * Open the generated notice directly in the
         * browser PDF viewer.
         */
        $filePath = Storage::disk('public')->path(
            $notice->pdf_path
        );

        return response()->file(
            $filePath,
            [
                'Content-Type' =>
                    'application/pdf',

                'Content-Disposition' =>
                    'inline; filename="' .
                    basename($filePath) .
                    '"',
            ]
        );
    }

    private function createNotice(
        Student $student,
        ?StudentStatusHistory $latestStatus,
        string $noticeType,
        DomPdf $pdf,
        array $snapshotData = []
    ): AcademicNotice {
        if (!$latestStatus) {
            abort(
                422,
                'The student does not have an academic status record.'
            );
        }

        $safeStudentNo = preg_replace(
            '/[^A-Za-z0-9_\-]/',
            '_',
            $student->student_no
        );

        $safeNoticeType = preg_replace(
            '/[^A-Za-z0-9_\-]/',
            '_',
            $noticeType
        );

        $filename =
            $safeNoticeType .
            '_' .
            $safeStudentNo .
            '_' .
            Str::uuid() .
            '.pdf';

        $directory =
            'notices/' .
            now()->format('Y');

        $pdfPath =
            $directory .
            '/' .
            $filename;

        $notice = AcademicNotice::create([
            'student_id' =>
                $student->student_id,

            'semester_id' =>
                $latestStatus->semester_id,

            'status_id' =>
                $latestStatus->status_id,

            'notice_type' =>
                $noticeType,

            'pdf_path' =>
                $pdfPath,

            'generated_at' =>
                now(),
        ]);

        Storage::disk('public')->put(
            $pdfPath,
            $pdf->output()
        );

        $snapshot = [
            'notice_id' =>
                $notice->notice_id,

            'student_id' =>
                $student->student_id,

            'student_no' =>
                $student->student_no,

            'notice_type' =>
                $noticeType,

            'semester_id' =>
                $latestStatus->semester_id,

            'status_id' =>
                $latestStatus->status_id,

            'generated_at' =>
                $notice->generated_at
                    ? $notice->generated_at->toDateTimeString()
                    : now()->toDateTimeString(),

            'subjects' =>
                $snapshotData['subjects'] ?? [],

            'total_units' =>
                $snapshotData['total_units'] ?? null,

            'reasons' =>
                $snapshotData['reasons'] ?? [],
        ];

        $recordDirectory =
            storage_path(
                'app/public/notices/records'
            );

        File::ensureDirectoryExists(
            $recordDirectory
        );

        File::put(
            $recordDirectory .
            '/' .
            $notice->notice_id .
            '.json',
            json_encode(
                $snapshot,
                JSON_PRETTY_PRINT
            )
        );

        return $notice->fresh();
    }

    public function view(
        int $id,
        int $notice
    ) {
        $student = Student::findOrFail($id);

        $academicNotice = AcademicNotice::where(
            'notice_id',
            $notice
        )
            ->where(
                'student_id',
                $student->student_id
            )
            ->firstOrFail();

        if (
            !$academicNotice->pdf_path ||
            !Storage::disk('public')->exists(
                $academicNotice->pdf_path
            )
        ) {
            return redirect()
                ->route(
                    'students.history',
                    $student->student_id
                )
                ->with(
                    'error',
                    'The saved notice PDF could not be found.'
                );
        }

        $filePath =
            Storage::disk('public')->path(
                $academicNotice->pdf_path
            );

        return response()->file(
            $filePath,
            [
                'Content-Type' =>
                    'application/pdf',

                'Content-Disposition' =>
                    'inline; filename="' .
                    basename($filePath) .
                    '"',
            ]
        );
    }

    public function download(
        int $id,
        int $notice
    ) {
        $student = Student::findOrFail($id);

        $academicNotice = AcademicNotice::where(
            'notice_id',
            $notice
        )
            ->where(
                'student_id',
                $student->student_id
            )
            ->firstOrFail();

        if (
            !$academicNotice->pdf_path ||
            !Storage::disk('public')->exists(
                $academicNotice->pdf_path
            )
        ) {
            return redirect()
                ->route(
                    'students.history',
                    $student->student_id
                )
                ->with(
                    'error',
                    'The saved notice PDF could not be found.'
                );
        }

        $this->recordNoticeAction(
            $academicNotice,
            'downloaded'
        );

        $filePath =
            Storage::disk('public')->path(
                $academicNotice->pdf_path
            );

        $downloadName = preg_replace(
            '/[^A-Za-z0-9_\-\.]/',
            '_',
            basename($academicNotice->pdf_path)
        );

        return response()->download(
            $filePath,
            $downloadName,
            [
                'Content-Type' =>
                    'application/pdf',
            ]
        );
    }

    public function print(
        int $id,
        int $notice
    ) {
        $student = Student::findOrFail($id);

        $academicNotice = AcademicNotice::where(
            'notice_id',
            $notice
        )
            ->where(
                'student_id',
                $student->student_id
            )
            ->firstOrFail();

        if (
            !$academicNotice->pdf_path ||
            !Storage::disk('public')->exists(
                $academicNotice->pdf_path
            )
        ) {
            return redirect()
                ->route(
                    'students.history',
                    $student->student_id
                )
                ->with(
                    'error',
                    'The saved notice PDF could not be found.'
                );
        }

        $this->recordNoticeAction(
            $academicNotice,
            'printed'
        );

        $filePath =
            Storage::disk('public')->path(
                $academicNotice->pdf_path
            );

        return response()->file(
            $filePath,
            [
                'Content-Type' =>
                    'application/pdf',

                'Content-Disposition' =>
                    'inline; filename="' .
                    basename($filePath) .
                    '"',
            ]
        );
    }

    private function recordNoticeAction(
        AcademicNotice $notice,
        string $action
    ): void {
        $file = storage_path(
            'app/notice_actions.json'
        );

        $data = [];

        if (file_exists($file)) {
            $contents = file_get_contents($file);

            if ($contents) {
                $decoded = json_decode(
                    $contents,
                    true
                );

                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }

        $noticeKey =
            (string) $notice->notice_id;

        if (
            !isset($data[$noticeKey]) ||
            !is_array($data[$noticeKey])
        ) {
            $data[$noticeKey] = [];
        }

        if (
            !isset($data[$noticeKey]['actions']) ||
            !is_array($data[$noticeKey]['actions'])
        ) {
            $data[$noticeKey]['actions'] = [];
        }

        $performedAt = now();

        $data[$noticeKey]['actions'][] = [
            'action' =>
                $action,

            'performed_at' =>
                $performedAt->toDateTimeString(),
        ];

        if ($action === 'printed') {
            $data[$noticeKey]['printed'] = true;

            $data[$noticeKey]['printed_at'] =
                $performedAt->toDateTimeString();
        }

        if ($action === 'downloaded') {
            $data[$noticeKey]['downloaded'] = true;

            $data[$noticeKey]['downloaded_at'] =
                $performedAt->toDateTimeString();
        }

        File::ensureDirectoryExists(
            dirname($file)
        );

        File::put(
            $file,
            json_encode(
                $data,
                JSON_PRETTY_PRINT
            )
        );
    }

    private function getLogos(): array
    {
        $paths = [
            'cdk' =>
                public_path(
                    'images/notices/cdk-logo.png'
                ),

            'ite' =>
                public_path(
                    'images/notices/ite-logo.png'
                ),

            'facebook' =>
                public_path(
                    'images/notices/facebook.jpg'
                ),

            'email' =>
                public_path(
                    'images/notices/email.jpg'
                ),
        ];

        $logos = [
            'cdk' => '',
            'ite' => '',
            'facebook' => '',
            'email' => '',
        ];

        foreach ($paths as $key => $path) {
            if (file_exists($path)) {
                $extension = strtolower(
                    pathinfo(
                        $path,
                        PATHINFO_EXTENSION
                    )
                );

                $mime = match ($extension) {
                    'jpg',
                    'jpeg' =>
                        'image/jpeg',

                    'gif' =>
                        'image/gif',

                    default =>
                        'image/png',
                };

                $logos[$key] =
                    'data:' .
                    $mime .
                    ';base64,' .
                    base64_encode(
                        file_get_contents($path)
                    );
            }
        }

        return $logos;
    }

    private function getDisqualificationReasons(
        Student $student,
        ?StudentStatusHistory $latestStatus
    ): array {
        $reasons = [];

        $semesterId =
            optional($latestStatus)->semester_id;

        $thirdFailure =
            AcademicGrade::where(
                'student_id',
                $student->student_id
            )
            ->where(
                'attempt_no',
                '>=',
                3
            )
            ->where(
                'grade',
                '<=',
                74
            )
            ->exists();

        if ($thirdFailure) {
            $reasons[] =
                'Three-Strike Rule';
        }

        $failedAllSubjects = false;

        if ($semesterId) {
            $evaluatedGrades =
                AcademicGrade::where(
                    'student_id',
                    $student->student_id
                )
                ->where(
                    'semester_id',
                    $semesterId
                )
                ->get();

            if ($evaluatedGrades->count() > 0) {
                $failedCount =
                    $evaluatedGrades
                        ->filter(function ($grade) {
                            return $grade->grade !== null &&
                                (float) $grade->grade <= 74;
                        })
                        ->count();

                if (
                    $failedCount ===
                    $evaluatedGrades->count()
                ) {
                    $failedAllSubjects = true;
                }
            }
        }

        if ($failedAllSubjects) {
            $reasons[] =
                'Permanent Debarment';
        }

        $residencyExceeded = false;

        if (
            !empty($student->admission_year) &&
            $semesterId
        ) {
            $semester =
                $latestStatus->semester;

            if ($semester) {
                $schoolYear =
                    trim(
                        (string) (
                            $semester->school_year ?? ''
                        )
                    );

                $endYear = null;

                if (
                    preg_match(
                        '/(\d{4})\s*[-\/]\s*(\d{2,4})$/',
                        $schoolYear,
                        $matches
                    )
                ) {
                    $firstYear =
                        (int) $matches[1];

                    $secondPart =
                        $matches[2];

                    if (
                        strlen($secondPart) === 2
                    ) {
                        $century =
                            (int) floor(
                                $firstYear / 100
                            ) * 100;

                        $endYear =
                            $century +
                            (int) $secondPart;
                    } else {
                        $endYear =
                            (int) $secondPart;
                    }
                } elseif (
                    preg_match(
                        '/(\d{4})$/',
                        $schoolYear,
                        $matches
                    )
                ) {
                    $endYear =
                        (int) $matches[1];
                }

                if ($endYear !== null) {
                    $yearsSpent =
                        $endYear -
                        (int) $student->admission_year;

                    if ($yearsSpent > 6) {
                        $residencyExceeded = true;
                    }
                }
            }
        }

        if ($residencyExceeded) {
            $reasons[] =
                'Maximum Residency';
        }

        if (empty($reasons)) {
            $reasons[] =
                'Retention Policy Violation';
        }

        return $reasons;
    }
}