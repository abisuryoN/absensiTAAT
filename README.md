# Absensi Sekolah

Aplikasi absensi sekolah berbasis web menggunakan Laravel dan MySQL.

## 1. Teknologi yang Digunakan

- PHP 8.3
- Laravel 12 / Laravel 13
- MySQL
- Composer
- Node.js dan npm
- Git dan GitLab

**Persyaratan:** Gunakan PHP 8.3 agar kompatibel dengan Laravel 12 maupun Laravel 13. Pastikan ekstensi PHP yang dibutuhkan Laravel juga sudah aktif.

## 2. Instalasi Proyek

### Langkah 1. Clone repository

```bash
git clone https://gitlab.com/abisuryoN/absensi-sekolah.git
cd absensi-sekolah
```

### Langkah 2. Install dependency PHP

```bash
composer install
```

### Langkah 3. Buat file environment

Linux:

```bash
cp .env.example .env
```

Windows:

```bash
copy .env.example .env
```

### Langkah 4. Generate application key

```bash
php artisan key:generate
```

### Langkah 5. Buat database MySQL

Buka MySQL melalui phpMyAdmin, Laragon, atau MySQL client. Buat database baru, misalnya:

```sql
CREATE DATABASE absensi_sekolah;
```

### Langkah 6. Konfigurasi database

Buka file `.env`, lalu sesuaikan konfigurasi berikut dengan MySQL lokal masing-masing.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_sekolah
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan username dan password dengan konfigurasi MySQL di komputer masing-masing.

### Langkah 7. Jalankan migration dan seeder

**PERHATIAN:** Perintah berikut akan membuat tabel dan menjalankan data awal. Gunakan database development yang masih kosong.

```bash
php artisan migrate --seed
```

Jangan menggunakan `migrate:fresh` pada database yang berisi data penting karena perintah tersebut menghapus tabel sebelum membuatnya kembali.

### Langkah 8. Install dependency frontend

```bash
npm install
npm run build
```

### Langkah 9. Jalankan aplikasi

```bash
php artisan serve
```

Buka alamat berikut di browser:

http://127.0.0.1:8000

## 3. Akun Login Seeder

Akun di bawah ini harus sesuai dengan akun yang benar-benar dibuat oleh seeder proyek.

| Role | Email | Password |
|---|---|---|
| Admin | Isi sesuai seeder | Isi sesuai seeder |
| HRD | Isi sesuai seeder | Isi sesuai seeder |
| Guru | Isi sesuai seeder | Isi sesuai seeder |
| Karyawan | Isi sesuai seeder | Isi sesuai seeder |

**Catatan:** Daftar role di atas merupakan template. Hapus role yang tidak digunakan dan tambahkan role lain sesuai implementasi aplikasi. Jangan menganggap semua akun tersebut sudah tersedia sebelum memeriksa seeder.

## 4. Aturan Kolaborasi GitLab

Repository utama:

https://gitlab.com/abisuryoN/absensi-sekolah

Branch `main` adalah branch utama yang harus dijaga tetap stabil.

### Aturan utama

1. Jangan mengerjakan fitur langsung di branch `main`.
2. Setiap anggota mengerjakan fitur di branch masing-masing.
3. Push hasil pekerjaan ke branch milik sendiri.
4. Ajukan Merge Request (MR) ke `main`.
5. Pemilik repository memeriksa dan menggabungkan MR setelah kode dinyatakan aman.
6. Jangan menggunakan `git push --force` pada branch `main`.
7. Jangan mengunggah file `.env`, password, token, atau kredensial rahasia ke GitLab.

## 5. Alur Kerja Anggota Tim

### A. Pertama kali mengunduh proyek

Jika repository belum ada di komputer:

```bash
git clone https://gitlab.com/abisuryoN/absensi-sekolah.git
cd absensi-sekolah
```

Lanjutkan instalasi sesuai bagian Instalasi Proyek.

### B. Ambil kode terbaru dari main

Sebelum mulai mengerjakan fitur:

```bash
git switch main
git pull origin main
```

Jika hasil clone menggunakan remote `origin`, perintah tersebut mengambil pembaruan dari repository utama.

### C. Buat branch sendiri

Contoh jika mengerjakan fitur laporan:

```bash
git switch -c feature/laporan
```

Contoh nama branch lain:

- `feature/absensi`
- `feature/kelola-karyawan`
- `fix/perbaikan-login`
- `feature/laporan`

Gunakan nama yang menjelaskan pekerjaan.

### D. Kerjakan dan simpan perubahan

Periksa file yang berubah:

```bash
git status
```

Tambahkan file yang relevan dan buat commit:

```bash
git add .
git commit -m "Menambahkan fitur laporan"
```

### E. Push branch ke GitLab

```bash
git push -u origin feature/laporan
```

Ganti `feature/laporan` dengan nama branch milik sendiri.

### F. Ajukan Merge Request

1. Buka repository GitLab.
2. Pilih **Code → Merge requests** atau menu Merge Requests.
3. Klik **New merge request**.
4. Source branch: `feature/laporan`.
5. Target branch: `main`.
6. Jelaskan perubahan yang dibuat.
7. Ajukan Merge Request untuk diperiksa pemilik repository.

**Pekerjaan anggota belum masuk ke `main` hanya karena sudah di-push.** Perubahan baru menjadi bagian dari branch utama setelah Merge Request diterima dan di-merge.

## 6. Alur Pemilik Repository

Pemilik repository bertugas memeriksa dan menggabungkan pekerjaan anggota.

### A. Periksa Merge Request

Pastikan perubahan:

- Tidak merusak fitur yang sudah ada.
- Tidak memasukkan kredensial rahasia.
- Tidak mengandung konflik yang belum diselesaikan.
- Sudah diuji sesuai fitur yang dikerjakan.

### B. Merge pekerjaan anggota

Jika perubahan sudah sesuai, pemilik dapat melakukan merge melalui GitLab.

### C. Ambil hasil terbaru ke komputer lokal

```bash
git switch main
git pull origin main
```

Jika pekerjaan selanjutnya dilakukan di branch lokal, buat branch baru dari `main` yang sudah diperbarui.

### D. Jika perlu menguji pekerjaan anggota sebelum merge

Ambil branch anggota:

```bash
git fetch origin
git switch --track origin/feature/laporan
```

Sesuaikan nama branch dengan branch yang akan diperiksa.

Setelah selesai menguji, kembali ke `main`:

```bash
git switch main
git pull origin main
```

## 7. Catatan Penting

- `pull` mengambil perubahan dari remote dan menggabungkannya ke branch lokal.
- `push` mengirim commit lokal ke remote.
- `commit` menyimpan snapshot perubahan di repository lokal.
- `Merge Request` adalah permintaan agar perubahan dari branch anggota ditinjau dan digabungkan ke branch tujuan.
- Jika terjadi konflik, selesaikan konflik sebelum melakukan merge.
- Setiap anggota sebaiknya melakukan `pull` sebelum mulai bekerja dan sebelum membuat perubahan besar.
- File `.env` harus dibuat sendiri berdasarkan `.env.example`.
- Jangan menjalankan migration destruktif pada database yang berisi data penting.

## 8. Pembagian Tugas Tim

Catat pembagian fitur agar tidak mengerjakan file yang sama tanpa koordinasi.

| Anggota | Fitur | Branch | Status |
|---|---|---|---|
| Pemilik repository | Integrasi dan review | `main` | Aktif |
| Anggota 1 | Sesuaikan dengan tugas | `feature/nama-fitur` | Belum dimulai |
| Anggota 2 | Sesuaikan dengan tugas | `feature/nama-fitur` | Belum dimulai |

---

**Repository:** https://gitlab.com/abisuryoN/absensi-sekolah

**Aturan kerja:** Branch sendiri → commit → push → Merge Request → review → merge ke `main`.
