<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SemesterController;


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [AuthController::class, 'showLogin']
)->name('login');

Route::post(
    '/login',
    [AuthController::class, 'login']
)->name('login.process');

Route::post(
    '/logout',
    [AuthController::class, 'logout']
)->name('logout');


/*
|--------------------------------------------------------------------------
| CHANGE PASSWORD
|--------------------------------------------------------------------------
*/

Route::get(
    '/change-password',
    [AuthController::class, 'showChangePassword']
)->name('change.password');

Route::post(
    '/change-password',
    [AuthController::class, 'changePassword']
)->name('change.password.update');


/*
|--------------------------------------------------------------------------
| PROTECTED ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['checklogin', 'autologout'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [AuthController::class, 'dashboard']
    )->name('dashboard');

    Route::get(
        '/home',
        [HomeController::class, 'index']
    )->name('home');


    /*
    |--------------------------------------------------------------------------
    | REPORTS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/reports',
        [ReportController::class, 'index']
    )->name('reports.index');

    Route::get(
        '/reports/generate',
        [ReportController::class, 'generate']
    )->name('reports.generate');


    /*
    |--------------------------------------------------------------------------
    | SCHOOL YEAR MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/school-years',
        [SemesterController::class, 'index']
    )->name('semesters.index');

    Route::post(
        '/school-years',
        [SemesterController::class, 'store']
    )->name('semesters.store');

    Route::put(
        '/school-years/{schoolYear}',
        [SemesterController::class, 'update']
    )->name('semesters.update');


    /*
    |--------------------------------------------------------------------------
    | STUDENT STATUS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/students/status/{status}',
        [StudentController::class, 'statusList']
    )->name('students.status');


    /*
    |--------------------------------------------------------------------------
    | ADD GRADE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/students/add-grade/{id}',
        [StudentController::class, 'addGrade']
    )->name('students.addGrade');


    /*
    |--------------------------------------------------------------------------
    | MARK BACKLOG AS PASSED
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/students/{id}/backlog/{subjectId}/mark-passed',
        [StudentController::class, 'markBacklogAsPassed']
    )->name('students.backlog.markPassed');


    /*
    |--------------------------------------------------------------------------
    | STUDENT HISTORY
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/students/{id}/history',
        [StudentController::class, 'history']
    )->name('students.history');


    /*
    |--------------------------------------------------------------------------
    | PRIORITY ALERT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/students/{id}/priority-alert',
        [StudentController::class, 'priorityAlert']
    )->name('students.priorityAlert');


    /*
    |--------------------------------------------------------------------------
    | NOTICES
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/students/{id}/notice',
        [NoticeController::class, 'generate']
    )->name('notices.generate');


    Route::get(
        '/students/{id}/notice/{notice}/view',
        [NoticeController::class, 'view']
    )->name('notices.view');


    Route::get(
        '/students/{id}/notice/{notice}/print',
        [NoticeController::class, 'print']
    )->name('notices.print');


    Route::get(
        '/students/{id}/notice/{notice}/download',
        [NoticeController::class, 'download']
    )->name('notices.download');


    Route::post(
        '/students/{id}/notice/probation-preview',
        [NoticeController::class, 'previewProbation']
    )->name('notices.probation.preview');


    /*
    |--------------------------------------------------------------------------
    | STUDENTS RESOURCE
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'students',
        StudentController::class
    );

});