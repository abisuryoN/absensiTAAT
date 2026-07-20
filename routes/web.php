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
use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\SuperAdminController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\GuruPiketAccountController;
use App\Http\Controllers\Parent\ParentPortalController;
use App\Http\Controllers\GuruPiket\GuruPiketController;

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
        Route::post('/academic-year/switch', [AdminDashboardController::class, 'switchAcademicYear'])->name('academic-year.switch');

        // Master Data CRUD Resources
        // Academic Year Transition Routes (MUST come BEFORE resource to avoid route conflict)
        Route::prefix('academic-years/transition')->name('academic-years.transition.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'index'])->name('index');

            // Grade 10 ke 11 (dengan penjurusan)
            Route::get('/grade10-to-11', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'grade10To11'])->name('grade10-to-11');
            Route::get('/get-grade11-classes', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'getGrade11Classes'])->name('get-grade11-classes');
            Route::post('/process-grade10-to-11', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'processGrade10To11'])->name('process-grade10-to-11');

            // Grade 11 ke 12 (auto-mapping jurusan)
            Route::get('/grade11-to-12', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'grade11To12'])->name('grade11-to-12');
            Route::post('/process-grade11-to-12', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'processGrade11To12'])->name('process-grade11-to-12');

            // Grade 12 kelulusan
            Route::get('/grade12-graduate', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'grade12Graduate'])->name('grade12-graduate');
            Route::post('/process-grade12-graduate', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'processGrade12Graduate'])->name('process-grade12-graduate');

            // Finalisasi & aktivasi tahun ajaran baru
            Route::get('/finalize', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'finalize'])->name('finalize');
            Route::post('/activate-new-year', [\App\Http\Controllers\Admin\AcademicYearTransitionController::class, 'activateNewYear'])->name('activate-new-year');
        });
        
        Route::resource('academic-years', AcademicYearController::class);
        Route::resource('semesters', SemesterController::class);
        Route::resource('majors', MajorController::class);
        Route::resource('classes', SchoolClassController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('teachers', TeacherController::class);
        // Custom parent routes MUST come before resource to avoid {parent} wildcard matching
        Route::get('/parents/picker-search', [ParentController::class, 'pickerSearch'])->name('parents.picker');
        Route::get('/parents/export-reference', [ParentController::class, 'exportReference'])->name('parents.export');
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

        // Manajemen Akun Login
        Route::get('/accounts', [AccountManagementController::class, 'index'])->name('accounts.index');
        Route::post('/accounts/reset-password', [AccountManagementController::class, 'resetPassword'])->name('accounts.reset-password');

        // Manajemen Super Admin
        Route::get('/super-admins', [SuperAdminController::class, 'index'])->name('super-admins.index');
        Route::get('/super-admins/create', [SuperAdminController::class, 'create'])->name('super-admins.create');
        Route::post('/super-admins', [SuperAdminController::class, 'store'])->name('super-admins.store');
        Route::patch('/super-admins/{superAdmin}/toggle-active', [SuperAdminController::class, 'toggleActive'])->name('super-admins.toggle-active');

        // Manajemen Akun Guru Piket
        Route::get('/guru-piket-accounts', [GuruPiketAccountController::class, 'index'])->name('guru-piket-accounts.index');
        Route::get('/guru-piket-accounts/create', [GuruPiketAccountController::class, 'create'])->name('guru-piket-accounts.create');
        Route::post('/guru-piket-accounts', [GuruPiketAccountController::class, 'store'])->name('guru-piket-accounts.store');
        Route::patch('/guru-piket-accounts/{guruPiketAccount}/toggle-active', [GuruPiketAccountController::class, 'toggleActive'])->name('guru-piket-accounts.toggle-active');
        Route::delete('/guru-piket-accounts/{guruPiketAccount}', [GuruPiketAccountController::class, 'destroy'])->name('guru-piket-accounts.destroy');

        // Log Aktivitas
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/modules-by-role', [ActivityLogController::class, 'modulesByRole'])->name('activity-logs.modules-by-role');

        // Absensi Gerbang
        Route::get('/attendance/scan', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'index'])->name('attendance.scan');
        Route::post('/attendance/scan', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'scan'])->name('attendance.scan.post');
        Route::get('/attendance/today', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'today'])->name('attendance.today');
        Route::get('/attendance/manual', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'manualIndex'])->name('attendance.manual');
        Route::post('/attendance/manual', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'manualStore'])->name('attendance.manual.post');
        Route::post('/attendance/bulk-mark', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'bulkMark'])->name('attendance.bulk-mark');
        Route::get('/attendance/export', [\App\Http\Controllers\Admin\AttendanceGateController::class, 'exportAttendance'])->name('attendance.export');
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

// 4. Parent / Wali Group
Route::middleware(['auth', 'active', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard', [ParentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/rekap-harian', [ParentPortalController::class, 'rekapHarian'])->name('rekap_harian');
        Route::get('/rekap-bulanan', [ParentPortalController::class, 'rekapBulanan'])->name('rekap_bulanan');
    });

// 5. Guru Piket Group (shared account, multi-session)
// Setup routes: tidak pakai middleware piket.setup supaya bisa akses form isi nama
Route::middleware(['auth', 'active', 'role:guru_piket'])
    ->prefix('piket')
    ->name('piket.')
    ->group(function () {
        // Setup nama piket untuk sesi ini (wajib sebelum akses halaman lain)
        Route::get('/setup', [GuruPiketController::class, 'setup'])->name('setup');
        Route::post('/setup', [GuruPiketController::class, 'setupStore'])->name('setup.post');

        // Halaman yang butuh nama piket sudah diisi (dijaga middleware piket.setup)
        Route::middleware(['piket.setup'])->group(function () {
            Route::get('/dashboard', [GuruPiketController::class, 'dashboard'])->name('dashboard');
            Route::get('/scan', [GuruPiketController::class, 'scan'])->name('scan');
            Route::post('/scan', [GuruPiketController::class, 'scanPost'])->name('scan.post');
            Route::get('/rekap', [GuruPiketController::class, 'rekap'])->name('rekap');
            Route::post('/end-session', [GuruPiketController::class, 'endSession'])->name('end-session');
        });
    });

require __DIR__.'/auth.php';
