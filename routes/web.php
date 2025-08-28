<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Main Web Routes
|--------------------------------------------------------------------------
|
| This file contains the core route definitions for EnrollAssess.
| Route organization is split into logical modules for better maintainability.
|
*/

// Public Routes (no authentication required)
require __DIR__.'/public.php';

// Authentication Routes
require __DIR__.'/auth.php';

// Protected Admin Routes (Department Head role required)
Route::middleware(['auth', 'role:department-head'])->prefix('admin')->name('admin.')->group(function () {
    require __DIR__.'/admin.php';
});

// Protected Instructor Routes (Instructor role required)
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    require __DIR__.'/instructor.php';
});
