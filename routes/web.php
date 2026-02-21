<?php

use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\NotesController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\RentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoutineController;
use App\Http\Controllers\Admin\RoutineScraperController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use Illuminate\Support\Facades\Route;




// Public Routes
Route::get('/', [PublicHomeController::class, 'index'])->name('home');
Route::get('/cover-page-generator', [PublicHomeController::class, 'coverPageGenerator'])->name('cover-page-generator');
Route::post('/preview-cover-page', [PublicHomeController::class, 'previewCoverPage'])->name('cover-page-generator.preview');
Route::get('/cgpa-calculator', [PublicHomeController::class, 'cgpaCalculator'])->name('cgpa-calculator');
Route::get('/notes', [PublicHomeController::class, 'notes'])->name('public.notes');

Route::prefix('admin-dashboard')->group(function () {

    // Super Admin, Admin, User Dashboard Routes
    Route::middleware(['auth', 'verified', 'role:super_admin|admin|user|faculty|student'])->group(function () {

        Route::get('/', [HomeController::class, 'index'])->name('dashboard');

        // Notes Management Routes
        Route::resource('notes', NotesController::class)->names('admin.notes');
        Route::get('notes/{note}/download', [NotesController::class, 'download'])->name('admin.notes.download');
        Route::get('notes/export', [NotesController::class, 'export'])->name('admin.notes.export');
        Route::post('notes/bulk-delete', [NotesController::class, 'bulkDelete'])->name('admin.notes.bulk-delete');

        Route::prefix('/classes')->name('admin.')->group(function () {
            // Classes
            Route::get('/', [ClassController::class, 'index'])->name('classes.index');
            Route::get('/create', [ClassController::class, 'create'])->name('classes.create');
            Route::post('create', [ClassController::class, 'store'])->name('classes.store');
            Route::get('/{class}', [ClassController::class, 'show'])->name('classes.show');
            Route::put('/{class}/status', [ClassController::class, 'updateStatus'])->name('classes.status');
            Route::post('/join', [ClassController::class, 'join'])->name('classes.join');
            Route::post('/leave', [ClassController::class, 'leave'])->name('classes.leave');
            Route::delete('/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');
            Route::delete('/{classId}/remove-student/{studentId}', [ClassController::class, 'removeStudent'])->name('classes.remove-student');

            // Assignments
            Route::get('/{class}/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
            Route::get('/{class}/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
            Route::post('/{class}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
            Route::get('/{class}/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
            Route::get('/{class}/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('assignments.edit');
            Route::put('/{class}/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
            Route::delete('/{class}/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
            Route::put('/{class}/assignments/{assignment}/status', [AssignmentController::class, 'updateStatus'])->name('assignments.status');
            Route::get('/{class}/assignments/{assignment}/submissions/{submission}', [AssignmentController::class, 'viewSubmission'])->name('assignments.submissions.view');

            // Submissions
            Route::post('/{class}/assignments/{assignment}/submit', [SubmissionController::class, 'submit'])->name('submissions.submit');
            Route::post('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
            Route::get('/submissions/{submission}/download/{index}', [SubmissionController::class, 'downloadAttachment'])->name('submissions.download');
            Route::put('/classes/{class}/assignments/{assignment}/submissions/{submission}', [AssignmentController::class, 'updateSubmission'])->name('submissions.update');
        });

        Route::get('/my-routines', [RoutineController::class, 'myRoutines'])->name('admin.myRoutines');

        // Routine Management Routes
        Route::resource('routines', RoutineController::class)->names('admin.routines');
        Route::get('routines/bulk-create', [RoutineController::class, 'bulkCreate'])->name('admin.routines.bulk-create');
        Route::post('routines/bulk-destroy', [RoutineController::class, 'bulkDestroy'])->name('admin.routines.bulk-destroy');
        Route::get('routines/export', [RoutineController::class, 'export'])->name('admin.routines.export');
        Route::get('routines/filter-options', [RoutineController::class, 'getFilterOptions'])->name('admin.routines.filter-options');

        // Rent management
        Route::resource('rent', RentController::class)->names('admin.rent-posts');
        Route::patch('/rent-posts/{rentPost}/toggle-availability', [RentController::class, 'toggleAvailability'])->name('admin.rent-posts.toggle-availability');
        Route::get('/my-rent-posts', [RentController::class, 'myPosts'])->name('admin.rent-posts.my-posts');

    });

    // Super Admin, Admin, User Dashboard Routes
    Route::middleware(['auth', 'verified', 'role:super_admin|admin'])->group(function () {

        Route::resource('programs', ProgramController::class)->names('admin.programs');


        Route::prefix('scraper')->group(function () {
            Route::get('/program/{code}', [RoutineScraperController::class, 'scrapeProgram'])->name('admin.scraper.program.scrape');
            Route::get('/status', [RoutineScraperController::class, 'getStatus']);
        });

        Route::get('/api-documentation', [ApiController::class, 'index'])->name('admin.api.index');

        Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    });

    // Super Admin Dashboard Routes
    Route::middleware(['auth', 'verified', 'role:super_admin'])->group(function () {

        Route::resource('roles', RoleController::class)->names('admin.roles');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'givePermission'])->name('admin.roles.permissions.give');
        Route::delete('/roles/{role}/permissions/{permission}', [RoleController::class, 'revokePermission'])->name('admin.roles.permissions.revoke');

        Route::resource('permissions', PermissionController::class)->names('admin.permissions');
        Route::post('/permissions/{permission}/roles', [PermissionController::class, 'assignRole'])->name('admin.permissions.roles.assign');
        Route::delete('/permissions/{permission}/roles/{role}', [PermissionController::class, 'removeRole'])->name('admin.permissions.roles.remove');

        Route::resource('users', UserController::class)->names('admin.users');
        Route::put('/users/{user}/status', [UserController::class, 'updateStatus'])->name('admin.users.status');
        Route::post('/users/{user}/roles', [UserController::class, 'assignRole'])->name('admin.users.roles.assign');
        Route::delete('/users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('admin.users.roles.remove');
        Route::post('/users/{user}/permissions', [UserController::class, 'givePermission'])->name('admin.users.permissions.give');
        Route::delete('/users/{user}/permissions/{permission}', [UserController::class, 'revokePermission'])->name('admin.users.permissions.revoke');
    });

});


/*
Route::middleware(['permission:manage users'])->group(function () {
    // Additional routes that require 'manage users' permission can be added here
});
*/



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
