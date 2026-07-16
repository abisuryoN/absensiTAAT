<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Student\StudentPortalController;
use App\Http\Controllers\Teacher\TeacherPortalController;
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
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AdminDashboardController;

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
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

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
        Route::post('/holidays/sync', [HolidayController::class, 'sync'])->name('holidays.sync');

        // Laporan & Reporting
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
        Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');

        // System Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Excel Imports
        Route::get('/imports', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('imports.index');
        Route::get('/imports/template/{type}', [\App\Http\Controllers\Admin\ImportController::class, 'downloadTemplate'])->name('imports.template');
        Route::post('/imports/preview', [\App\Http\Controllers\Admin\ImportController::class, 'preview'])->name('imports.preview');
        Route::post('/imports/commit', [\App\Http\Controllers\Admin\ImportController::class, 'commit'])->name('imports.commit');
        Route::post('/imports/cancel', [\App\Http\Controllers\Admin\ImportController::class, 'cancel'])->name('imports.cancel');

        // Absensi Gerbang
        Route::get('/attendance/scan', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'index'])->name('attendance.scan');
        Route::post('/attendance/scan', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'scan'])->name('attendance.scan.post');
        Route::get('/attendance/today', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'today'])->name('attendance.today');
        Route::get('/attendance/manual', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'manualIndex'])->name('attendance.manual');
        Route::post('/attendance/manual', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'manualStore'])->name('attendance.manual.post');
    });

// 2. Guru Group
Route::middleware(['auth', 'active', 'role:guru'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', [TeacherPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/schedules', [TeacherPortalController::class, 'schedules'])->name('schedules');
        
        // Absensi Mapel
        Route::get('/attendance/{schedule}', [TeacherPortalController::class, 'attendanceInput'])->name('attendance.input');
        Route::post('/attendance/store', [TeacherPortalController::class, 'attendanceStore'])->name('attendance.store');
        
        // Rekap Mengajar
        Route::get('/recap', [TeacherPortalController::class, 'recap'])->name('recap');
    });

// 3. Siswa Group
Route::middleware(['auth', 'active', 'role:siswa'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        // Dashboard with live QR Code
        Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');

        // QR Code AJAX endpoint
        Route::get('/qr-code', [StudentPortalController::class, 'dashboard'])->name('qrcode');
        Route::post('/qr-code/generate', [StudentPortalController::class, 'generateQr'])->name('qrcode.generate');

        // Jadwal Pelajaran
        Route::get('/schedule', [StudentPortalController::class, 'schedule'])->name('schedule');

        // Riwayat Kehadiran
        Route::get('/history', [StudentPortalController::class, 'history'])->name('history');
    });

require __DIR__.'/auth.php';
