<?php

use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use Illuminate\Support\Facades\Route;




// Public Routes
Route::get('/', [PublicHomeController::class, 'index'])->name('home');



// Super Admin, Admin, User Dashboard Routes
Route::middleware(['auth', 'verified', 'role:super_admin|admin|user'])->prefix('admin-dashboard')->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    Route::get('/api-documentation', [ApiController::class, 'index'])->name('admin.api.index');

    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('admin.settings.update');

});


// Super Admin Dashboard Routes
Route::middleware(['auth', 'verified', 'role:super_admin'])->prefix('admin-dashboard')->group(function () {
    
    Route::resource('roles', RoleController::class)->names('admin.roles');
    // Additional permission routes
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
