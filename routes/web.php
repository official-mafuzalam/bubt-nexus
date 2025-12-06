<?php

use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\NotesController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoutineController;
use App\Http\Controllers\Admin\RoutineScraperController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use Illuminate\Support\Facades\Route;




// Public Routes
Route::get('/', [PublicHomeController::class, 'index'])->name('home');

// Super Admin, Admin, User Dashboard Routes
Route::middleware(['auth', 'verified', 'role:super_admin|admin|user|faculty|student'])->prefix('admin-dashboard')->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    // Routine Management Routes
    Route::resource('routines', RoutineController::class)->names('admin.routines');
    Route::post('routines/bulk-destroy', [RoutineController::class, 'bulkDestroy'])->name('admin.routines.bulk-destroy');
    Route::get('routines/export', [RoutineController::class, 'export'])->name('admin.routines.export');
    Route::get('routines/filter-options', [RoutineController::class, 'getFilterOptions'])->name('admin.routines.filter-options');
});

// Super Admin, Admin, User Dashboard Routes
Route::middleware(['auth', 'verified', 'role:super_admin|admin'])->prefix('admin-dashboard')->group(function () {

    Route::get('/notes', [NotesController::class, 'index'])->name('admin.notes.index');
    Route::post('/notes', [NotesController::class, 'store'])->name('admin.notes.store');
    Route::delete('/notes/{note}', [NotesController::class, 'destroy'])->name('admin.notes.destroy');

    Route::prefix('scraper')->group(function () {
        Route::get('/program/{code}', [RoutineScraperController::class, 'scrapeProgram']);
        Route::get('/status', [RoutineScraperController::class, 'getStatus']);
    });

    Route::get('/api-documentation', [ApiController::class, 'index'])->name('admin.api.index');

    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
});

// Super Admin Dashboard Routes
Route::middleware(['auth', 'verified', 'role:super_admin'])->prefix('admin-dashboard')->group(function () {

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


/*
Route::middleware(['permission:manage users'])->group(function () {
    // Additional routes that require 'manage users' permission can be added here
});
*/



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
