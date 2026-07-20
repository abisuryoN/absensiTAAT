# Database Seeder — SMAN 1 Tajurhalang (Demo / Testing)

> **PERINGATAN:** Seeder ini untuk keperluan **testing & demo** saja.  
> Semua password testing adalah `password123` — BUKAN password produksi.  
> Untuk produksi, gunakan fitur reset password yang sudah didesain dengan pola  
> NIS/NISN/NIK yang berbeda.

---

## Cara Menjalankan

```bash
# Fresh install (hapus semua data + migrasi ulang + seed)
php artisan migrate:fresh --seed

# Hanya seed ulang (tanpa reset migrasi)
php artisan db:seed

# Seed seeder tertentu saja
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=AttendanceSeeder
```

---

## Akun Default untuk Testing

### Super Admin (2 akun)

| Email | Password | Keterangan |
|-------|----------|------------|
| `superadmin1@sman1tajurhalang.sch.id` | `password123` | Kepala Sekolah |
| `superadmin2@sman1tajurhalang.sch.id` | `password123` | Operator TU |

---

### Guru Piket (1 akun shared)

| Email | Password | Keterangan |
|-------|----------|------------|
| `piket@sman1tajurhalang.sch.id` | `password123` | Akun bersama petugas piket gerbang |

---

### Guru (28 akun — format: `guru.[nama]@sman1tajurhalang.sch.id`)

| Email | Password | Nama Guru | Wali Kelas |
|-------|----------|-----------|------------|
| `guru.budi.santoso@sman1tajurhalang.sch.id` | `password123` | Budi Santoso, S.Pd. | X-1 |
| `guru.siti.rahayu@sman1tajurhalang.sch.id` | `password123` | Siti Rahayu, S.Pd. | X-2 |
| `guru.ahmad.fauzi@sman1tajurhalang.sch.id` | `password123` | Ahmad Fauzi, M.Pd. | X-3 |
| `guru.dewi.kusuma@sman1tajurhalang.sch.id` | `password123` | Dewi Kusuma, S.Pd. | X-4 |
| `guru.hendra.saputra@sman1tajurhalang.sch.id` | `password123` | Hendra Saputra, S.Pd. | X-5 |
| `guru.nurul.hidayah@sman1tajurhalang.sch.id` | `password123` | Nurul Hidayah, S.Pd. | X-6 |
| `guru.joko.prasetyo@sman1tajurhalang.sch.id` | `password123` | Joko Prasetyo, M.Si. | X-7 |
| `guru.sri.wahyuni@sman1tajurhalang.sch.id` | `password123` | Sri Wahyuni, S.Pd. | X-8 |
| `guru.bambang.sumarno@sman1tajurhalang.sch.id` | `password123` | Bambang Sumarno, S.Pd. | XI IPA 1 |
| `guru.rina.astuti@sman1tajurhalang.sch.id` | `password123` | Rina Astuti, S.Pd.I. | XI IPA 2 |
| `guru.wahyu.hidayat@sman1tajurhalang.sch.id` | `password123` | Wahyu Hidayat, S.Pd. | XI IPA 3 |
| `guru.fitri.handayani@sman1tajurhalang.sch.id` | `password123` | Fitri Handayani, S.Sn. | XI IPA 4 |
| `guru.agus.supriyanto@sman1tajurhalang.sch.id` | `password123` | Agus Supriyanto, S.Pd. | XI IPS 1 |
| `guru.yuli.setiawati@sman1tajurhalang.sch.id` | `password123` | Yuli Setiawati, S.Pd. | XI IPS 2 |
| `guru.dian.pratiwi@sman1tajurhalang.sch.id` | `password123` | Dian Pratiwi, M.Sc. | XI IPS 3 |
| `guru.sigit.wibowo@sman1tajurhalang.sch.id` | `password123` | Sigit Wibowo, S.Pd. | XI Bahasa 1 |
| `guru.endah.sulistyo@sman1tajurhalang.sch.id` | `password123` | Endah Sulistyowati, M.Pd. | XII IPA 1 |
| `guru.eko.purnomo@sman1tajurhalang.sch.id` | `password123` | Eko Purnomo, S.Pd. | XII IPA 2 |
| `guru.lia.kusumawati@sman1tajurhalang.sch.id` | `password123` | Lia Kusumawati, S.Pd. | XII IPA 3 |
| `guru.rudi.hartono@sman1tajurhalang.sch.id` | `password123` | Rudi Hartono, S.E., S.Pd. | XII IPA 4 |
| `guru.heni.ratnasari@sman1tajurhalang.sch.id` | `password123` | Heni Ratnasari, S.Pd. | XII IPS 1 |
| `guru.darman.purwanto@sman1tajurhalang.sch.id` | `password123` | Darman Purwanto, S.Pd. | XII IPS 2 |
| `guru.suci.indrawati@sman1tajurhalang.sch.id` | `password123` | Suci Indrawati, S.Pd. | XII IPS 3 |
| `guru.taufik.hidayat@sman1tajurhalang.sch.id` | `password123` | Taufik Hidayat, S.Pd.I. | XII Bahasa 1 |
| `guru.maya.sari@sman1tajurhalang.sch.id` | `password123` | Maya Sari, S.Sn. | *(tidak wali kelas)* |
| `guru.irwan.santoso@sman1tajurhalang.sch.id` | `password123` | Irwan Santoso, S.Pd. | *(tidak wali kelas)* |
| `guru.lestari.wulandari@sman1tajurhalang.sch.id` | `password123` | Lestari Wulandari, S.Pd. | *(tidak wali kelas)* |
| `guru.gunawan.setiadi@sman1tajurhalang.sch.id` | `password123` | Gunawan Setiadi, M.Hum. | *(tidak wali kelas — nonaktif)* |

---

### Siswa (contoh akun — pola email: `siswa.[NIS]@sman1tajurhalang.sch.id`)

Format NIS: `[tahun_masuk][4-digit urut]`

| Contoh Email | Password | Kelas | Keterangan |
|-------------|----------|-------|------------|
| `siswa.20250001@sman1tajurhalang.sch.id` | `password123` | X-1 | Siswa pertama angkatan 2025 |
| `siswa.20240001@sman1tajurhalang.sch.id` | `password123` | XI IPA 1 | Siswa pertama angkatan 2024 |
| `siswa.20230001@sman1tajurhalang.sch.id` | `password123` | XII IPA 1 | Siswa pertama angkatan 2023 |

> Untuk mendapatkan daftar lengkap NIS, jalankan:  
> `SELECT nis, name, email FROM users JOIN students ON users.id = students.user_id LIMIT 20;`

---

### Orang Tua/Wali (contoh akun — pola email: `ortu.[nama]@gmail.com`)

Akun orang tua di-generate secara acak dengan Faker. Untuk menemukan akun orang tua dari siswa tertentu:

```sql
SELECT u.email, p.name, p.phone
FROM users u
JOIN parents p ON u.id = p.user_id
JOIN students s ON s.parent_id = p.id
WHERE s.nis = '20250001';
```

---

## Data yang Di-seed

| Entitas | Jumlah | Catatan |
|---------|--------|---------|
| Tahun Ajaran | 1 | 2025/2026 (aktif) |
| Semester | 2 | Ganjil (aktif), Genap |
| Jurusan | 4 | UMUM, IPA, IPS, BAHASA |
| Mata Pelajaran | 17 | Mapel SMA lengkap |
| Kelas | 24 | 8×X + 8×XI + 8×XII |
| Guru | 28 | 24 wali kelas + 4 tambahan |
| Super Admin | 2 | — |
| Guru Piket | 1 | Akun shared |
| Orang Tua | ~494 | 3 diset nonaktif untuk testing filter |
| Siswa | ~548 | 24 kelas, ~23 siswa/kelas rata-rata |
| Absensi | ~7.400 | 14 hari kerja × ~530 siswa aktif × 96% hadir/izin/sakit/terlambat |

---

## Struktur Kelas

| Tingkat | Kelas | Jurusan | Jumlah Siswa |
|---------|-------|---------|-------------|
| X | X-1 s/d X-8 | UMUM (belum penjurusan) | 22–25 per kelas |
| XI | XI IPA 1–4, XI IPS 1–3, XI Bahasa 1 | IPA / IPS / BAHASA | 20–26 per kelas |
| XII | XII IPA 1–4, XII IPS 1–3, XII Bahasa 1 | IPA / IPS / BAHASA | 19–25 per kelas |

---

## Catatan Teknis

- **Bulk insert** digunakan untuk semua entitas besar (parents, students, attendance) supaya seed cepat.
- **Peran (roles)** di-assign via `model_has_roles` tabel Spatie secara bulk.
- **Password** semua akun: `password123` (di-hash dengan `bcrypt`).
- **NIK** orang tua mengikuti format Indonesia: kode wilayah (6 digit) + tanggal lahir (6 digit, wanita +40 pada hari) + nomor urut (4 digit).
- **NISN** siswa: 10 digit mulai dari `0012345000` dst.
- **Absensi alpha** (tidak hadir tanpa keterangan) = tidak ada record sama sekali di tabel (4% distribusi).
- Seeder **idempotent** (`updateOrCreate`) untuk semua entitas statis; bulk-insert untuk entitas besar (jalankan hanya sekali atau setelah `migrate:fresh`).

---

## File Seeder

```
database/seeders/
├── DatabaseSeeder.php          ← entry point, urutan dependency
├── AcademicYearSeeder.php      ← tahun ajaran + semester
├── MajorSeeder.php             ← jurusan (UMUM, IPA, IPS, BAHASA)
├── SubjectSeeder.php           ← 17 mata pelajaran
├── TeacherSeeder.php           ← 28 guru + akun login
├── ClassSeeder.php             ← 24 kelas + wali kelas
├── SuperAdminSeeder.php        ← 2 akun super admin
├── GuruPiketSeeder.php         ← 1 akun shared piket
├── ParentSeeder.php            ← 494 orang tua + akun login
├── StudentSeeder.php           ← 548 siswa + akun login
└── AttendanceSeeder.php        ← 14 hari data absensi dummy