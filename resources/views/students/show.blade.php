@extends('layouts.app')

@section('content')

@php

/* =========================================================
| CURRENT / MOST RECENT EVALUATED SEMESTER
========================================================= */

$currentStatus = optional($latestStatus)->academic_status ?? 'Below Treshold';

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
    $currentStatus = 'Below Treshold';
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
========================================================= */

/*
| Use the semester attached to the most recently submitted evaluation.
| Keep $currentSemester unchanged for the other academic-standing sections.
*/
$recentSemesterId = $recentEvaluationSemesterId ?? $currentSemesterId;

$recentEvaluationSemester = $recentSemesterId
    ? $semesters->firstWhere('semester_id', $recentSemesterId)
    : null;

$recentFailedSubjects = collect($failedSubjects ?? [])
    ->filter(function ($grade) use ($recentSemesterId) {
        if ($recentSemesterId === null) {
            return false;
        }

        return (int) optional($grade)->semester_id === (int) $recentSemesterId;
    })
    ->sortBy(function ($grade) {
        return (int) optional($grade)->subject_id;
    })
    ->values();

@endphp


<style>
/* =========================================================
   MAIN PAGE BACKGROUND
========================================================= */

.student-show-wrapper {
    background: #7e8388;
    min-height: calc(100vh - 56px);
    margin: 0;
    padding: 22px 0 50px;
}

.student-show-wrapper,
.student-show-wrapper * {
    box-sizing: border-box;
}

.student-page {
    max-width: 1500px;
    margin: 0 auto;
    padding: 0 18px 20px;
}

.student-page .card {
    border-radius: 14px;
}

/* =========================================================
   STUDENT HEADER
========================================================= */

.student-header {
    background: #f8f9fa;
    border: 1px solid #e2e5e8;
    border-radius: 16px;
    box-shadow: 0 5px 18px rgba(0,0,0,.13);
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
    min-width: 0;
}

.student-profile-icon {
    width: 58px;
    height: 58px;
    min-width: 58px;
    border-radius: 14px;
    background: #e4f3e7;
    color: #198754;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 25px;
}

.student-name {
    margin: 0;
    font-size: 25px;
    font-weight: 700;
    color: #252a2e;
    line-height: 1.35;
    overflow-wrap: anywhere;
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
    background: #fff4d6;
    color: #946c00;
    border: 1px solid #e7cd7b;
    font-size: 13px;
    vertical-align: middle;
    cursor: help;
}

.student-residency-alert:hover {
    background: #946c00;
    color: #fff;
}

.student-number {
    color: #646d75;
    font-size: 14px;
    margin-top: 5px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    color: #626b73;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
    transition: color .2s ease;
}

.back-link:hover {
    color: #198754;
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
    transition: all .2s ease;
}

.student-actions .btn:hover {
    transform: translateY(-1px);
}

/* =========================================================
   ALERTS
========================================================= */

.page-alert {
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 10px;
    box-shadow: 0 3px 12px rgba(0,0,0,.10);
    margin-bottom: 20px;
}

/* =========================================================
   SECTION CARDS
========================================================= */

.section-card {
    background: #f8f9fa;
    border: 1px solid #e0e4e7;
    border-radius: 14px;
    box-shadow: 0 5px 16px rgba(0,0,0,.11);
    overflow: hidden;
    margin-bottom: 20px;
}

.section-header {
    padding: 17px 22px;
    border-bottom: 1px solid #dfe3e6;
    background: #f1f3f5;
}

.section-header-content {
    display: flex;
    align-items: center;
    gap: 11px;
}

.section-header-icon {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.section-header-icon.blue {
    background: #e4f0ff;
    color: #1768b2;
}

.section-header-icon.orange {
    background: #fff0dc;
    color: #b96a08;
}

.section-header-icon.dark {
    background: #e1e5e8;
    color: #343a40;
}

.section-title {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #292e33;
}

.section-subtitle {
    margin: 2px 0 0;
    font-size: 13px;
    color: #66717a;
}

/* =========================================================
   CURRENT ACADEMIC STATUS
========================================================= */

.status-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.status-info {
    min-width: 0;
    min-height: 108px;
    padding: 17px;
    background: #eceff1;
    border: 1px solid #d8dde1;
    border-radius: 11px;
    transition: background .2s ease, border-color .2s ease,
                box-shadow .2s ease;
}

.status-info:hover {
    border-color: #c3cbd1;
    background: #f8f9fa;
    box-shadow: 0 3px 10px rgba(0,0,0,.05);
}

.status-label {
    display: block;
    color: #606b74;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 8px;
}

.status-value {
    font-size: 21px;
    font-weight: 700;
    color: #252a2e;
    overflow-wrap: anywhere;
}

.status-badge {
    display: inline-block;
    font-size: 13px;
    padding: 7px 11px;
    border-radius: 7px;
}

/* =========================================================
   TABLES
========================================================= */

.failed-section-body {
    padding: 0;
    background: #f8f9fa;
}

.failed-table,
.failed-history-table,
.backlog-table {
    margin-bottom: 0;
    color: #343a40;
}

.failed-table thead th,
.failed-history-table thead th {
    background: #e9edf0 !important;
    color: #424c55 !important;
    border-color: #d5dbe0 !important;
    border-top: 0;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    padding: 13px 16px;
    vertical-align: middle;
}

.failed-table tbody td,
.failed-history-table tbody td {
    padding: 14px 16px;
    border-color: #e0e4e7;
    vertical-align: middle;
    background: #f8f9fa;
}

.failed-table tbody tr,
.failed-history-table tbody tr,
.backlog-table tbody tr {
    transition: background .15s ease;
}

.failed-table tbody tr:hover td,
.failed-history-table tbody tr:hover td {
    background: #edf1f4 !important;
}

.course-code {
    font-weight: 700;
    color: #343a40;
}

.subject-title {
    color: #495057;
}

.grade-failed {
    display: inline-block;
    background: #fce8eb;
    color: #b42332;
    border: 1px solid #efc3ca;
    font-weight: 700;
    padding: 6px 10px;
    border-radius: 7px;
}

.failed-history-table {
    border-color: #d9dfe3;
}

.failed-history-table tbody tr:hover {
    background: #edf1f4;
}

/* =========================================================
   BACKLOG SUMMARY
========================================================= */

.backlog-intro {
    color: #626d76;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 18px;
}

.backlog-summary {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
}

.backlog-card {
    width: 100%;
    min-height: 112px;
    border-radius: 13px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    text-align: left;
    background: #f8f9fa;
    cursor: pointer;
    transition: all .2s ease;
    box-shadow: 0 2px 7px rgba(0,0,0,.04);
}

.backlog-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 7px 20px rgba(0,0,0,.10);
}

.backlog-card:focus-visible {
    outline: 3px solid #6ea8fe;
    outline-offset: 3px;
}

.unresolved-card {
    border: 1px solid #e9b7be;
}

.unresolved-card:hover {
    border-color: #dc3545;
    background: #fffafa;
}

.resolved-card {
    border: 1px solid #b5d9c4;
}

.resolved-card:hover {
    border-color: #198754;
    background: #f7fcf9;
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
    background: #fce8eb;
    color: #b42332;
}

.resolved-card .backlog-icon {
    background: #e4f5eb;
    color: #198754;
}

.backlog-count {
    font-size: 25px;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 4px;
}

.unresolved-card .backlog-count {
    color: #b42332;
}

.resolved-card .backlog-count {
    color: #198754;
}

.backlog-title {
    font-size: 15px;
    font-weight: 700;
    color: #343a40;
    margin-bottom: 3px;
}

.backlog-description {
    font-size: 12px;
    line-height: 1.5;
    color: #626d76;
}

.backlog-chevron {
    margin-left: auto;
    flex-shrink: 0;
    color: #818b94;
    transition: transform .2s ease;
}

/* =========================================================
   COLLAPSED BACKLOG DETAILS
========================================================= */

.backlog-detail {
    border-radius: 12px;
    overflow: hidden;
    margin-top: 18px;
    border: 1px solid #d5dce1;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}

.backlog-detail-header {
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 9px;
    font-weight: 700;
}

.unresolved-detail .backlog-detail-header {
    background: #f9e9eb;
    color: #a92332;
    border-bottom: 1px solid #ebc5ca;
}

.resolved-detail .backlog-detail-header {
    background: #e7f4ec;
    color: #17643f;
    border-bottom: 1px solid #c4dfce;
}

.backlog-detail-body {
    padding: 18px;
    background: #f5f7f8;
}

.backlog-info {
    border-radius: 8px;
    padding: 11px 13px;
    font-size: 13px;
    line-height: 1.6;
    margin-bottom: 16px;
}

.unresolved-detail .backlog-info {
    background: #fff8f8;
    border: 1px solid #ecd2d5;
    color: #65474a;
}

.resolved-detail .backlog-info {
    background: #f5fbf7;
    border: 1px solid #cfe4d6;
    color: #41634e;
}

.backlog-table {
    border-color: #d8dee3;
}

.backlog-table thead th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .3px;
    white-space: nowrap;
    padding: 11px 13px;
    vertical-align: middle;
    border-color: #d9dfe3;
}

.backlog-table tbody td {
    padding: 12px 13px;
    vertical-align: middle;
    border-color: #e0e4e7;
    background: #f8f9fa;
}

.backlog-table tbody tr:hover td {
    background: #edf1f4;
}

/* =========================================================
   EVALUATION MODAL
========================================================= */

.evaluation-modal {
    border: 1px solid #dce1e5;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 15px 45px rgba(0,0,0,.25);
    height: calc(100vh - 2rem);
    max-height: calc(100vh - 2rem);
    display: flex;
    flex-direction: column;
    background: #f5f7f8;
}

.evaluation-modal form {
    display: flex;
    flex-direction: column;
    min-height: 0;
    height: 100%;
}

.evaluation-modal .modal-body {
    background: #f5f7f8;
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
    background: #e6e9ec;
}

.evaluation-modal .modal-body::-webkit-scrollbar-thumb {
    background: #a4adb5;
    border-radius: 10px;
}

.evaluation-modal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #7e8388;
}

.evaluation-modal-header {
    background: #198754;
    color: #fff;
    padding: 18px 22px;
}

.modal-title-main {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 3px;
}

.modal-title-sub {
    font-size: 12px;
    opacity: .85;
}

.modal-body {
    background: #f5f7f8;
}

.student-evaluation-info {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 220px;
    gap: 15px;
    padding: 15px 17px;
    background: #e9edf0;
    border: 1px solid #d7dee3;
    border-radius: 10px;
}

.info-label {
    display: block;
    color: #626d76;
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
    border-bottom: 1px solid #d9dfe3;
}

.form-label {
    color: #424c55;
    font-size: 13px;
}

.form-select,
.form-control {
    border-radius: 8px;
    border-color: #cbd3d9;
    background-color: #fff;
    color: #292e33;
    min-height: 40px;
}

.form-select:focus,
.form-control:focus {
    border-color: #198754;
    box-shadow: 0 0 0 3px rgba(25,135,84,.14);
}

.input-group-text {
    background: #e6eaed;
    border-color: #cbd3d9;
    color: #4b5660;
    border-radius: 8px 0 0 8px;
}

.input-group .form-control {
    border-radius: 0 8px 8px 0;
}

.semester-options {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 40px;
}

.semester-option {
    border: 1px solid #d0d8de;
    border-radius: 8px;
    padding: 9px 13px;
    background: #fff;
    transition: background .2s ease, border-color .2s ease;
}

.semester-option:hover {
    background: #edf6f0;
    border-color: #8ac5a4;
}

.semester-option .form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.subject-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 10px 13px;
    background: #e5e9ec;
    border: 1px solid #d0d8de;
    border-bottom: 0;
    border-radius: 8px 8px 0 0;
    color: #4c5760;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.subject-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}

.subject-header-grade {
    width: 160px;
    flex-shrink: 0;
}

.subject-header-action {
    width: 38px;
    flex-shrink: 0;
    text-align: center;
}

.subject-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 13px;
    border: 1px solid #d0d8de;
    border-bottom: 0;
    background: #f8f9fa;
}

.subject-row:last-child {
    border-bottom: 1px solid #d0d8de;
    border-radius: 0 0 8px 8px;
}

.subject-number {
    width: 28px;
    height: 28px;
    min-width: 28px;
    border-radius: 8px;
    background: #e2f0e7;
    color: #198754;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
}

.grade-field {
    width: 160px;
    flex-shrink: 0;
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

.subject-add-bar {
    display: flex;
    justify-content: flex-end;
    padding: 12px 0;
}

.subject-list {
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

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}

.modal-footer {
    border-top: 1px solid #d9dfe3;
    padding: 14px 20px;
    background: #e9edf0;
}

.modal-footer .btn {
    border-radius: 8px;
    font-weight: 600;
}

/* =========================================================
   EMPTY STATES
========================================================= */

.empty-table-state {
    padding: 28px 15px !important;
    color: #626d76;
    background: #f8f9fa;
}

.empty-table-icon {
    font-size: 22px;
    margin-bottom: 7px;
}

/* =========================================================
   ACCESSIBILITY AND INTERACTION
========================================================= */

.student-show-wrapper button:focus-visible,
.student-show-wrapper a:focus-visible,
.evaluation-modal button:focus-visible {
    outline: 3px solid #0d6efd;
    outline-offset: 3px;
}

.student-show-wrapper .table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #a4adb5 #e6e9ec;
}

/* =========================================================
   RESPONSIVE LAYOUT
========================================================= */

@media (max-width: 1100px) {
    .student-header-top {
        flex-direction: column;
    }

    .student-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .status-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .student-show-wrapper {
        padding-top: 14px;
    }

    .student-page {
        padding-left: 10px;
        padding-right: 10px;
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
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .student-actions .btn {
        width: 100%;
        white-space: normal;
    }

    .status-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
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

    .subject-header-grade {
        width: 100px;
    }

    .semester-options {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    .semester-option {
        width: 100%;
    }

    .evaluation-modal {
        height: calc(100vh - 1rem);
        max-height: calc(100vh - 1rem);
        border-radius: 12px;
    }

    .evaluation-modal .modal-body {
        padding: 16px !important;
    }

    .section-header {
        padding: 15px 17px;
    }

    .backlog-detail-body {
        padding: 12px;
    }

    .failed-table thead th,
    .failed-table tbody td,
    .failed-history-table thead th,
    .failed-history-table tbody td,
    .backlog-table thead th,
    .backlog-table tbody td {
        padding: 11px 12px;
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

    .subject-header {
        gap: 8px;
        padding: 10px;
    }

    .subject-header-left {
        gap: 8px;
    }

    .subject-header-grade {
        width: 85px;
    }

    .subject-row {
        gap: 8px;
        padding: 10px;
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

    .student-header {
        padding: 16px;
    }

    .backlog-card {
        padding: 16px;
    }

    .status-info {
        min-height: 90px;
    }

    .section-title {
        font-size: 16px;
    }
}


/* =========================================================
   FAILED-SUBJECT HISTORY SEARCH AND GROUPING
========================================================= */

.history-search-wrapper {
    max-width: 520px;
}

.history-search-wrapper .input-group-text {
    background: #e9edf0;
    border-color: #cbd3d9;
    color: #59636c;
}

.history-search-wrapper .form-control {
    min-width: 0;
}

.history-year-heading {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 13px 16px;
    background: #e5eee8;
    color: #17643f;
    border: 1px solid #c7ddce;
    border-radius: 9px;
    font-size: 16px;
    font-weight: 700;
}

.history-semester-block {
    border: 1px solid #d9dfe3;
    border-radius: 10px;
    overflow: hidden;
    background: #f8f9fa;
}

.history-semester-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #f1f3f5;
    border-bottom: 1px solid #d9dfe3;
}

.history-semester-heading h6 {
    color: #343a40;
}

.history-notice-button {
    flex-shrink: 0;
    border-radius: 7px;
    font-weight: 600;
    white-space: nowrap;
}

#failedSubjectHistorySection .failed-history-table thead th {
    white-space: nowrap;
}

@media (max-width: 576px) {
    .history-semester-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .history-notice-button {
        width: 100%;
    }
}


/* =========================================================
   FINAL PAGE SPACING
========================================================= */

.student-page > .section-card:last-child {
    margin-bottom: 0;
}


/* =========================================================
   FAILURE-PERCENTAGE COMPARATIVE ANALYSIS
========================================================= */
.comparative-trend-card {
    background: #ffffff;
    border: 2px solid #aebdb2;
    border-radius: 13px;
    overflow: hidden;
    margin-top: 28px;
    margin-bottom: 24px;
    box-shadow: 0 3px 12px rgba(31, 41, 55, 0.04);
}
.comparative-trend-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 23px;
    background: #edf4ef;
    border-bottom: 1.5px solid #b5c9ba;
}
.comparative-trend-header i {
    color: #198754;
    font-size: 1.1rem;
}
.comparative-trend-header h5 {
    margin: 0;
    font-weight: 750;
    font-size: 1.02rem;
    color: #245a36;
}
.comparative-trend-body {
    padding: 24px;
    background: #ffffff;
}
.comparative-metric-card {
    height: 100%;
    padding: 18px;
    background: #f7f8fa;
    border: 1.5px solid #b5c0c9;
    border-radius: 10px;
}
.comparative-metric-label {
    display: block;
    color: #737d89;
    font-size: 0.72rem;
    font-weight: 750;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.comparative-metric-value {
    display: block;
    color: #26382c;
    font-size: 1.5rem;
    font-weight: 750;
    margin-bottom: 8px;
}
.comparative-metric-period {
    color: #737d89;
    font-size: 0.82rem;
    line-height: 1.6;
    overflow-wrap: anywhere;
}
.comparative-trend-summary {
    height: 100%;
    padding: 18px;
    border: 1.5px solid transparent;
    border-radius: 10px;
}
.comparative-trend-summary strong {
    display: block;
    font-size: 0.92rem;
    line-height: 1.5;
}
.comparative-trend-summary p {
    margin: 10px 0 0;
    font-size: 0.84rem;
    line-height: 1.6;
}
.comparative-trend-summary.improved {
    background: #eff8f1;
    border-color: #8fc5a0;
    color: #176b36;
}
.comparative-trend-summary.worsened {
    background: #fff2f2;
    border-color: #d98f8f;
    color: #a52d2d;
}
.comparative-trend-summary.neutral {
    background: #fff9e9;
    border-color: #d8c17b;
    color: #795d12;
}
.comparative-trend-chart {
    position: relative;
    width: 100%;
    height: 380px;
    padding: 20px;
    border: 2px solid #aebbb8;
    border-radius: 11px;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(31, 41, 55, 0.05);
}
@media (max-width: 767.98px) {
    .comparative-trend-body { padding: 17px; }
    .comparative-metric-value { font-size: 1.35rem; }
    .comparative-trend-chart { height: 320px; padding: 12px; }
}
@media print {
    .comparative-trend-card { box-shadow: none; }
}

</style>


<div class="student-show-wrapper">

<div class="container-fluid">

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
                                title="Residency Risk: Projected residency is {{ $projectedResidencyYears }} school years">

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
                class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#addEvaluationModal">

                <i class="fa fa-plus me-1"></i>

                New Evaluation

            </button>


            <a
                href="{{ route(
                    'students.edit',
                    $student->student_id
                ) }}"
                class="btn btn-outline-warning">

                <i class="fa fa-edit me-1"></i>

                Edit Student

            </a>

            <a
    href="{{ route(
        'students.residencyPrediction',
        $student->student_id
    ) }}"
    class="btn btn-outline-success"
>
    <i class="fa fa-clock me-1"></i>
    Residency Prediction
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

                @if($currentStatus === 'Below Treshold')

                    <span class="badge bg-success status-badge">
                        Below Treshold
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

                            @if($recentEvaluationSemester)

                                <div style="font-size: 0.9rem; font-weight: 700; line-height: 1.3; margin-bottom: 4px;">

                                    {{ $recentEvaluationSemester->school_year }}

                                    &middot;

                                    {{ $recentEvaluationSemester->semester_name }}

                                </div>

                            @endif

                            <div class="course-code" style="font-size: 0.8rem;">
                                Course Code
                            </div>

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
     COMPLETE FAILED-SUBJECT HISTORY
========================================================= -->

<div class="section-card">

    <div class="section-header">

        <div class="section-header-content">

            <div class="section-header-icon orange">
                <i class="fa fa-history"></i>
            </div>

            <div>
                <h5 class="section-title">Failed-Subject History</h5>
                <p class="section-subtitle mb-0">
                    Failed subjects organized by school year and semester
                </p>
            </div>

        </div>

    </div>

    <div class="card-body p-3 p-md-4">

        @php
            $historyRecords = collect($failedSubjectHistory ?? []);

            /*
             * Retrieve the latest recorded academic status for each semester.
             * This allows the history table to show the student's status
             * for the school year and semester associated with each record.
             */
            $historyStatuses = \App\Models\StudentStatusHistory::where(
                    'student_id',
                    $student->student_id
                )
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('semester_id')
                ->map(function ($statuses) {
                    return $statuses->first();
                });

            /*
             * Retrieve previously generated notices for this student.
             * Keep the most recently generated notice for each semester.
             */
            $historyNotices = \App\Models\AcademicNotice::where(
                    'student_id',
                    $student->student_id
                )
                ->whereNotNull('pdf_path')
                ->orderByDesc('generated_at')
                ->get()
                ->groupBy('semester_id')
                ->map(function ($notices) {
                    return $notices->first();
                });

            /*
             * Group failed grades by school year.
             */
            $failedHistoryByYear = $historyRecords
                ->groupBy(function ($grade) {
                    return optional($grade->semester)->school_year
                        ?? 'School year unavailable';
                })
                ->sortByDesc(function ($grades, $schoolYear) {
                    return (int) preg_replace('/\D/', '', $schoolYear);
                });

            /*
             * Normalize semester names for display and ordering.
             */
            $semesterOrder = function ($semesterName) {
                $name = strtolower(trim((string) $semesterName));

                if (
                    str_contains($name, '1st') ||
                    str_contains($name, 'first')
                ) {
                    return 1;
                }

                if (
                    str_contains($name, '2nd') ||
                    str_contains($name, 'second')
                ) {
                    return 2;
                }

                return 3;
            };
        @endphp

        <!-- SCHOOL YEAR SEARCH -->
        <div class="history-search-wrapper mb-4">

            <label
                for="failedHistoryYearSearch"
                class="form-label fw-semibold">

                Search School Year

            </label>

            <div class="input-group">

                <span class="input-group-text">
                    <i class="fa fa-search"></i>
                </span>

                <input
                    type="text"
                    id="failedHistoryYearSearch"
                    class="form-control"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="Enter school year, e.g. 2024 or 20242025"
                    aria-describedby="failedHistorySearchHelp">

            </div>

            <div
                id="failedHistoryNoSearchResults"
                class="alert alert-light border text-center mt-3 mb-0"
                style="display: none;">

                <i class="fa fa-search me-2"></i>
                No failed-subject history matches that school year.

            </div>

        </div>

        @forelse($failedHistoryByYear as $schoolYear => $yearGrades)

            @php
                $yearDigits = preg_replace('/\D/', '', (string) $schoolYear);

                $semesterGroups = $yearGrades
                    ->groupBy(function ($grade) {
                        return optional($grade->semester)->semester_id
                            ?? 'unknown';
                    })
                    ->sortBy(function ($semesterGrades) use ($semesterOrder) {
                        $semester = optional(
                            $semesterGrades->first()
                        )->semester;

                        return $semesterOrder(
                            optional($semester)->semester_name
                        );
                    });
            @endphp

            <div
                class="history-year-block mb-4"
                data-school-year="{{ $yearDigits }}">

                <div class="history-year-heading">

                    <div class="d-flex align-items-center gap-2">

                        <span class="history-year-icon">
                            <i class="fa fa-calendar-alt"></i>
                        </span>

                        <h6 class="mb-0 fw-bold">
                            School Year {{ $schoolYear }}
                        </h6>

                    </div>


                </div>

                @foreach($semesterGroups as $semesterFailedGrades)

                    @php
                        $historySemester = optional(
                            $semesterFailedGrades->first()
                        )->semester;

                        $historySemesterId = optional(
                            $historySemester
                        )->semester_id;

                        $historySemesterName = optional(
                            $historySemester
                        )->semester_name ?? 'Semester unavailable';

                        $historyNotice = $historySemesterId
                            ? $historyNotices->get($historySemesterId)
                            : null;

                        $historyStatus = $historySemesterId
                            ? $historyStatuses->get($historySemesterId)
                            : null;

                        $historyAcademicStatus = optional(
                            $historyStatus
                        )->academic_status ?? 'Status unavailable';

                        if ($historyAcademicStatus === 'For Shifting Out') {
                            $historyAcademicStatus = 'Shifting Out';
                        }
                    @endphp

                    <div class="history-semester-block mb-3">

                        <div class="history-semester-heading">

                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <h6 class="mb-1 fw-bold">
                                    {{ $historySemesterName }}
                                </h6>

                                <span class="badge bg-secondary">
                                    {{ $historyAcademicStatus }}
                                </span>
                            </div>

                            @if(
                                $historyNotice &&
                                !empty($historyNotice->pdf_path)
                            )

                                <a
                                    href="{{ route('notices.view', [
                                        'id' => $student->student_id,
                                        'notice' => $historyNotice->notice_id
                                    ]) }}"
                                    class="btn btn-sm btn-success history-notice-button">

                                    <i class="fa fa-file-pdf me-1"></i>
                                    View Notice

                                </a>

                            @endif

                        </div>

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover align-middle mb-0 failed-history-table">

                                <thead>

                                    <tr>
                                        <th>Course Code</th>
                                        <th>Subject Name</th>
                                        <th class="text-center">Units</th>
                                        <th class="text-center">Failed Grade</th>
                                        <th class="text-center">Attempt</th>
                                        <th class="text-center">Backlog Status</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach($semesterFailedGrades as $historyGrade)

                                        <tr>

                                            <td class="fw-semibold">
                                                {{ optional($historyGrade->subject)->subject_code ?? 'N/A' }}
                                            </td>

                                            <td>
                                                {{ optional($historyGrade->subject)->subject_title ?? 'N/A' }}
                                            </td>

                                            <td class="text-center">
                                                {{ optional($historyGrade->subject)->units ?? 'N/A' }}
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-danger">
                                                    {{ number_format((float) $historyGrade->grade, 2) }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                {{ $historyGrade->attempt_no }}
                                            </td>

                                            <td class="text-center">

                                                @if($historyGrade->backlog_resolution === 'Resolved')

                                                    <span class="badge bg-success">
                                                        Resolved
                                                    </span>

                                                @else

                                                    <span class="badge bg-warning text-dark">
                                                        Unresolved
                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @endforeach

            </div>

        @empty

            <div class="text-center text-muted py-4">

                <i class="fa fa-check-circle text-success me-2"></i>

                No failed-subject history has been recorded for this student.

            </div>

        @endforelse

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

</div>



<!-- =========================================================
     FAILURE-PERCENTAGE COMPARATIVE ANALYSIS AND TREND GRAPH
========================================================= -->

@php
    $comparisonHistory = collect($student->statusHistory ?? [])
        ->filter(function ($evaluation) {
            return $evaluation->semester !== null;
        })
        ->groupBy('semester_id')
        ->map(function ($semesterEvaluations) {
            return $semesterEvaluations->sortByDesc('status_id')->first();
        })
        ->sortBy(function ($evaluation) {
            $schoolYear = (string) optional($evaluation->semester)->school_year;
            $startYear = 0;

            if (preg_match('/((?:19|20)\\d{2})/', $schoolYear, $matches)) {
                $startYear = (int) $matches[1];
            }

            $semesterName = strtolower((string) optional($evaluation->semester)->semester_name);
            $semesterNumber = (
                str_contains($semesterName, '2') ||
                str_contains($semesterName, 'second')
            ) ? 2 : 1;

            return ($startYear * 10) + $semesterNumber;
        })
        ->values();

    $firstComparison = $comparisonHistory->first();
    $latestComparison = $comparisonHistory->last();
    $overallComparisonDifference = null;

    if ($firstComparison && $latestComparison && $comparisonHistory->count() >= 2) {
        $overallComparisonDifference =
            (float) $latestComparison->failure_percentage -
            (float) $firstComparison->failure_percentage;
    }
@endphp

<div class="comparative-trend-card">
    <div class="comparative-trend-header">
        <i class="fa fa-line-chart"></i>
        <div>
            <h5>Failure Percentage Comparative Analysis</h5>
            <small class="text-muted">Track changes in failure percentage across evaluated semesters</small>
        </div>
    </div>

    <div class="comparative-trend-body">
        @if($comparisonHistory->count() >= 2)
            <div class="row g-3 mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="comparative-metric-card">
                        <span class="comparative-metric-label">First Evaluation</span>
                        <span class="comparative-metric-value">{{ number_format((float) $firstComparison->failure_percentage, 2) }}%</span>
                        <div class="comparative-metric-period">
                            {{ optional($firstComparison->semester)->school_year }} — {{ optional($firstComparison->semester)->semester_name }}
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="comparative-metric-card">
                        <span class="comparative-metric-label">Latest Evaluation</span>
                        <span class="comparative-metric-value">{{ number_format((float) $latestComparison->failure_percentage, 2) }}%</span>
                        <div class="comparative-metric-period">
                            {{ optional($latestComparison->semester)->school_year }} — {{ optional($latestComparison->semester)->semester_name }}
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    @if($overallComparisonDifference < 0)
                        <div class="comparative-trend-summary improved">
                            <strong><i class="fa fa-arrow-down me-2"></i>Overall Improvement</strong>
                            <p>Failure decreased by {{ number_format(abs($overallComparisonDifference), 2) }} percentage points.</p>
                        </div>
                    @elseif($overallComparisonDifference > 0)
                        <div class="comparative-trend-summary worsened">
                            <strong><i class="fa fa-arrow-up me-2"></i>Overall Worsening</strong>
                            <p>Failure increased by {{ number_format($overallComparisonDifference, 2) }} percentage points.</p>
                        </div>
                    @else
                        <div class="comparative-trend-summary neutral">
                            <strong><i class="fa fa-minus me-2"></i>No Overall Change</strong>
                            <p>Failure percentage remained the same.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold mb-1">Semester Trends</h6>
                <p class="text-muted small mb-0">Track the student's failure percentage across evaluated semesters.</p>
            </div>

            <div class="comparative-trend-chart">
                <canvas id="studentSemesterTrendChart" role="img" aria-label="Line graph showing failure percentage across evaluated semesters"></canvas>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                (function () {
                    function initializeStudentSemesterTrendChart() {
                        const chartCanvas = document.getElementById('studentSemesterTrendChart');
                        if (!chartCanvas || typeof Chart === 'undefined' || chartCanvas.dataset.initialized === 'true') {
                            return;
                        }
                        chartCanvas.dataset.initialized = 'true';

                        const semesterLabels = @json(
                            $comparisonHistory->map(function ($evaluation) {
                                return optional($evaluation->semester)->school_year . ' - ' . optional($evaluation->semester)->semester_name;
                            })->values()
                        );

                        const failurePercentages = @json(
                            $comparisonHistory->map(function ($evaluation) {
                                return (float) $evaluation->failure_percentage;
                            })->values()
                        );

                        new Chart(chartCanvas, {
                            type: 'line',
                            data: {
                                labels: semesterLabels,
                                datasets: [{
                                    label: 'Failure Percentage',
                                    data: failurePercentages,
                                    borderColor: '#198754',
                                    backgroundColor: 'rgba(25, 135, 84, 0.12)',
                                    pointBackgroundColor: '#198754',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    borderWidth: 3,
                                    tension: 0.3,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: { usePointStyle: true, padding: 18 }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function (context) {
                                                return 'Failure Percentage: ' + Number(context.parsed.y).toFixed(2) + '%';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        suggestedMax: 100,
                                        title: { display: true, text: 'Failure Percentage (%)' },
                                        ticks: { callback: function (value) { return value + '%'; } },
                                        grid: { color: '#edf0f2' }
                                    },
                                    x: {
                                        title: { display: true, text: 'School Year and Semester' },
                                        grid: { display: false },
                                        ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 }
                                    }
                                }
                            }
                        });
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initializeStudentSemesterTrendChart);
                    } else {
                        initializeStudentSemesterTrendChart();
                    }
                })();
            </script>
        @elseif($comparisonHistory->count() === 1)
            <div class="text-center text-muted py-4">
                Only one evaluation is available. At least two evaluated semesters are needed to compare failure-percentage trends.
            </div>
        @else
            <div class="text-center text-muted py-4">
                No evaluation history is available for failure-percentage comparative analysis.
            </div>
        @endif
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
                                            return $yearA <=> $yearB;
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
                                placeholder="Enter total number of subjects">

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

                            <div class="subject-number">
                                1
                            </div>

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
                                    autocomplete="off">

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
                                    placeholder="Grade">

                            </div>

                            <div style="width:38px;"></div>

                        </div>

                    </div>


                    <!-- ADD SUBJECT BUTTON -->

                    <div class="subject-add-bar">

                        <button
                            type="button"
                            id="addSubject"
                            class="btn btn-outline-primary">

                            <i class="fa fa-plus me-1"></i>

                            Add Subject

                        </button>

                    </div>


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
       FAILED-SUBJECT HISTORY SCHOOL-YEAR SEARCH
    ========================================================= */

    const failedHistoryYearSearch =
        document.getElementById('failedHistoryYearSearch');

    const failedHistoryYearBlocks =
        document.querySelectorAll('.history-year-block');

    const failedHistoryNoSearchResults =
        document.getElementById('failedHistoryNoSearchResults');

    if (failedHistoryYearSearch) {

        failedHistoryYearSearch.addEventListener('input', function () {

            // Keep numeric characters only.
            const numericValue = this.value.replace(/[^0-9]/g, '');

            if (this.value !== numericValue) {
                this.value = numericValue;
            }

            const searchValue = numericValue.trim();
            let visibleYearCount = 0;

            failedHistoryYearBlocks.forEach(function (yearBlock) {

                const schoolYearDigits =
                    yearBlock.getAttribute('data-school-year') || '';

                // Empty search: show every school year.
                // Numeric search: show every year containing those digits.
                const matches =
                    searchValue === '' ||
                    schoolYearDigits.includes(searchValue);

                yearBlock.style.display = matches ? '' : 'none';

                if (matches) {
                    visibleYearCount++;
                }

            });

            // Show a message only when a non-empty search has no matches.
            if (failedHistoryNoSearchResults) {
                failedHistoryNoSearchResults.style.display =
                    searchValue !== '' && visibleYearCount === 0
                        ? 'block'
                        : 'none';
            }

        });

    }


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
                    number === 1
                        ? 'none'
                        : 'inline-flex';

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

                    this.value =
                        this.value.toUpperCase();

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

        row.className =
            'subject-row';

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
                    autocomplete="off">

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
                    placeholder="Grade">

            </div>

            <div style="width:38px;">

                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger remove-subject"
                    title="Remove Subject"
                    aria-label="Remove Subject">

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


    if (
        addSubjectButton &&
        subjectContainer
    ) {

        addSubjectButton.addEventListener(
            'click',
            function () {

                createSubjectRow();

            }
        );

    }


    /* =========================================================
       MODAL SCROLL CONTROLLER
    ========================================================= */

    const addEvaluationModal =
        document.getElementById(
            'addEvaluationModal'
        );


    if (addEvaluationModal) {

        addEvaluationModal.addEventListener(
            'shown.bs.modal',
            function () {

                const modalBody =
                    addEvaluationModal.querySelector(
                        '.modal-body'
                    );


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
                    !Number.isInteger(
                        totalSubjectsValue
                    ) ||
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

                    totalSubjectsError.style.display =
                        'none';

                }


                if (
                    !schoolYearSelect ||
                    !schoolYearSelect.value ||
                    !document.querySelector(
                        '.semester-radio:checked'
                    ) ||
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

                let hasAnySubject =
                    false;


                for (
                    let i = 0;
                    i < rows.length;
                    i++
                ) {

                    const subjectCode =
                        rows[i].querySelector(
                            '.subject-code'
                        );

                    const gradeInput =
                        rows[i].querySelector(
                            '.grade-input'
                        );


                    const subject =
                        subjectCode
                            ? subjectCode.value.trim()
                            : '';


                    const grade =
                        gradeInput
                            ? gradeInput.value.trim()
                            : '';


                    if (
                        subject === '' &&
                        grade === ''
                    ) {

                        continue;

                    }


                    hasAnySubject = true;


                    if (
                        subject !== '' &&
                        grade === ''
                    ) {

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


                    if (
                        subject === '' &&
                        grade !== ''
                    ) {

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


    if (
        unresolvedButton &&
        unresolved
    ) {

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


    if (
        resolvedButton &&
        resolved
    ) {

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