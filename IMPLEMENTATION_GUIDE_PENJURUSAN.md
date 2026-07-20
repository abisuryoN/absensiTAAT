# Panduan Implementasi Fitur Penjurusan - Kenaikan Kelas

## Status Implementasi

### ✅ Sudah Selesai:
1. **Database Migration** - `database/migrations/2026_07_20_120000_add_major_id_to_classes_table.php`
   - Menambahkan kolom `major_id` ke tabel `classes`
   - NULL untuk kelas 10, wajib diisi untuk kelas 11 & 12

2. **Service Layer** - `app/Services/AcademicYearTransitionService.php`
   - getGrade10StudentsForMajorSelection()
   - processGrade10To11Transition()
   - getGrade11StudentsWithAutoMapping()
   - processGrade11To12Transition()
   - getGrade12StudentsForGraduation()
   - processGrade12ToGraduateTransition()

3. **Controller** - `app/Http/Controllers/Admin/AcademicYearTransitionController.php`
   - index()
   - grade10To11() & processGrade10To11()
   - getGrade11Classes() (AJAX)
   - grade11To12() & processGrade11To12()
   - grade12Graduate() & processGrade12Graduate()
   - finalize() & activateNewYear()

4. **Views** (Partial):
   - ✅ `resources/views/admin/academic-years/transition/index.blade.php`
   - ✅ `resources/views/admin/academic-years/transition/grade10-to-11.blade.php`

### ❌ Belum Selesai:

#### 1. View Files yang Masih Perlu Dibuat:

**A. `resources/views/admin/academic-years/transition/grade11-to-12.blade.php`**
```blade
@extends('layouts.app')
@section('content')
<!-- Tampilkan daftar siswa kelas 11 dengan auto-mapping ke kelas 12 -->
<!-- Format: Tabel dengan kolom: NIS, Nama, Kelas Saat Ini (11), Kelas Usulan (12), Kelas Tujuan (dropdown editabel) -->
<!-- Auto-suggest berdasarkan jurusan yang sama -->
<!-- Tombol: Kembali | Simpan & Lanjut ke Langkah 3 -->
@endsection
```

**B. `resources/views/admin/academic-years/transition/grade12-graduate.blade.php`**
```blade
@extends('layouts.app')
@section('content')
<!-- Tampilkan daftar siswa kelas 12 untuk kelulusan -->
<!-- Format: Tabel dengan kolom: NIS, Nama, Kelas, Status (dropdown: Lulus/Tinggal Kelas), Kelas Tujuan (jika Tinggal Kelas) -->
<!-- Default status: Lulus -->
<!-- Tombol: Kembali | Simpan & Lanjut ke Finalisasi -->
@endsection
```

**C. `resources/views/admin/academic-years/transition/finalize.blade.php`**
```blade
@extends('layouts.app')
@section('content')
<!-- Ringkasan proses transisi -->
<!-- Konfirmasi untuk aktivasi tahun ajaran baru -->
<!-- Tombol: Kembali | Aktifkan Tahun Ajaran Baru -->
@endsection
```

#### 2. Routes yang Perlu Ditambahkan ke `routes/web.php`:

```php
// Tambahkan di dalam Route::middleware(['auth'])->group:
Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
    
    // Academic Year Transition Routes
    Route::prefix('academic-years/transition')->name('academic-years.transition.')->group(function () {
        Route::get('/', [AcademicYearTransitionController::class, 'index'])->name('index');
        
        // Grade 10 → 11
        Route::get('/grade10-to-11', [AcademicYearTransitionController::class, 'grade10To11'])->name('grade10-to-11');
        Route::get('/get-grade11-classes', [AcademicYearTransitionController::class, 'getGrade11Classes'])->name('get-grade11-classes');
        Route::post('/process-grade10-to-11', [AcademicYearTransitionController::class, 'processGrade10To11'])->name('process-grade10-to-11');
        
        // Grade 11 → 12
        Route::get('/grade11-to-12', [AcademicYearTransitionController::class, 'grade11To12'])->name('grade11-to-12');
        Route::post('/process-grade11-to-12', [AcademicYearTransitionController::class, 'processGrade11To12'])->name('process-grade11-to-12');
        
        // Grade 12 → Graduate
        Route::get('/grade12-graduate', [AcademicYearTransitionController::class, 'grade12Graduate'])->name('grade12-graduate');
        Route::post('/process-grade12-graduate', [AcademicYearTransitionController::class, 'processGrade12Graduate'])->name('process-grade12-graduate');
        
        // Finalize
        Route::get('/finalize', [AcademicYearTransitionController::class, 'finalize'])->name('finalize');
        Route::post('/activate-new-year', [AcademicYearTransitionController::class, 'activateNewYear'])->name('activate-new-year');
    });
});
```

#### 3. Model Updates:

**A. Update `app/Models/SchoolClass.php`** - Tambahkan relationship ke Major:
```php
public function major()
{
    return $this->belongsTo(Major::class);
}
```

**B. Update `app/Models/Student.php`** - Pastikan relationship schoolClass ada:
```php
public function schoolClass()
{
    return $this->belongsTo(SchoolClass::class, 'class_id');
}
```

#### 4. Sidebar Menu:

Tambahkan link ke menu admin untuk akses fitur transisi tahun ajaran di `resources/views/layouts/partials/sidebar-menu-items.blade.php`:
```blade
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.academic-years.transition.*') ? 'active' : '' }}" 
       href="{{ route('admin.academic-years.transition.index') }}">
        <i class="bi bi-arrow-repeat"></i>
        <span>Mulai Tahun Ajaran Baru</span>
    </a>
</li>
```

#### 5. Testing Checklist:

- [ ] Jalankan migration: `php artisan migrate`
- [ ] Buat data sample (tahun ajaran, kelas 10/11/12, jurusan, siswa)
- [ ] Test proses Grade 10 → 11:
  - [ ] Tampilan daftar siswa kelas 10
  - [ ] Fitur batch assignment
  - [ ] Individual assignment
  - [ ] Validasi form
  - [ ] Proses simpan
- [ ] Test proses Grade 11 → 12:
  - [ ] Auto-mapping berdasarkan jurusan
  - [ ] Edit manual jika perlu
  - [ ] Proses simpan
- [ ] Test proses Grade 12 → Graduate:
  - [ ] Default status Lulus
  - [ ] Opsi Tinggal Kelas
  - [ ] Proses simpan
- [ ] Test finalisasi:
  - [ ] Aktivasi tahun ajaran baru
  - [ ] Verifikasi data siswa ter-update
  - [ ] Verifikasi tahun ajaran aktif berubah

## Catatan Penting:

1. **Format Nama Kelas:**
   - Kelas 10: "X.1", "X.2", dll (tanpa jurusan, major_id = NULL)
   - Kelas 11: "XI RPL 1", "XI TKJ 2", dll (dengan jurusan, major_id != NULL)
   - Kelas 12: "XII RPL 1", "XII TKJ 2", dll (dengan jurusan, major_id != NULL)

2. **Validasi:**
   - Pastikan semua siswa kelas 10 sudah memilih jurusan & kelas sebelum bisa lanjut
   - Kelas tujuan harus sesuai dengan grade level yang benar
   - Major_id kelas tujuan harus match dengan major yang dipilih

3. **Error Handling:**
   - Tangani case dimana tidak ada kelas tersedia untuk jurusan tertentu
   - Tangani case student/class not found
   - Log semua error ke Laravel log

4. **UX Improvements:**
   - Tambahkan loading indicator saat AJAX call
   - Tambahkan konfirmasi sebelum submit
   - Tampilkan progress indicator di setiap step
   - Success/error messages yang jelas

## File Yang Perlu Dibuat Selanjutnya:

1. `resources/views/admin/academic-years/transition/grade11-to-12.blade.php`
2. `resources/views/admin/academic-years/transition/grade12-graduate.blade.php`
3. `resources/views/admin/academic-years/transition/finalize.blade.php`

Setelah file-file view ini dibuat dan routes ditambahkan, sistem penjurusan siap untuk testing!