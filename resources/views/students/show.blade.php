@extends('layouts.app') 
 
@section('content') 
 
@php 
 
/* ========================================================= 
| CURRENT / MOST RECENT EVALUATED SEMESTER 
========================================================= */ 
 
$currentStatus = optional($latestStatus)->academic_status ?? 'Regular';

/*
|--------------------------------------------------------------------------
| Normalize old status value
|--------------------------------------------------------------------------
*/

if ($currentStatus === 'For Shifting Out') {
    $currentStatus = 'Shifting Out';
}

/*
|--------------------------------------------------------------------------
| Normalize old Residency Risk records
|--------------------------------------------------------------------------
| Residency Risk is now a warning only, not an academic status.
*/

if ($currentStatus === 'Residency Risk') {
    $currentStatus = 'Regular';
}

$currentSemesterId = optional($latestStatus)->semester_id;

$currentSemester = $currentSemesterId
    ? $semesters->firstWhere('semester_id', $currentSemesterId)
    : null;
 
/* ========================================================= 
| CORRECT UNRESOLVED BACKLOGS 
========================================================= */ 
 
$correctedActiveBacklogs = collect($activeBacklogs ?? []); 
 
if ($currentSemester) { 
 
    $currentSemesterLabel = 
        $currentSemester->school_year 
        . ' - ' 
        . $currentSemester->semester_name; 
 
    $correctedActiveBacklogs = 
        $correctedActiveBacklogs->filter(function ($backlog) use ( 
            $currentSemesterLabel 
        ) { 
 
            return trim( 
                strtolower( 
                    $backlog['failed_semester'] ?? '' 
                ) 
            ) !== trim( 
                strtolower( 
                    $currentSemesterLabel 
                ) 
            ); 
 
        })->values(); 
} 
 
 
/* ========================================================= 
| BACKLOG COUNTS 
========================================================= */ 
 
$unresolvedBacklogCount = 
    $correctedActiveBacklogs->count(); 
 
$resolvedBacklogCount = 
    collect($resolvedBacklogs ?? [])->count(); 
 


/* =========================================================
| RECENTLY EVALUATED FAILED SUBJECTS
| Show only failed subjects belonging to the latest evaluated
| semester. Previous failed subjects are displayed under Backlogs.
========================================================= */
$recentFailedSubjects = collect($failedSubjects ?? [])->filter(function ($grade) use ($currentSemesterId) {
    return $currentSemesterId !== null
        && (string) optional($grade)->semester_id === (string) $currentSemesterId;
})->values();
@endphp 
 
 
<style> 
 
/* ========================================================= 
   GENERAL PAGE 
========================================================= */ 
 
.student-page { 
    max-width: 1500px; 
    margin: 0 auto; 
    padding-bottom: 40px; 
} 
 
.student-page .card { 
    border-radius: 14px; 
} 
 
 
/* ========================================================= 
   STUDENT HEADER 
========================================================= */ 
 
.student-header { 
    background: #ffffff; 
    border: 1px solid #e9ecef; 
    border-radius: 16px; 
    box-shadow: 0 3px 16px rgba(0, 0, 0, 0.05); 
    padding: 24px 28px; 
    margin-bottom: 20px; 
} 
 
.student-header-top { 
    display: flex; 
    justify-content: space-between; 
    align-items: flex-start; 
    gap: 25px; 
} 
 
.student-profile { 
    display: flex; 
    align-items: center; 
    gap: 16px; 
} 
 
.student-profile-icon { 
    width: 58px; 
    height: 58px; 
    min-width: 58px; 
    border-radius: 14px; 
    background: #edf4ff; 
    color: #0e8203; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 25px; 
} 
 
.student-name { 
    margin: 0; 
    font-size: 25px; 
    font-weight: 700; 
    color: #212529; 
    line-height: 1.25; 
}

.student-residency-alert {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    width: 31px;
    height: 31px;
    min-width: 31px;

    margin-left: 8px;
    border-radius: 50%;

    background: #fff8e1;
    color: #d39e00;
    border: 1px solid #f0d98c;

    font-size: 13px;
    vertical-align: middle;
    cursor: help;
}

.student-residency-alert:hover {
    background: #d39e00;
    color: #ffffff;
}
 
.student-number { 
    color: #6c757d; 
    font-size: 14px; 
    margin-top: 5px; 
} 
 
.back-link { 
    display: inline-flex; 
    align-items: center; 
    gap: 6px; 
    text-decoration: none; 
    color: #6c757d; 
    font-size: 14px; 
    font-weight: 500; 
    margin-bottom: 10px; 
    transition: 0.2s ease; 
} 
 
.back-link:hover { 
    color: #0e8203; 
} 
 
 
/* ========================================================= 
   ACTION BUTTONS 
========================================================= */ 
 
.student-actions { 
    display: flex; 
    flex-wrap: wrap; 
    justify-content: flex-end; 
    gap: 8px; 
} 
 
.student-actions .btn { 
    border-radius: 8px; 
    font-weight: 600; 
    padding: 9px 14px; 
    white-space: nowrap; 
} 
 
 
/* ========================================================= 
   ALERTS 
========================================================= */ 
 
.page-alert { 
    border: 0; 
    border-radius: 10px; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.04); 
} 
 
 
/* ========================================================= 
   SECTION CARD 
========================================================= */ 
 
.section-card { 
    background: #ffffff; 
    border: 1px solid #e9ecef; 
    border-radius: 14px; 
    box-shadow: 0 3px 14px rgba(0, 0, 0, 0.045); 
    overflow: hidden; 
    margin-bottom: 20px; 
} 
 
.section-header { 
    padding: 17px 22px; 
    border-bottom: 1px solid #edf0f2; 
    background: #ffffff; 
} 
 
.section-header-content { 
    display: flex; 
    align-items: center; 
    gap: 11px; 
} 
 
.section-header-icon { 
    width: 38px; 
    height: 38px; 
    border-radius: 10px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 16px; 
} 
 
.section-header-icon.blue { 
    background: #edf4ff; 
    color: #0e8203; 
} 
 
.section-header-icon.orange { 
    background: #fff4df; 
    color: #0e8203; 
} 
 
.section-header-icon.dark { 
    background: #eef0f2; 
    color: #343a40; 
} 
 
.section-title { 
    margin: 0; 
    font-size: 17px; 
    font-weight: 700; 
    color: #212529; 
} 
 
.section-subtitle { 
    margin: 2px 0 0; 
    font-size: 13px; 
    color: #6c757d; 
} 
 
 
/* ========================================================= 
   CURRENT STATUS 
========================================================= */ 
 
.status-grid { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    gap: 14px; 
} 
 
.status-info { 
    min-height: 108px; 
    padding: 17px; 
    background: #fafbfc; 
    border: 1px solid #e8ebee; 
    border-radius: 11px; 
    transition: 0.2s ease; 
} 
 
.status-info:hover { 
    border-color: #d8dee5; 
    background: #ffffff; 
} 
 
.status-label { 
    display: block; 
    color: #7a828a; 
    font-size: 11px; 
    font-weight: 700; 
    text-transform: uppercase; 
    letter-spacing: .5px; 
    margin-bottom: 8px; 
} 
 
.status-value { 
    font-size: 21px; 
    font-weight: 700; 
    color: #212529; 
} 
 
.status-badge { 
    display: inline-block; 
    font-size: 13px; 
    padding: 7px 11px; 
    border-radius: 7px; 
} 
 
 
/* ========================================================= 
   FAILED SUBJECTS 
========================================================= */ 
 
.failed-section-body { 
    padding: 0; 
} 
 
.failed-table { 
    margin-bottom: 0; 
} 
 
.failed-table thead th { 
    background: #f8f9fa; 
    color: #59636e; 
    border-bottom: 1px solid #dee2e6; 
    border-top: 0; 
    font-size: 12px; 
    font-weight: 700; 
    text-transform: uppercase; 
    letter-spacing: .3px; 
    padding: 13px 16px; 
    white-space: nowrap; 
} 
 
.failed-table tbody td { 
    padding: 14px 16px; 
    border-color: #edf0f2; 
    vertical-align: middle; 
} 
 
.failed-table tbody tr { 
    transition: background 0.15s ease; 
} 
 
.failed-table tbody tr:hover { 
    background: #fafcff; 
} 
 
.course-code { 
    font-weight: 700; 
    color: #343a40; 
} 
 
.subject-title { 
    color: #495057; 
} 
 
.grade-failed { 
    background: #fff0f1; 
    color: #dc3545; 
    border: 1px solid #ffd4d8; 
    font-weight: 700; 
    padding: 6px 10px; 
    border-radius: 7px; 
} 
 
 
/* ========================================================= 
   BACKLOG SUMMARY 
========================================================= */ 
 
.backlog-intro { 
    color: #6c757d; 
    font-size: 14px; 
    line-height: 1.6; 
    margin-bottom: 18px; 
} 
 
.backlog-summary { 
    display: grid; 
    grid-template-columns: repeat(2, 1fr); 
    gap: 15px; 
} 
 
.backlog-card { 
    width: 100%; 
    border-radius: 13px; 
    padding: 20px; 
    display: flex; 
    align-items: center; 
    gap: 15px; 
    text-align: left; 
    background: #ffffff; 
    cursor: pointer; 
    transition: all 0.2s ease; 
} 
 
.backlog-card:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 6px 18px rgba(0,0,0,0.08); 
} 
 
.backlog-card:focus { 
    outline: none; 
} 
 
.unresolved-card { 
    border: 1px solid #f1b8be; 
} 
 
.unresolved-card:hover { 
    border-color: #dc3545; 
} 
 
.resolved-card { 
    border: 1px solid #b8dfca; 
} 
 
.resolved-card:hover { 
    border-color: #198754; 
} 
 
.backlog-icon { 
    width: 48px; 
    height: 48px; 
    min-width: 48px; 
    border-radius: 12px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 20px; 
} 
 
.unresolved-card .backlog-icon { 
    background: #fff0f1; 
    color: #dc3545; 
} 
 
.resolved-card .backlog-icon { 
    background: #eefaf3; 
    color: #198754; 
} 
 
.backlog-count { 
    font-size: 25px; 
    font-weight: 700; 
    line-height: 1; 
    margin-bottom: 4px; 
} 
 
.unresolved-card .backlog-count { 
    color: #dc3545; 
} 
 
.resolved-card .backlog-count { 
    color: #198754; 
} 
 
.backlog-title { 
    font-size: 15px; 
    font-weight: 700; 
    color: #343a40; 
    margin-bottom: 2px; 
} 
 
.backlog-description { 
    font-size: 12px; 
    color: #6c757d; 
} 
 
.backlog-chevron { 
    margin-left: auto; 
    color: #adb5bd; 
    transition: transform 0.2s ease; 
} 
 
 
/* ========================================================= 
   COLLAPSED BACKLOG DETAILS 
========================================================= */ 
 
.backlog-detail { 
    border-radius: 12px; 
    overflow: hidden; 
    margin-top: 18px; 
    border: 1px solid #e1e5e9; 
} 
 
.backlog-detail-header { 
    padding: 14px 18px; 
    display: flex; 
    align-items: center; 
    gap: 9px; 
    font-weight: 700; 
} 
 
.unresolved-detail .backlog-detail-header { 
    background: #fff5f5; 
    color: #b02a37; 
    border-bottom: 1px solid #f1c2c7; 
} 
 
.resolved-detail .backlog-detail-header { 
    background: #f1faf5; 
    color: #146c43; 
    border-bottom: 1px solid #badbcc; 
} 
 
.backlog-detail-body { 
    padding: 18px; 
    background: #ffffff; 
} 
 
.backlog-info { 
    border-radius: 8px; 
    padding: 11px 13px; 
    font-size: 13px; 
    margin-bottom: 16px; 
} 
 
.unresolved-detail .backlog-info { 
    background: #fff9f9; 
    border: 1px solid #f5d5d8; 
    color: #6c4a4d; 
} 
 
.resolved-detail .backlog-info { 
    background: #f7fcf9; 
    border: 1px solid #d2e9da; 
    color: #41634e; 
} 
 
.backlog-table { 
    margin-bottom: 0; 
} 
 
.backlog-table thead th { 
    font-size: 11px; 
    text-transform: uppercase; 
    letter-spacing: .3px; 
    white-space: nowrap; 
    padding: 11px 13px; 
} 
 
.backlog-table tbody td { 
    padding: 12px 13px; 
    vertical-align: middle; 
} 
 
 
/* ========================================================= 
   MODAL 
========================================================= */ 
 
.evaluation-modal {
    border: 0;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 15px 45px rgba(0,0,0,0.18);

    /* Keep the modal within the screen */
    height: calc(100vh - 2rem);
    max-height: calc(100vh - 2rem);
    display: flex;
    flex-direction: column;
}

.evaluation-modal form {
    display: flex;
    flex-direction: column;
    min-height: 0;
    height: 100%;
}

.evaluation-modal .modal-body {
    background: #ffffff;

    /* The whole modal body is the scroll area */
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    max-height: none;

    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}

.evaluation-modal .evaluation-modal-header,
.evaluation-modal .modal-footer {
    flex: 0 0 auto;
}

.evaluation-modal .modal-body::-webkit-scrollbar {
    width: 8px;
}

.evaluation-modal .modal-body::-webkit-scrollbar-track {
    background: #f1f3f5;
}

.evaluation-modal .modal-body::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 10px;
}

.evaluation-modal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #868e96;
}
 
 
.evaluation-modal-header { 
    background: #0e8203; 
    color: #ffffff; 
    padding: 18px 22px; 
} 
 
.modal-title-main { 
    font-size: 18px; 
    font-weight: 700; 
    margin-bottom: 3px; 
} 
 
.modal-title-sub { 
    font-size: 12px; 
    opacity: .8; 
} 
 
.modal-body { 
    background: #ffffff; 
} 
 
.student-evaluation-info { 
    display: grid; 
    grid-template-columns: 1fr 220px; 
    gap: 15px; 
    padding: 15px 17px; 
    background: #f8f9fa; 
    border: 1px solid #e8ebee; 
    border-radius: 10px; 
} 
 
.info-label { 
    display: block; 
    color: #7a828a; 
    font-size: 11px; 
    font-weight: 700; 
    text-transform: uppercase; 
    letter-spacing: .4px; 
    margin-bottom: 4px; 
} 
 
.section-title-modal { 
    display: flex; 
    align-items: center; 
    gap: 8px; 
    font-size: 15px; 
    font-weight: 700; 
    color: #343a40; 
    margin-bottom: 14px; 
    padding-bottom: 9px; 
    border-bottom: 1px solid #e9ecef; 
} 
 
.form-label { 
    color: #495057; 
    font-size: 13px; 
} 
 
.form-select, 
.form-control { 
    border-radius: 8px; 
    border-color: #dfe3e7; 
} 
 
.form-select:focus, 
.form-control:focus { 
    border-color: #32eb41; 
    box-shadow: 0 0 0 3px rgba(13,110,253,.1); 
} 
 
.semester-options { 
    display: flex; 
    align-items: center; 
    gap: 25px; 
    min-height: 38px; 
} 
 
.semester-option { 
    border: 1px solid #dee2e6; 
    border-radius: 8px; 
    padding: 9px 13px; 
    transition: 0.2s ease; 
} 
 
.semester-option:hover { 
    background: #f7faff; 
    border-color: #b6d4fe; 
} 
 
.subject-header { 
    display: flex; 
    gap: 12px; 
    padding: 10px 13px; 
    background: #f8f9fa; 
    border: 1px solid #dee2e6; 
    border-bottom: 0; 
    border-radius: 8px 8px 0 0; 
    color: #59636e; 
    font-size: 12px; 
    font-weight: 700; 
    text-transform: uppercase; 
} 
 
.subject-row { 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    padding: 11px 13px; 
    border: 1px solid #dee2e6; 
    border-bottom: 0; 
    background: #ffffff; 
} 
 
.subject-row:last-child { 
    border-bottom: 1px solid #dee2e6; 
    border-radius: 0 0 8px 8px; 
} 
 
.subject-number { 
    width: 28px; 
    height: 28px; 
    min-width: 28px; 
    border-radius: 8px; 
    background: #edf4ff; 
    color: #0e8203; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-weight: 700; 
    font-size: 12px; 
} 
 
.grade-field { 
    width: 160px; 
} 
 
/* =========================================================
   ADD SUBJECT / GRADE INPUT
========================================================= */

#addSubject {
    border-radius: 8px;
    font-weight: 600;
    padding: 8px 14px;
    white-space: nowrap;
}

.subject-header {
    justify-content: space-between;
    align-items: center;
}

.subject-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.subject-header-grade {
    width: 160px;
}

.subject-header-action {
    width: 38px;
    text-align: center;
}

.subject-add-bar {
    display: flex;
    justify-content: flex-end;
    padding: 10px 0;
}

.subject-list {
    /* The modal body is the only scrollbar. */
    max-height: none;
    overflow: visible;
}

.remove-subject {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Remove number input arrows/spinners */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}


.modal-footer { 
    border-top: 1px solid #e9ecef; 
    padding: 14px 20px; 
    background: #fafbfc; 
} 
 
.modal-footer .btn { 
    border-radius: 8px; 
    font-weight: 600; 
} 
 
 
/* ========================================================= 
   EMPTY STATE 
========================================================= */ 
 
.empty-table-state { 
    padding: 28px 15px !important; 
    color: #6c757d; 
} 
 
.empty-table-icon { 
    font-size: 22px; 
    margin-bottom: 7px; 
} 
 
 
/* ========================================================= 
   RESPONSIVE 
========================================================= */ 
 
@media (max-width: 768px) {
    .evaluation-modal {
        height: calc(100vh - 1rem);
        max-height: calc(100vh - 1rem);
        border-radius: 12px;
    }

    .evaluation-modal .modal-body {
        padding: 16px !important;
    }
}

@media (max-width: 576px) {
    .evaluation-modal {
        height: 100vh;
        max-height: 100vh;
        border-radius: 0;
    }

    .evaluation-modal .modal-body {
        padding: 14px !important;
    }

    .evaluation-modal-header {
        padding: 16px !important;
    }
}

@media (max-width: 1100px) { 
 
    .student-header-top { 
        flex-direction: column; 
    } 
 
    .student-actions { 
        width: 100%; 
        justify-content: flex-start; 
    } 
 
    .status-grid { 
        grid-template-columns: repeat(2, 1fr); 
    } 
 
} 
 
@media (max-width: 768px) { 
 
    .student-page { 
        padding-left: 5px; 
        padding-right: 5px; 
    } 
 
    .student-header { 
        padding: 20px; 
    } 
 
    .student-profile { 
        align-items: flex-start; 
    } 
 
    .student-profile-icon { 
        width: 48px; 
        height: 48px; 
        min-width: 48px; 
        font-size: 20px; 
    } 
 
    .student-name { 
        font-size: 20px; 
    } 
 
    .student-actions { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
    } 
 
    .student-actions .btn { 
        width: 100%; 
    } 
 
    .status-grid { 
        grid-template-columns: 1fr 1fr; 
    } 
 
    .backlog-summary { 
        grid-template-columns: 1fr; 
    } 
 
    .student-evaluation-info { 
        grid-template-columns: 1fr; 
    } 
 
    .subject-row { 
        flex-wrap: wrap; 
    } 
 
    .subject-number { 
        display: none; 
    } 
 
    .grade-field { 
        width: 100%; 
    } 
 
    .semester-options { 
        flex-direction: column; 
        align-items: flex-start; 
        gap: 8px; 
    } 
 
} 
 
@media (max-width: 500px) { 
 
    .student-actions { 
        grid-template-columns: 1fr; 
    } 
 
    .status-grid { 
        grid-template-columns: 1fr; 
    } 
 
    .student-name { 
        font-size: 18px; 
    } 
 
    .backlog-card { 
        padding: 16px; 
    } 
 
} 
 
</style> 
 
 
<div class="container-fluid mt-4"> 
 
<div class="student-page"> 
 
 
<!-- ========================================================= 
     STUDENT HEADER 
========================================================= --> 
 
<div class="student-header"> 
 
    <div class="student-header-top"> 
 
        <div> 
 
            <a href="{{ route('students.index') }}" 
               class="back-link"> 
 
                <i class="fa fa-arrow-left"></i> 
 
                Back
 
            </a> 
 
 
            <div class="student-profile"> 
 
                <div class="student-profile-icon"> 
 
                    <i class="fa fa-user-graduate"></i> 
 
                </div> 
 
 
                <div> 
 
                    <h1 class="student-name"> 
 
                        {{ $student->last_name }}, 
                        {{ $student->first_name }} 
 
                        @if($student->middle_name) 
                            {{ $student->middle_name }} 
                        @endif 
 
                        @if($residencyRisk)

                            <span
                                class="student-residency-alert"
                                title="Residency Risk: Projected residency is {{ $projectedResidencyYears }} school years"
                            >

                                <i class="fa fa-clock"></i>

                            </span>

                        @endif

                    </h1>


                    <div class="student-number"> 
 
                        Student No. 
 
                        <strong> 
                            {{ $student->student_no }} 
                        </strong> 
 
                    </div> 
 
                </div> 
 
            </div> 
 
        </div> 
 
 
        <!-- ACTIONS --> 
 
        <div class="student-actions"> 
 
            <button 
                type="button" 
                class="btn btn-primary" 
                data-bs-toggle="modal" 
                data-bs-target="#addEvaluationModal"> 
 
                <i class="fa fa-plus me-1"></i> 
 
                New Evaluation 
 
            </button> 
 
 
            <a 
                href="{{ route( 
                    'students.history', 
                    $student->student_id 
                ) }}" 
                class="btn btn-outline-secondary"> 
 
                <i class="fa fa-history me-1"></i> 
 
                Academic History 
 
            </a> 
 
 
            <a 
                href="{{ route( 
                    'students.edit', 
                    $student->student_id 
                ) }}" 
                class="btn btn-outline-warning"> 
 
                <i class="fa fa-edit me-1"></i> 
 
                Edit Student 
 
            </a> 
 
 
            @if(in_array( 
                $currentStatus, 
                [ 
                    'Academic Warning', 
                    'Probation', 
                    'Shifting Out' 
                ] 
            )) 
 
                <a 
                    href="{{ route( 
                        'notices.generate', 
                        ['id' => $student->student_id] 
                    ) }}" 
                    class="btn btn-success"> 
 
                    <i class="fa fa-file-alt me-1"></i> 
 
                    Generate Notice 
 
                </a> 
 
            @endif 
 
        </div> 
 
    </div> 
 
</div> 
 
 
<!-- ========================================================= 
     ALERT MESSAGES 
========================================================= --> 
 
@if(session('success')) 
 
    <div 
        class="alert alert-success alert-dismissible fade show page-alert" 
        role="alert"> 
 
        <i class="fa fa-check-circle me-2"></i> 
 
        {{ session('success') }} 
 
        <button 
            type="button" 
            class="btn-close" 
            data-bs-dismiss="alert"> 
        </button> 
 
    </div> 
 
@endif 
 
 
@if(session('error')) 
 
    <div 
        class="alert alert-danger alert-dismissible fade show page-alert" 
        role="alert"> 
 
        <i class="fa fa-exclamation-circle me-2"></i> 
 
        {{ session('error') }} 
 
        <button 
            type="button" 
            class="btn-close" 
            data-bs-dismiss="alert"> 
        </button> 
 
    </div> 
 
@endif 
 
 
@if($errors->any()) 
 
    <div 
        class="alert alert-danger alert-dismissible fade show page-alert" 
        role="alert"> 
 
        <strong> 
 
            <i class="fa fa-exclamation-triangle me-2"></i> 
 
            Please correct the following: 
 
        </strong> 
 
        <ul class="mb-0 mt-2"> 
 
            @foreach($errors->all() as $error) 
 
                <li> 
                    {{ $error }} 
                </li> 
 
            @endforeach 
 
        </ul> 
 
        <button 
            type="button" 
            class="btn-close" 
            data-bs-dismiss="alert"> 
        </button> 
 
    </div> 
 
@endif 
 
 
<!-- ========================================================= 
     CURRENT ACADEMIC STATUS 
========================================================= --> 
 
<div class="section-card"> 
 
    <div class="section-header"> 
 
        <div class="section-header-content"> 
 
            <div class="section-header-icon blue"> 
 
                <i class="fa fa-chart-line"></i> 
 
            </div> 
 
            <div> 
 
                <h5 class="section-title"> 
                    Current Academic Status 
                </h5> 
 
 
            </div> 
 
        </div> 
 
    </div> 
 
 
    <div class="card-body p-3 p-md-4"> 
 
        <div class="status-grid"> 
 
 
            <!-- STATUS --> 
 
            <div class="status-info"> 
 
                <span class="status-label"> 
                    Academic Status 
                </span> 
 
                @if($currentStatus === 'Regular') 
 
                    <span class="badge bg-success status-badge"> 
                        Regular 
                    </span> 
 
                @elseif($currentStatus === 'Academic Warning') 
 
                    <span class="badge bg-warning text-dark status-badge"> 
                        Academic Warning 
                    </span> 
 
                @elseif($currentStatus === 'Probation') 
 
                    <span class="badge bg-danger status-badge"> 
                        Probation 
                    </span> 
 
                @elseif($currentStatus === 'Shifting Out') 
 
                    <span class="badge bg-dark status-badge"> 
                        Shifting Out 
                    </span>
                @else 
 
                    <span class="badge bg-secondary status-badge"> 
                        {{ $currentStatus }} 
                    </span> 
 
                @endif 
 
            </div> 
 
 
            <!-- FAILURE PERCENTAGE --> 
 
            <div class="status-info"> 
 
                <span class="status-label"> 
                    Failure Percentage 
                </span> 
 
                <div class="status-value"> 
 
                    {{ number_format( 
                        optional($latestStatus)->failure_percentage ?? 0, 
                        2 
                    ) }}% 
 
                </div> 
 
            </div> 
 
 
            <!-- YEAR LEVEL --> 
 
            <div class="status-info"> 
 
                <span class="status-label"> 
                    Year Level 
                </span> 
 
                <div class="status-value"> 
 
                    {{ $student->current_year_level }} 
 
                </div> 
 
            </div> 
 
 
            <!-- ADMISSION YEAR --> 
 
            <div class="status-info"> 
 
                <span class="status-label"> 
                    Admission Year 
                </span> 
 
                <div class="status-value"> 
 
                    {{ $student->admission_year }} 
 
                </div> 
 
            </div> 
 
 
        </div> 
 
    </div> 
 
</div> 
 
 
<!-- ========================================================= 
     RECENTLY EVALUATED FAILED SUBJECTS 
========================================================= --> 
 
<div class="section-card"> 
 
    <div class="section-header"> 
 
        <div class="section-header-content"> 
 
            <div class="section-header-icon orange"> 
 
                <i class="fa fa-exclamation-circle"></i> 
 
            </div> 
 
            <div> 
 
                <h5 class="section-title"> 
                    Recently Evaluated Failed Subjects 
                </h5> 
 
 
            </div> 
 
        </div> 
 
    </div> 
 
 
    <div class="failed-section-body"> 
 
        <div class="table-responsive"> 
 
            <table class="table failed-table align-middle"> 
 
                <thead> 
 
                    <tr>
                        <th>
                            @if($currentSemester)
                                <div style="font-size: 0.9rem; font-weight: 700; line-height: 1.3; margin-bottom: 4px;">
                                    {{ $currentSemester->school_year }}
                                    &middot;
                                    {{ $currentSemester->semester_name }}
                                </div>
                            @endif
                            <div class="course-code" style="font-size: 0.8rem;">Course Code</div>
                        </th>

                        <th>
                            Subject Title
                        </th>

                        <th class="text-center">
                            Grade
                        </th>

                        <th class="text-center">
                            Attempt
                        </th> 
 
                    </tr> 
 
                </thead> 
 
 
                <tbody> 
 
                    @forelse($recentFailedSubjects as $grade) 
 
                        <tr> 
 
                            <td class="course-code"> 
 
                                {{ optional( 
                                    $grade->subject 
                                )->subject_code ?? 'N/A' }} 
 
                            </td> 
 
 
                            <td class="subject-title"> 
 
                                {{ optional( 
                                    $grade->subject 
                                )->subject_title ?? 'N/A' }} 
 
                            </td> 
 
 
                            <td class="text-center"> 
 
                                <span class="grade-failed"> 
 
                                    {{ $grade->grade }} 
 
                                </span> 
 
                            </td> 
 
 
                            <td class="text-center"> 
 
                                {{ $grade->attempt_no }} 
 
                            </td>
                        </tr> 
 
                    @empty 
 
                        <tr> 
 
                            <td 
                                colspan="4" 
                                class="text-center empty-table-state"> 
 
                                <div class="empty-table-icon"> 
 
                                    <i class="fa fa-check-circle text-success"></i> 
 
                                </div> 
 
                                No failed subjects found in the most recent 
                                evaluated semester. 
 
                            </td> 
 
                        </tr> 
 
                    @endforelse 
 
                </tbody> 
 
            </table> 
 
        </div> 
 
    </div> 
 
</div> 
 
 
<!-- ========================================================= 
     BACKLOG MONITORING 
========================================================= --> 
 
<div class="section-card"> 
 
    <div class="section-header"> 
 
        <div class="section-header-content"> 
 
            <div class="section-header-icon dark"> 
 
                <i class="fa fa-layer-group"></i> 
 
            </div> 
 
            <div> 
 
                <h5 class="section-title"> 
                    Student Backlog Monitoring 
                </h5> 
 
 
            </div> 
 
        </div> 
 
    </div> 
 
 
    <div class="card-body p-3 p-md-4"> 
 
 
 
        <!-- BACKLOG SUMMARY --> 
 
        <div class="backlog-summary"> 
 
 
            <!-- UNRESOLVED --> 
 
            <div> 
 
                <button 
                    class="backlog-card unresolved-card" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#unresolvedBacklogs" 
                    aria-expanded="false" 
                    aria-controls="unresolvedBacklogs"> 
 
                    <div class="backlog-icon"> 
 
                        <i class="fa fa-exclamation-triangle"></i> 
 
                    </div> 
 
 
                    <div> 
 
                        <div class="backlog-count"> 
 
                            {{ $unresolvedBacklogCount }} 
 
                        </div> 
 
                        <div class="backlog-title"> 
 
                            Unresolved Backlogs 
 
                        </div> 
 
                        <div class="backlog-description"> 
 
                            Previous failed subjects not yet passed 
 
                        </div> 
 
                    </div> 
 
 
                    <i class="fa fa-chevron-down backlog-chevron"></i> 
 
                </button> 
 
            </div> 
 
 
            <!-- RESOLVED --> 
 
            <div> 
 
                <button 
                    class="backlog-card resolved-card" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#resolvedBacklogs" 
                    aria-expanded="false" 
                    aria-controls="resolvedBacklogs"> 
 
                    <div class="backlog-icon"> 
 
                        <i class="fa fa-check-circle"></i> 
 
                    </div> 
 
 
                    <div> 
 
                        <div class="backlog-count"> 
 
                            {{ $resolvedBacklogCount }} 
 
                        </div> 
 
                        <div class="backlog-title"> 
 
                            Resolved Backlogs 
 
                        </div> 
 
                        <div class="backlog-description"> 
 
                            Previously failed subjects later passed 
 
                        </div> 
 
                    </div> 
 
 
                    <i class="fa fa-chevron-down backlog-chevron"></i> 
 
                </button> 
 
            </div> 
 
 
        </div> 
 
 
        <!-- ================================================= 
             UNRESOLVED DETAILS 
        ================================================= --> 
 
        <div 
            class="collapse" 
            id="unresolvedBacklogs"> 
 
            <div class="backlog-detail unresolved-detail"> 
 
                <div class="backlog-detail-header"> 
 
                    <i class="fa fa-exclamation-circle"></i> 
 
                    Unresolved Backlogs 
 
                </div> 
 
 
                <div class="backlog-detail-body"> 
 
                    <div class="backlog-info"> 
 
                        <i class="fa fa-info-circle me-2"></i> 
 
                        These subjects were failed in a previous semester 
                        and remain unresolved. 
 
                    </div> 
 
 
                    <div class="table-responsive"> 
 
                        <table 
                            class="table table-bordered table-hover backlog-table align-middle"> 
 
                            <thead class="table-danger"> 
 
                                <tr> 
 
                                    <th> 
                                        Course Code 
                                    </th> 
 
                                    <th> 
                                        Subject Title 
                                    </th> 
 
                                    <th> 
                                        Failed Semester 
                                    </th> 
 
                                    <th class="text-center"> 
                                        Attempt 
                                    </th> 
 
                                    <th class="text-center"> 
                                        Latest Grade 
                                    </th> 
<th class="text-center"> 
                                        Action 
                                    </th> 
 
                                </tr> 
 
                            </thead> 
 
 
                            <tbody> 
 
                                @forelse( 
                                    $correctedActiveBacklogs 
                                    as $backlog 
                                ) 
 
                                    <tr> 
 
                                        <td class="fw-semibold"> 
 
                                            {{ $backlog['subject_code'] }} 
 
                                        </td> 
 
 
                                        <td> 
 
                                            {{ $backlog['subject_title'] }} 
 
                                        </td> 
 
 
                                        <td> 
 
                                            {{ $backlog['failed_semester'] }} 
 
                                        </td> 
 
 
                                        <td class="text-center"> 
 
                                            {{ $backlog['attempt_no'] }} 
 
                                        </td> 
 
 
                                        <td class="text-center"> 
 
                                            <span class="badge bg-danger"> 
 
                                                {{ $backlog['latest_grade'] }} 
 
                                            </span> 
 
                                        </td> 
<td class="text-center"> 

                                            <form 
                                                method="POST" 
                                                action="{{ route('students.backlog.markPassed', [$student->student_id, $backlog['subject_id']]) }}" 
                                                class="d-inline" 
                                                onsubmit="return confirm('Mark {{ $backlog['subject_code'] }} as passed? This will create the next passing attempt and move the backlog to Resolved Backlogs.');"> 

                                                @csrf 

                                                <button 
                                                    type="submit" 
                                                    class="btn btn-sm btn-success">  

                                                    Mark as Passed 

                                                </button> 

                                            </form> 

                                         
 
                                        </td> 
                                    </tr> 
 
                                @empty 
 
                                    <tr> 
 
                                        <td 
                                            colspan="6" 
                                            class="text-center text-muted py-4"> 
 
                                            <i 
                                                class="fa fa-check-circle text-success me-2"> 
                                            </i> 
 
                                            No unresolved previous-semester 
                                            backlogs found. 
 
                                        </td> 
 
                                    </tr> 
 
                                @endforelse 
 
                            </tbody> 
 
                        </table> 
 
                    </div> 
 
                </div> 
 
            </div> 
 
        </div> 
 
 
        <!-- ================================================= 
             RESOLVED DETAILS 
        ================================================= --> 
 
        <div 
            class="collapse" 
            id="resolvedBacklogs"> 
 
            <div class="backlog-detail resolved-detail"> 
 
                <div class="backlog-detail-header"> 
 
                    <i class="fa fa-check-circle"></i> 
 
                    Resolved Backlogs 
 
                </div> 
 
 
                <div class="backlog-detail-body"> 
 
                    <div class="backlog-info"> 
 
                        <i class="fa fa-info-circle me-2"></i> 
 
                        These subjects were failed in an earlier semester 
                        and were subsequently passed. 
 
                    </div> 
 
 
                    <div class="table-responsive"> 
 
                        <table 
                            class="table table-bordered table-hover backlog-table align-middle"> 
 
                            <thead class="table-success"> 
 
                                <tr> 
 
                                    <th> 
                                        Course Code 
                                    </th> 
 
                                    <th> 
                                        Subject Title 
                                    </th> 
 
                                    <th class="text-center"> 
                                        Total Attempts 
                                    </th> 
 
                                </tr> 
 
                            </thead> 
 
 
                            <tbody> 
 
                                @forelse( 
                                    $resolvedBacklogs 
                                    as $backlog 
                                ) 
 
                                    <tr> 
 
                                        <td class="fw-semibold"> 
 
                                            {{ $backlog['subject_code'] }} 
 
                                        </td> 
 
 
                                        <td> 
 
                                            {{ $backlog['subject_title'] }} 
 
                                        </td> 
 
 
                                        <td class="text-center"> 
 
                                            {{ $backlog['attempt_no'] }} 
 
                                        </td> 
 
 
 
 
                                    </tr> 
 
                                @empty 
 
                                    <tr> 
 
                                        <td 
                                            colspan="3" 
                                            class="text-center text-muted py-4"> 
 
                                            <i class="fa fa-info-circle me-2"></i> 
 
                                            No resolved backlogs found. 
 
                                        </td> 
 
                                    </tr> 
 
                                @endforelse 
 
                            </tbody> 
 
                        </table> 
 
                    </div> 
 
                </div> 
 
            </div> 
 
        </div> 
 
    </div> 
 
</div> 
 
 
</div> 
 
</div> 
 
 
<!-- ============================================================= 
     ADD EVALUATION MODAL 
============================================================= --> 
 
<div 
    class="modal fade" 
    id="addEvaluationModal" 
    tabindex="-1" 
    aria-labelledby="addEvaluationModalLabel" 
    aria-hidden="true"> 
 
    <div 
        class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable evaluation-modal-dialog"> 
 
        <div class="modal-content evaluation-modal"> 
 
 
            <form 
                method="POST" 
                action="{{ route( 
                    'students.addGrade', 
                    $student->student_id 
                ) }}"> 
 
                @csrf 
 
 
                <!-- MODAL HEADER --> 
 
                <div class="evaluation-modal-header"> 
 
                    <div 
                        class="d-flex justify-content-between align-items-start"> 
 
                        <div> 
 
                            <div 
                                class="modal-title-main" 
                                id="addEvaluationModalLabel"> 
 
                                <i class="fa fa-plus-circle me-2"></i> 
 
                                New Academic Evaluation 
 
                            </div> 

 
                        </div> 
 
 
                        <button 
                            type="button" 
                            class="btn-close btn-close-white" 
                            data-bs-dismiss="modal"> 
                        </button> 
 
                    </div> 
 
                </div> 
 
 
                <!-- MODAL BODY --> 
 
                <div class="modal-body p-3 p-md-4"> 
 
 
                    <!-- STUDENT INFORMATION --> 
 
                    <div class="student-evaluation-info mb-4"> 
 
                        <div> 
 
                            <span class="info-label"> 
                                Student 
                            </span> 
 
                            <div class="fw-bold"> 
 
                                {{ $student->last_name }}, 
                                {{ $student->first_name }} 
 
                                @if($student->middle_name) 
                                    {{ $student->middle_name }} 
                                @endif 
 
                            </div> 
 
                        </div> 
 
 
                        <div> 
 
                            <span class="info-label"> 
                                Student No. 
                            </span> 
 
                            <div class="fw-bold"> 
 
                                {{ $student->student_no }} 
 
                            </div> 
 
                        </div> 
 
                    </div> 
 
 
                    <!-- EVALUATION PERIOD --> 
 
                    <div class="section-title-modal"> 
 
                        <i class="fa fa-calendar-alt text-primary"></i> 
 
                        Evaluation Period 
 
                    </div> 
 
 
                    <div class="row g-3 mb-4"> 
 
 
                        <!-- SCHOOL YEAR --> 
 
                        <div class="col-md-5"> 
 
                            <label 
                                class="form-label fw-semibold"> 
 
                                School Year 
 
                            </label> 
 
 
                            <select 
                                class="form-select school-year-select" 
                                id="schoolYearSelect" 
                                required> 
 
                                <option value=""> 
 
                                    Select School Year 
 
                                </option> 
 
 
                                @php
                                    $uniqueSchoolYears = $semesters
                                        ->pluck('school_year')
                                        ->filter()
                                        ->unique()
                                        ->sort(function ($a, $b) {
                                            $yearA = (int) preg_replace('/[^0-9].*/', '', $a);
                                            $yearB = (int) preg_replace('/[^0-9].*/', '', $b);
                                            return $yearB <=> $yearA;
                                        })
                                        ->values();
                                @endphp 
 
 
                                @foreach( 
                                    $uniqueSchoolYears 
                                    as $schoolYear 
                                ) 
 
                                    <option 
                                        value="{{ $schoolYear }}"> 
 
                                        {{ $schoolYear }} 
 
                                    </option> 
 
                                @endforeach 
 
                            </select> 
 
                        </div> 
 
 
                        <!-- SEMESTER --> 
 
                        <div class="col-md-7"> 
 
                            <label 
                                class="form-label fw-semibold d-block"> 
 
                                Semester 
 
                            </label> 
 
 
                            <div class="semester-options"> 
 
 
                                <div class="form-check semester-option"> 
 
                                    <input 
                                        class="form-check-input semester-radio" 
                                        type="radio" 
                                        name="semester_choice" 
                                        value="1st Semester" 
                                        id="firstSemester"> 
 
 
                                    <label 
                                        class="form-check-label" 
                                        for="firstSemester"> 
 
                                        1st Semester 
 
                                    </label> 
 
                                </div> 
 
 
                                <div class="form-check semester-option"> 
 
                                    <input 
                                        class="form-check-input semester-radio" 
                                        type="radio" 
                                        name="semester_choice" 
                                        value="2nd Semester" 
                                        id="secondSemester"> 
 
 
                                    <label 
                                        class="form-check-label" 
                                        for="secondSemester"> 
 
                                        2nd Semester 
 
                                    </label> 
 
                                </div> 
 
 
                            </div> 
 
                        </div> 
 
                    </div> 
 
 
                    <input 
                        type="hidden" 
                        name="semester_id" 
                        id="semesterId" 
                        required> 
 
 
                    <div 
                        class="text-danger small mb-4" 
                        id="semesterError" 
                        style="display:none;"> 
 
                        <i class="fa fa-exclamation-circle me-1"></i> 
 
                        Please select a valid school year and semester. 
 
                    </div> 
 
 
                    <!-- YEAR LEVEL --> 
 
                    <div class="mb-4"> 
 
                        <label 
                            class="form-label fw-semibold"> 
 
                            Year Level 
 
                        </label> 
 
 
                        <select 
                            name="year_level" 
                            class="form-select" 
                            required> 
 
                            <option value=""> 
 
                                Select Year Level 
 
                            </option> 
 
 
                            <option 
                                value="1st Year" 
                                {{ $student->current_year_level === '1st Year' 
                                    ? 'selected' 
                                    : '' 
                                }}> 
 
                                1st Year 
 
                            </option> 
 
 
                            <option 
                                value="2nd Year" 
                                {{ $student->current_year_level === '2nd Year' 
                                    ? 'selected' 
                                    : '' 
                                }}> 
 
                                2nd Year 
 
                            </option> 
 
 
                            <option 
                                value="3rd Year" 
                                {{ $student->current_year_level === '3rd Year' 
                                    ? 'selected' 
                                    : '' 
                                }}> 
 
                                3rd Year 
 
                            </option> 
 
 
                            <option 
                                value="4th Year" 
                                {{ $student->current_year_level === '4th Year' 
                                    ? 'selected' 
                                    : '' 
                                }}> 
 
                                4th Year 
 
                            </option> 
 
                        </select> 
 
                    </div> 
 
 
                    <!-- TOTAL SUBJECTS -->

                    <div class="mb-4">

                        <label class="form-label fw-semibold">

                            Total Subjects for This Semester
                            <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa fa-list-ol"></i>
                            </span>

                            <input
                                type="number"
                                name="total_subjects"
                                id="evaluationTotalSubjects"
                                class="form-control"
                                value="{{ old('total_subjects') }}"
                                min="1"
                                max="100"
                                step="1"
                                required
                                autocomplete="off"
                                placeholder="Enter total number of subjects"
                            >

                        </div>

                        <div
                            id="totalSubjectsError"
                            class="text-danger small mt-1"
                            style="display:none;">
                        </div>

                    </div>


                    <!-- SUBJECTS --> 
 
                    <div class="section-title-modal"> 
 
                        <i class="fa fa-book text-primary"></i> 
 
                        Subjects and Grades 
 
                    </div> 

 
                    <!-- SUBJECT HEADER -->

                    <div class="subject-header">

                        <div class="subject-header-left">

                            <div style="width:28px;"></div>

                            <div class="flex-grow-1">
                                Course Code
                            </div>

                            <div class="subject-header-grade">
                                Grade
                            </div>

                        </div>

                        <div class="subject-header-action d-none d-md-block">
                            Action
                        </div>

                    </div>


                    <!-- SUBJECT LIST -->

                    <div id="subjectContainer" class="subject-list">

                        <!-- FIRST SUBJECT ROW -->

                        <div class="subject-row">

                            <div class="subject-number">1</div>

                            <div class="flex-grow-1">

                                <label class="form-label d-md-none">
                                    Course Code
                                </label>

                                <input
                                    type="text"
                                    name="subject_code_1"
                                    value="{{ old('subject_code_1') }}"
                                    class="form-control subject-code"
                                    placeholder="Example: IT 101"
                                    style="text-transform: uppercase;"
                                    autocomplete="off"
                                >

                            </div>

                            <div class="grade-field">

                                <label class="form-label d-md-none">
                                    Grade
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="74"
                                    name="grade_1"
                                    value="{{ old('grade_1') }}"
                                    class="form-control grade-input"
                                    placeholder="Grade"
                                >

                            </div>

                            <div style="width:38px;"></div>

                        </div>

                    </div>


                    <!-- ADD SUBJECT BUTTON -->

                    <div class="subject-add-bar">

                        <button
                            type="button"
                            id="addSubject"
                            class="btn btn-outline-primary"
                        >
                            <i class="fa fa-plus me-1"></i>
                            Add Subject
                        </button>

                    </div>


                <!-- MODAL FOOTER --> 
 
                <div class="modal-footer"> 

 
                    <button 
                        type="submit" 
                        class="btn btn-success"> 
 
                        <i class="fa fa-save me-1"></i> 
 
                        Save Evaluation 
 
                    </button> 
 
                </div> 
 
 
            </form> 
 
        </div> 
 
    </div> 
 
</div> 
 
 
<!-- ============================================================= 
     JAVASCRIPT 
============================================================= --> 
 
<script> 
 
document.addEventListener('DOMContentLoaded', function () { 
 
 
    /* ========================================================= 
       SEMESTER DATA 
    ========================================================= */ 
 
    const semesterData = @json( 
 
        $semesters->map(function ($semester) { 
 
            return [ 
 
                'id' => 
                    $semester->semester_id, 
 
                'school_year' => 
                    $semester->school_year, 
 
                'semester_name' => 
                    $semester->semester_name 
 
            ]; 
 
        })->values() 
 
    ); 
 
 
    /* ========================================================= 
       ELEMENTS 
    ========================================================= */ 
 
    const schoolYearSelect = 
        document.getElementById( 
            'schoolYearSelect' 
        ); 
 
 
    const semesterRadios = 
        document.querySelectorAll( 
            '.semester-radio' 
        ); 
 
 
    const semesterIdInput = 
        document.getElementById( 
            'semesterId' 
        ); 
 
 
    const semesterError = 
        document.getElementById( 
            'semesterError' 
        ); 
 
 
    /* ========================================================= 
       UPDATE SEMESTER ID 
    ========================================================= */ 
 
    function updateSemesterId() { 
 
        if ( 
            !schoolYearSelect || 
            !semesterIdInput 
        ) { 
 
            return; 
 
        } 
 
 
        const schoolYear = 
            schoolYearSelect.value; 
 
 
        const selectedRadio = 
            document.querySelector( 
                '.semester-radio:checked' 
            ); 
 
 
        const semesterName = 
            selectedRadio 
                ? selectedRadio.value 
                : ''; 
 
 
        semesterIdInput.value = ''; 
 
 
        if ( 
            !schoolYear || 
            !semesterName 
        ) { 
 
            if (semesterError) { 
 
                semesterError.style.display = 
                    'none'; 
 
            } 
 
            return; 
 
        } 
 
 
        const matchingSemester = 
            semesterData.find(function (semester) { 
 
                return ( 
 
                    String( 
                        semester.school_year 
                    ).trim() 
                    === 
                    String( 
                        schoolYear 
                    ).trim() 
 
                    && 
 
                    String( 
                        semester.semester_name 
                    ).trim() 
                    === 
                    String( 
                        semesterName 
                    ).trim() 
 
                ); 
 
            }); 
 
 
        if (matchingSemester) { 
 
            semesterIdInput.value = 
                matchingSemester.id; 
 
 
            if (semesterError) { 
 
                semesterError.style.display = 
                    'none'; 
 
            } 
 
        } else { 
 
            if (semesterError) { 
 
                semesterError.innerText = 
                    'The selected semester does not exist for this school year.'; 
 
                semesterError.style.display = 
                    'block'; 
 
            } 
 
        } 
 
    } 
 
 
    /* ========================================================= 
       SCHOOL YEAR 
    ========================================================= */ 
 
    if (schoolYearSelect) { 
 
        schoolYearSelect.addEventListener( 
            'change', 
            updateSemesterId 
        ); 
 
    } 
 
 
    /* ========================================================= 
       SEMESTER 
    ========================================================= */ 
 
    semesterRadios.forEach(function (radio) { 
 
        radio.addEventListener( 
            'change', 
            updateSemesterId 
        ); 
 
    }); 
 
 
    /* =========================================================
       DYNAMIC SUBJECT / GRADE ROWS
    ========================================================= */

    const addSubjectButton =
        document.getElementById('addSubject');

    const subjectContainer =
        document.getElementById('subjectContainer');


    function getSubjectRows() {

        if (!subjectContainer) {
            return [];
        }

        return Array.from(
            subjectContainer.querySelectorAll('.subject-row')
        );

    }


    function updateSubjectRows() {

        const rows = getSubjectRows();

        rows.forEach(function (row, index) {

            const number = index + 1;

            const numberBox =
                row.querySelector('.subject-number');

            const subjectCode =
                row.querySelector('.subject-code');

            const grade =
                row.querySelector('.grade-input');

            const removeButton =
                row.querySelector('.remove-subject');


            if (numberBox) {
                numberBox.textContent = number;
            }

            if (subjectCode) {
                subjectCode.name =
                    'subject_code_' + number;
            }

            if (grade) {
                grade.name =
                    'grade_' + number;
            }

            if (removeButton) {
                removeButton.style.display =
                    number === 1 ? 'none' : 'inline-flex';
            }

        });

    }


    function attachSubjectEvents(row) {

        if (!row) {
            return;
        }

        const subjectCode =
            row.querySelector('.subject-code');

        const gradeInput =
            row.querySelector('.grade-input');

        const removeButton =
            row.querySelector('.remove-subject');


        if (subjectCode) {

            subjectCode.addEventListener(
                'input',
                function () {
                    this.value = this.value.toUpperCase();
                }
            );

        }


        if (gradeInput) {

            gradeInput.addEventListener(
                'wheel',
                function () {
                    this.blur();
                },
                { passive: true }
            );

        }


        if (removeButton) {

            removeButton.addEventListener(
                'click',
                function () {

                    row.remove();

                    updateSubjectRows();

                }
            );

        }

    }


  function createSubjectRow() {

    const number =
        getSubjectRows().length + 1;

    const row =
        document.createElement('div');

    row.className = 'subject-row';

    row.innerHTML = `
        <div class="subject-number">
            ${number}
        </div>

        <div class="flex-grow-1">

            <label class="form-label d-md-none">
                Course Code
            </label>

            <input
                type="text"
                name="subject_code_${number}"
                class="form-control subject-code"
                placeholder="Example: IT 101"
                style="text-transform: uppercase;"
                autocomplete="off"
            >

        </div>

        <div class="grade-field">

            <label class="form-label d-md-none">
                Grade
            </label>

            <input
                type="number"
                step="0.01"
                min="0"
                max="74"
                name="grade_${number}"
                class="form-control grade-input"
                placeholder="Grade"
            >

        </div>

        <div style="width:38px;">

            <button
                type="button"
                class="btn btn-sm btn-outline-danger remove-subject"
                title="Remove Subject"
                aria-label="Remove Subject"
            >
                <i class="fa fa-times"></i>
            </button>

        </div>
    `;

    subjectContainer.appendChild(row);

    attachSubjectEvents(row);

    updateSubjectRows();

    const newSubjectCode =
        row.querySelector('.subject-code');

    if (newSubjectCode) {
        newSubjectCode.focus();
    }
}


    if (subjectContainer) {

        getSubjectRows().forEach(
            attachSubjectEvents
        );

        updateSubjectRows();

    }


    if (addSubjectButton && subjectContainer) {

        addSubjectButton.addEventListener(
            'click',
            function () {
                createSubjectRow();
            }
        );

    }


    /* =========================================================
       MODAL SCROLL CONTROLLER
       The modal BODY is the only scrolling area.
    ========================================================= */

    const addEvaluationModal =
        document.getElementById('addEvaluationModal');

    if (addEvaluationModal) {

        addEvaluationModal.addEventListener(
            'shown.bs.modal',
            function () {

                const modalBody =
                    addEvaluationModal.querySelector('.modal-body');

                if (modalBody) {
                    modalBody.scrollTop = 0;
                }

            }
        );

    }


    /* =========================================================
       FORM VALIDATION
    ========================================================= */ 
 
    const evaluationForm =
        document.querySelector(
            '#addEvaluationModal form'
        );


    if (evaluationForm) {

        evaluationForm.addEventListener(
            'submit',
            function (event) {

                updateSemesterId();

                const evaluationTotalSubjects =
                    document.getElementById(
                        'evaluationTotalSubjects'
                    );

                const totalSubjectsError =
                    document.getElementById(
                        'totalSubjectsError'
                    );

                const totalSubjectsValue =
                    evaluationTotalSubjects
                        ? parseInt(
                            evaluationTotalSubjects.value,
                            10
                        )
                        : 0;

                if (
                    !evaluationTotalSubjects ||
                    !Number.isInteger(totalSubjectsValue) ||
                    totalSubjectsValue < 1 ||
                    totalSubjectsValue > 100
                ) {

                    event.preventDefault();

                    if (totalSubjectsError) {
                        totalSubjectsError.innerText =
                            'Please enter a valid total number of subjects from 1 to 100.';
                        totalSubjectsError.style.display =
                            'block';
                    }

                    if (evaluationTotalSubjects) {
                        evaluationTotalSubjects.focus();
                    }

                    return false;
                }

                if (totalSubjectsError) {
                    totalSubjectsError.style.display = 'none';
                }

                if (
                    !schoolYearSelect ||
                    !schoolYearSelect.value ||
                    !document.querySelector('.semester-radio:checked') ||
                    !semesterIdInput.value
                ) {

                    event.preventDefault();

                    if (semesterError) {

                        semesterError.innerText =
                            'Please select a valid school year and semester.';

                        semesterError.style.display =
                            'block';

                    }

                    return false;
                }


                /* =================================================
                   SUBJECT / GRADE PAIR VALIDATION
                ================================================= */

                const rows =
                    getSubjectRows();

                let hasAnySubject = false;

                for (let i = 0; i < rows.length; i++) {

                    const subjectCode =
                        rows[i].querySelector('.subject-code');

                    const gradeInput =
                        rows[i].querySelector('.grade-input');

                    const subject =
                        subjectCode
                            ? subjectCode.value.trim()
                            : '';

                    const grade =
                        gradeInput
                            ? gradeInput.value.trim()
                            : '';


                    if (subject === '' && grade === '') {
                        continue;
                    }

                    hasAnySubject = true;


                    if (subject !== '' && grade === '') {

                        event.preventDefault();

                        alert(
                            'Please enter a grade for course code: ' +
                            subject
                        );

                        if (gradeInput) {
                            gradeInput.focus();
                        }

                        return false;

                    }


                    if (subject === '' && grade !== '') {

                        event.preventDefault();

                        alert(
                            'Please enter the course code for grade: ' +
                            grade
                        );

                        if (subjectCode) {
                            subjectCode.focus();
                        }

                        return false;

                    }

                }


                /*
                 * No failed subject is also valid.
                 * It means failure percentage = 0%.
                 */

                return true;

            }
        );

    }


    /* ========================================================= 
       REOPEN MODAL AFTER VALIDATION ERROR 
    ========================================================= */ 
 
    @if($errors->any()) 
 
        const evaluationModal = 
            document.getElementById( 
                'addEvaluationModal' 
            ); 
 
 
        if (evaluationModal) { 
 
            const modal = 
                new bootstrap.Modal( 
                    evaluationModal 
                ); 
 
            modal.show(); 
 
        } 
 
    @endif 
 
 
    /* ========================================================= 
       BACKLOG COLLAPSE CONTROLLER 
    ========================================================= */ 
 
    const unresolved = 
        document.getElementById( 
            'unresolvedBacklogs' 
        ); 
 
 
    const resolved = 
        document.getElementById( 
            'resolvedBacklogs' 
        ); 
 
 
    if ( 
        unresolved && 
        resolved 
    ) { 
 
 
        unresolved.addEventListener( 
            'show.bs.collapse', 
            function () { 
 
                const resolvedInstance = 
                    bootstrap.Collapse.getInstance( 
                        resolved 
                    ); 
 
 
                if (resolvedInstance) { 
 
                    resolvedInstance.hide(); 
 
                } 
 
            } 
        ); 
 
 
        resolved.addEventListener( 
            'show.bs.collapse', 
            function () { 
 
                const unresolvedInstance = 
                    bootstrap.Collapse.getInstance( 
                        unresolved 
                    ); 
 
 
                if (unresolvedInstance) { 
 
                    unresolvedInstance.hide(); 
 
                } 
 
            } 
        ); 
 
    } 
 
 
    /* ========================================================= 
       BACKLOG CHEVRON ANIMATION 
    ========================================================= */ 
 
    const unresolvedButton = 
        document.querySelector( 
            '[data-bs-target="#unresolvedBacklogs"]' 
        ); 
 
 
    const resolvedButton = 
        document.querySelector( 
            '[data-bs-target="#resolvedBacklogs"]' 
        ); 
 
 
    if (unresolvedButton && unresolved) { 
 
        unresolved.addEventListener( 
            'show.bs.collapse', 
            function () { 
 
                const icon = 
                    unresolvedButton.querySelector( 
                        '.backlog-chevron' 
                    ); 
 
                if (icon) { 
 
                    icon.style.transform = 
                        'rotate(180deg)'; 
 
                } 
 
            } 
        ); 
 
 
        unresolved.addEventListener( 
            'hide.bs.collapse', 
            function () { 
 
                const icon = 
                    unresolvedButton.querySelector( 
                        '.backlog-chevron' 
                    ); 
 
                if (icon) { 
 
                    icon.style.transform = 
                        'rotate(0deg)'; 
 
                } 
 
            } 
        ); 
 
    } 
 
 
    if (resolvedButton && resolved) { 
 
        resolved.addEventListener( 
            'show.bs.collapse', 
            function () { 
 
                const icon = 
                    resolvedButton.querySelector( 
                        '.backlog-chevron' 
                    ); 
 
                if (icon) { 
 
                    icon.style.transform = 
                        'rotate(180deg)'; 
 
                } 
 
            } 
        ); 
 
 
        resolved.addEventListener( 
            'hide.bs.collapse', 
            function () { 
 
                const icon = 
                    resolvedButton.querySelector( 
                        '.backlog-chevron' 
                    ); 
 
                if (icon) { 
 
                    icon.style.transform = 
                        'rotate(0deg)'; 
 
                } 
 
            } 
        ); 
 
    } 
 
}); 
 
</script> 
 
@endsection
