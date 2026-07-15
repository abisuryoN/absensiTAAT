<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\SettingController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Main Dashboard redirector
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'active'])
    ->name('dashboard');

// Auth Profile
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 1. Super Admin Group (Operator TU)
Route::middleware(['auth', 'active', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // Master Data CRUD Resources
        Route::resource('academic-years', AcademicYearController::class);
        Route::resource('semesters', SemesterController::class);
        Route::resource('majors', MajorController::class);
        Route::resource('classes', SchoolClassController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('teachers', TeacherController::class);
        Route::resource('parents', ParentController::class);
        Route::resource('students', StudentController::class);
        Route::resource('schedules', ScheduleController::class);
        Route::resource('holidays', HolidayController::class);

        // System Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Excel Imports
        Route::get('/imports', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('imports.index');
        Route::post('/imports/preview', [\App\Http\Controllers\Admin\ImportController::class, 'preview'])->name('imports.preview');
        Route::post('/imports/commit', [\App\Http\Controllers\Admin\ImportController::class, 'commit'])->name('imports.commit');
        Route::post('/imports/cancel', [\App\Http\Controllers\Admin\ImportController::class, 'cancel'])->name('imports.cancel');
    });

// 2. Guru Group
Route::middleware(['auth', 'active', 'role:guru'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('teacher.dashboard');
        })->name('dashboard');

        // Absensi Mapel routes will go here in Fase 5
    });

// 3. Siswa Group
Route::middleware(['auth', 'active', 'role:siswa'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('student.dashboard');
        })->name('dashboard');

        // QR Code routes will go here in Fase 4
    });

require __DIR__.'/auth.php';
