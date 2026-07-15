<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

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

        // Master Data routes will go here in Fase 2
        // Absensi Gerbang routes will go here in Fase 3
        // Report routes will go here in Fase 6
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
