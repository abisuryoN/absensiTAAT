# Integrasi API Hari Libur - Dokumentasi

## Ringkasan Perubahan

Sistem hari libur telah diperbarui untuk menggunakan API dari https://libur.deno.dev/api untuk mendapatkan data hari libur nasional secara otomatis. Selain itu, sistem sekarang secara otomatis menandai hari Sabtu dan Minggu sebagai hari libur.

## Fitur Baru

### 1. Sinkronisasi Otomatis dari API
- Data hari libur nasional diambil otomatis dari API publik
- Tidak perlu input manual untuk hari libur nasional
- Data selalu up-to-date dengan kalender resmi Indonesia

### 2. Weekend Otomatis
- Hari Sabtu dan Minggu otomatis ditandai sebagai hari libur
- Berlaku untuk seluruh tahun ajaran
- Tidak memerlukan input manual

### 3. Sistem Absensi
- Absensi tidak dapat dilakukan pada hari Sabtu dan Minggu
- Absensi tidak dapat dilakukan pada hari libur nasional
- Validasi otomatis saat scanning barcode/QR code

## File yang Diubah/Ditambahkan

### File Baru
1. **app/Services/LiburApiService.php**
   - Service untuk berkomunikasi dengan API libur.deno.dev
   - Mengambil dan memproses data hari libur

### File yang Dimodifikasi
1. **app/Services/HolidayService.php**
   - Menambahkan method `syncFromApi()` untuk sinkronisasi
   - Menambahkan method `syncWeekends()` untuk menambah hari Sabtu/Minggu
   - Dependency injection untuk LiburApiService

2. **app/Services/AttendanceGateService.php**
   - Update method `checkHoliday()` untuk cek Sabtu & Minggu
   - Sebelumnya hanya cek Minggu, sekarang cek Sabtu & Minggu

3. **app/Http/Controllers/Admin/HolidayController.php**
   - Menambahkan method `sync()` untuk handle sinkronisasi
   - Update method `index()` untuk pass data academic years ke view

4. **routes/web.php**
   - Menambahkan route POST `/admin/holidays/sync`

5. **resources/views/admin/holidays/index.blade.php**
   - Menambahkan button "Sinkronisasi dari API"
   - Menambahkan modal untuk memilih tahun ajaran

## Cara Penggunaan

### Sinkronisasi Hari Libur
1. Login sebagai Super Admin (Operator TU)
2. Buka menu "Hari Libur"
3. Klik button "Sinkronisasi dari API" (hijau)
4. Pilih tahun ajaran yang ingin disinkronkan
5. Klik "Sinkronisasi"

### Hasil Sinkronisasi
Sistem akan:
- Mengambil data hari libur nasional dari API untuk tahun yang sesuai
- Menambahkan semua hari Sabtu & Minggu dalam rentang tahun ajaran
- Melewati data yang sudah ada (tidak duplikasi)
- Menampilkan pesan berapa data yang berhasil disinkronkan

## Tipe Hari Libur

### National (Nasional)
- Hari libur nasional resmi dari pemerintah
- Diambil dari API (is_national_holiday: true)
- Contoh: Tahun Baru, Idul Fitri, Kemerdekaan RI

### School (Sekolah)
- Cuti bersama dari API (is_national_holiday: false)
- Hari Sabtu dan Minggu (weekend)
- Hari libur khusus sekolah (input manual)

### Exam (Khusus)
- Tetap bisa diinput manual untuk keperluan khusus
- Contoh: Ujian, kegiatan sekolah tertentu

## Contoh Data dari API

```json
[
  {
    "date": "2026-01-01",
    "name": "Tahun Baru 2026 Masehi",
    "is_national_holiday": true
  },
  {
    "date": "2026-02-16",
    "name": "Cuti Bersama Tahun Baru Imlek 2577 Kongzili",
    "is_national_holiday": false
  }
]
```

## Validasi Absensi

### Kondisi yang Memblokir Absensi
1. Hari Sabtu (dayOfWeek = 6)
2. Hari Minggu (dayOfWeek = 0)
3. Hari libur yang terdaftar di database

### Pesan Error
- "Hari ini adalah hari libur sekolah. Absensi tidak dapat dilakukan."

## Technical Details

### API Endpoint
- URL: https://libur.deno.dev/api
- Method: GET
- Response: JSON array
- Timeout: 30 detik

### Database
- Tabel: `holidays`
- Unique constraint: `academic_year_id` + `date`
- Tidak ada duplikasi untuk tahun ajaran dan tanggal yang sama

### Error Handling
- API tidak tersedia → pesan error
- Timeout → pesan error
- Data sudah ada → skip (tidak error)

## Maintenance

### Update Data Hari Libur
- Jalankan sinkronisasi setiap awal tahun ajaran baru
- Atau saat ada perubahan kalender nasional
- Data lama tidak akan tertimpa

### Monitoring
- Check log di storage/logs/laravel.log
- Activity log untuk setiap sinkronisasi
- Jumlah data synced dan skipped dicatat

## Troubleshooting

### Problem: API tidak bisa diakses
**Solusi**: 
- Check koneksi internet
- Coba lagi beberapa saat kemudian
- Input manual jika mendesak

### Problem: Data tidak sesuai
**Solusi**:
- Hapus data yang salah secara manual
- Sinkronisasi ulang
- Atau edit manual jika perlu

### Problem: Weekend tidak otomatis
**Solusi**:
- Jalankan sinkronisasi untuk tahun ajaran tersebut
- Method `syncWeekends()` akan otomatis dipanggil

## Future Improvements

1. Auto-sync scheduled task (cron job)
2. Notifikasi jika ada update hari libur baru
3. Bulk delete untuk clear holiday data
4. Export/Import holiday data
5. Preview sebelum sync