# Dokumentasi Struktur Proyek Absensi TAAT

## 📁 ROOT — File Utama

| File | Fungsi |
|------|--------|
| `artisan` | CLI Laravel — menjalankan migrate, seed, dll |
| `composer.json` | Daftar dependency PHP (Laravel, Spatie, DomPDF, dll) |
| `package.json` | Daftar dependency frontend (Tailwind, Vite, Alpine.js) |
| `vite.config.js` | Bundler frontend (menggantikan Mix) |
| `tailwind.config.js` | Konfigurasi Tailwind CSS |
| `postcss.config.js` | Processor CSS |
| `.env.example` | Template environment variable |
| `.editorconfig` | Aturan coding style standar |
| `phpunit.xml` | Konfigurasi testing PHPUnit |

---

## 📁 app/ — Jantung Aplikasi

### app/Http/Controllers/

| File | Fungsi |
|------|--------|
| `Controller.php` | Base class semua controller |
| `DashboardController.php` | Redirect user ke dashboard sesuai role |
| `ProfileController.php` | Edit profil, ganti password, upload foto |

### app/Http/Controllers/Auth/ — Login & Registrasi

| File | Fungsi |
|------|--------|
| `AuthenticatedSessionController.php` | Proses login |
| `RegisteredUserController.php` | Register user baru (sudah dinonaktifkan) |
| `PasswordController.php` | Ganti password (setelah login) |
| `PasswordResetLinkController.php` | Lupa password → kirim link reset |
| `NewPasswordController.php` | Reset password via token |
| `VerifyEmailController.php` | Verifikasi email |
| `ConfirmablePasswordController.php` | Konfirmasi password sebelum aksi sensitif |
| `EmailVerificationNotificationController.php` | Kirim ulang notifikasi verifikasi |
| `EmailVerificationPromptController.php` | Halaman pengingat verifikasi email |

### app/Http/Controllers/Admin/ — Panel Super Admin

| Controller | Fungsi |
|------------|--------|
| `AdminDashboardController.php` | Dashboard admin dengan statistik dan grafik |
| `StudentController.php` | CRUD data siswa |
| `TeacherController.php` | CRUD data guru |
| `SubjectController.php` | CRUD mata pelajaran |
| `SchoolClassController.php` | CRUD kelas |
| `MajorController.php` | CRUD jurusan (RPL, AKL, BDP, dll) |
| `ParentController.php` | CRUD orang tua/wali |
| `ScheduleController.php` | CRUD jadwal pelajaran |
| `SemesterController.php` | CRUD semester |
| `AcademicYearController.php` | CRUD tahun ajaran |
| `AcademicYearTransitionController.php` | Proses naik kelas (X→XI, XI→XII, XII→Lulus) |
| `AttendanceGateController.php` | Absensi gerbang — scan QR/barcode, rekap, export |
| `ImportController.php` | Import data dari Excel |
| `ReportController.php` | Laporan absensi (PDF/Excel) |
| `SettingController.php` | Pengaturan aplikasi |
| `AccountManagementController.php` | Manajemen semua akun + reset password massal |
| `SuperAdminController.php` | Kelola akun super admin lain |
| `GuruPiketAccountController.php` | Kelola akun guru piket |
| `ActivityLogController.php` | Log aktivitas semua user |
| `HolidayController.php` | Hari libur nasional (sinkronisasi API) |
| `SchoolHolidayController.php` | Libur khusus sekolah |

### app/Http/Controllers/Student/

| File | Fungsi |
|------|--------|
| `StudentPortalController.php` | Dashboard siswa, QR code absen, jadwal, riwayat kehadiran |

### app/Http/Controllers/Teacher/

| File | Fungsi |
|------|--------|
| `TeacherPortalController.php` | Dashboard guru, absensi mapel, rekap |

### app/Http/Controllers/Parent/

| File | Fungsi |
|------|--------|
| `ParentPortalController.php` | Dashboard orang tua, pantau anak, rekap harian/bulanan |

### app/Http/Controllers/GuruPiket/

| File | Fungsi |
|------|--------|
| `GuruPiketController.php` | Scan absen di gerbang (khusus jam piket) |

---

### app/Http/Middleware/

| Middleware | Fungsi |
|------------|--------|
| `EnsureActiveUser.php` | Cek status aktif user — jika nonaktif, logout paksa |
| `EnsureGuruPiketSetup.php` | Pastikan guru piket mengisi identitas sebelum scan |

### app/Http/Requests/ — Form Validation

| File | Validasi Untuk |
|------|----------------|
| `LoginRequest.php` | Login |
| `StudentRequest.php` | Create/update siswa |
| `TeacherRequest.php` | Create/update guru |
| `ParentRequest.php` | Create/update orang tua |
| `SubjectRequest.php` | Mata pelajaran |
| `SchoolClassRequest.php` | Kelas |
| `MajorRequest.php` | Jurusan |
| `ScheduleRequest.php` | Jadwal |
| `SemesterRequest.php` | Semester |
| `AcademicYearRequest.php` | Tahun ajaran |
| `HolidayRequest.php` | Hari libur |
| `ProfileUpdateRequest.php` | Update profil |

---

### app/Models/ — Database ORM (Eloquent)

| Model | Tabel | Fungsi |
|-------|-------|--------|
| `User.php` | `users` | User login multi-role (Spatie Permission) |
| `Student.php` | `students` | Data siswa dengan relasi user + parent |
| `Teacher.php` | `teachers` | Data guru |
| `StudentParent.php` | `parents` | Data orang tua/wali |
| `SchoolClass.php` | `classes` | Kelas (X RPL 1, dll) |
| `Major.php` | `majors` | Jurusan (RPL, AKL, BDP, dll) |
| `Subject.php` | `subjects` | Mata pelajaran |
| `Schedule.php` | `schedules` | Jadwal pelajaran |
| `AttendanceGate.php` | `attendance_gates` | Absensi gerbang (tap in) |
| `AttendanceSubject.php` | `attendance_subjects` | Absensi mata pelajaran (di kelas) |
| `AttendanceSubjectDetail.php` | `attendance_subject_details` | Detail absensi per siswa per mapel |
| `AcademicYear.php` | `academic_years` | Tahun ajaran |
| `Semester.php` | `semesters` | Semester ganjil/genap |
| `QrToken.php` | `qr_tokens` | Token QR (expired 30 detik) |
| `ActivityLog.php` | `activity_logs` | Log aktivitas user |
| `Setting.php` | `settings` | Pengaturan sistem |
| `Holiday.php` | `holidays` | Libur nasional |
| `SchoolHoliday.php` | `school_holidays` | Libur khusus sekolah |
| `Device.php` | `devices` | Perangkat terdaftar |
| `PetugasPiket.php` | `petugas_piket` | Sesi piket guru |
| `AttendanceRule.php` | `attendance_rules` | Aturan absensi (batas telat) |
| `Notification.php` | `notifications` | Notifikasi sistem |
| `WhatsappTemplate.php` | `whatsapp_templates` | Template pesan WhatsApp |
| `Import.php` | `imports` | Riwayat import Excel |
| `ClassStudentHistory.php` | `class_student_history` | Riwayat perpindahan kelas siswa |
| `SchoolProfile.php` | `school_profiles` | Profil sekolah (nama, npsn, logo) |

### app/Policies/ — Otorisasi

| Policy | Fungsi |
|--------|--------|
| `StudentParentPolicy.php` | Cek apakah ortu berhak lihat data anaknya |
| `StudentPolicy.php` | Authorisasi data siswa (super_admin only) |
| `TeacherPolicy.php` | Authorisasi data guru (super_admin only) |
| `SchedulePolicy.php` | Authorisasi jadwal (guru lihat jadwal sendiri, siswa lihat kelasnya) |

---

### app/Services/ — Business Logic

| Service | Fungsi |
|---------|--------|
| `StudentService.php` | Query + logic siswa |
| `TeacherService.php` | Query + logic guru |
| `ParentService.php` | Query + logic orang tua |
| `ScheduleService.php` | Query jadwal |
| `SchoolClassService.php` | Query kelas |
| `MajorService.php` | Query jurusan |
| `SubjectService.php` | Query mapel |
| `SemesterService.php` | Query semester |
| `AcademicYearService.php` | Logic tahun ajaran |
| `AcademicYearTransitionService.php` | Proses naik kelas |
| `AttendanceGateService.php` | Logic absensi gerbang |
| `AttendanceSubjectService.php` | Logic absensi mapel |
| `HolidayService.php` | Logic hari libur |
| `ImportService.php` | Parse + validasi + import Excel |
| `PasswordGeneratorService.php` | Generate password default |
| `QrTokenService.php` | Generate + validasi QR token |
| `ActivityLogService.php` | Catat log aktivitas |
| `WhatsAppService.php` | Kirim notifikasi WhatsApp |
| `LiburApiService.php` | Sinkronisasi libur nasional dari API |

### app/Jobs/

| File | Fungsi |
|------|--------|
| `SendWhatsAppNotificationJob.php` | Queue kirim WhatsApp (biar tidak blocking) |

### app/Console/Commands/

| Command | Fungsi |
|---------|--------|
| `CleanupExpiredQrTokens.php` | Hapus QR token yang sudah expired |

### app/Exports/

| File | Fungsi |
|------|--------|
| `AttendanceGateExport.php` | Export absensi gerbang ke Excel |
| `AttendanceSubjectExport.php` | Export absensi mapel ke Excel |
| `ParentExport.php` | Export data orang tua ke Excel |
| `ParentReferenceExport.php` | Export referensi orang tua |
| `ImportErrorReportExport.php` | Export laporan error import |

**Templates/** — Template Excel untuk import:
- `StudentImportTemplate.php`
- `TeacherImportTemplate.php`
- `ClassImportTemplate.php`
- `ScheduleImportTemplate.php`
- `StudentImportWithParentsExport.php`
- `ParentReferenceSheet.php`
- `StudentImportSheet.php`

---

## 📁 config/ — Konfigurasi Aplikasi

| File | Fungsi |
|------|--------|
| `app.php` | Konfigurasi umum Laravel |
| `auth.php` | Setting authentication |
| `database.php` | Koneksi database |
| `session.php` | Session (secure, http_only, encrypt) |
| `permission.php` | Konfigurasi Spatie Permission |
| `mail.php` | Setting email |
| `queue.php` | Queue untuk WA notification |
| `filesystems.php` | Storage local/public |
| `services.php` | API keys layanan eksternal |
| `absensi.php` | Config custom aplikasi (QR ttl, jam masuk) |
| `whatsapp.php` | Config WhatsApp gateway |
| `logging.php` | Setting log |
| `cache.php` | Cache driver |
| `laravel-webp.php` | Webp image converter |

---

## 📁 database/

| Folder | Fungsi |
|--------|--------|
| `migrations/` | Riwayat struktur database (42 file) |
| `seeders/` | Data dummy/awal untuk testing |
| `factories/UserFactory.php` | Factory data user untuk testing |

---

## 📁 routes/ — Endpoint URL

| File | Fungsi |
|------|--------|
| `web.php` | Semua route web (admin, guru, siswa, parent, piket) |
| `auth.php` | Route auth (login, register, forgot password) |
| `console.php` | Command artisan custom |

---

## 📁 resources/views/ — Tampilan (Blade)

| Folder | Halaman |
|--------|---------|
| `auth/` | Login, register, forgot password, reset password, verify email |
| `layouts/` | Layout utama, sidebar, navbar |
| `layouts/partials/` | Sidebar, navbar, mobile drawer, mobile bottom nav |
| `components/` | Komponen reusable (button, input, modal, dropdown) |
| `admin/` | Semua halaman CRUD admin + dashboard |
| `admin/academic-years/transition/` | Proses naik kelas |
| `admin/attendance/` | Scan, manual, today, export PDF |
| `admin/reports/` | Laporan absensi PDF |
| `admin/imports/` | Import Excel |
| `student/` | Dashboard siswa, QR code, jadwal, riwayat |
| `teacher/` | Absensi mapel, jadwal, rekap |
| `parent/` | Dashboard orang tua, rekap harian/bulanan |
| `guru-piket/` | Scan, rekap, setup piket |
| `profile/` | Edit profil, ganti password, upload foto |
| `profile/partials/` | Form update profil |

---

## 📁 Role & Alur Aplikasi

### Role Pengguna
1. **Super Admin** — Mengelola seluruh data master (siswa, guru, kelas, jurusan, jadwal, import, laporan)
2. **Guru** — Mengisi absensi mata pelajaran, melihat jadwal mengajar
3. **Siswa** — Melihat QR code untuk absen, jadwal, riwayat kehadiran
4. **Parent (Orang Tua)** — Memantau kehadiran anak, rekap harian/bulanan
5. **Guru Piket** — Scan absensi di gerbang sekolah (sesi piket)

### Alur Absensi
**Gerbang:** Siswa scan QR → Guru Piket scan barcode → Tercatat di attendance_gates
**Mapel:** Guru buka absen di kelas → Siswa dicatat → Masuk attendance_subjects

### Fitur Unggulan
- ✅ Multi-role dengan Spatie Permission
- ✅ QR Token (expired 30 detik, one-time use)
- ✅ Barcode scan via NIS
- ✅ Naik kelas otomatis (X→XI→XII→Lulus)
- ✅ Import data massal dari Excel
- ✅ Export laporan PDF & Excel
- ✅ Notifikasi WhatsApp (via Fonnte/queue)
- ✅ Activity log semua aksi
- ✅ Session terenkripsi
- ✅ Sinkronisasi hari libur nasional dari API
- ✅ Soft Delete + restore data
