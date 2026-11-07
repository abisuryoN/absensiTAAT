{{--
    Penjelasan untuk anak yang berstatus nonaktif.
    Variabel: $student (Student)
--}}
@if(!$student->is_active)
<div class="alert alert-warning d-flex align-items-start gap-2 mb-4" role="alert">
    <i class="bi bi-info-circle-fill mt-1"></i>
    <div>
        <div class="fw-semibold">{{ $student->name }} berstatus nonaktif</div>
        <div class="small">
            Siswa yang nonaktif (misalnya sudah lulus, pindah sekolah, atau keluar) tidak lagi mengikuti
            absensi harian, sehingga data kehadiran terbaru tidak akan bertambah dan tampil 0.
            Riwayat kehadiran sebelum dinonaktifkan tetap bisa dilihat lewat filter tanggal di Rekap Harian
            atau pilihan tahun di Rekap Bulanan. Jika status ini keliru, hubungi admin sekolah.
        </div>
    </div>
</div>
@endif
