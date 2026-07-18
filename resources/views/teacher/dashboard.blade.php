<x-app-layout>
    @section('title', 'Dashboard Guru')

    <!-- Welcome Header (Photo 4 style) -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="bg-dark bg-opacity-10 text-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background-color: #334155 !important;">
            <i class="bi bi-eye-slash text-white fs-5"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-1 text-dark">Halo, {{ strtoupper($teacher->name) }}!</h4>
            <p class="text-muted mb-0 fs-7">
                Mode Privasi Aktif: Data sensitif disembunyikan.
            </p>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card stat-card glass-card text-white bg-primary border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-8 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Jadwal Hari Ini</span>
                        <h2 class="fw-bold mb-0 mt-1">{{ $schedulesCount }} Kelas</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-calendar-check fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card stat-card glass-card text-white bg-success border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-8 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Selesai Dikirim</span>
                        <h2 class="fw-bold mb-0 mt-1">{{ $submittedCount }} Kelas</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-check2-all fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card stat-card glass-card text-white bg-warning border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-8 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Draf Tersimpan</span>
                        <h2 class="fw-bold mb-0 mt-1">{{ $draftCount }} Kelas</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-pencil-square fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card glass-card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history me-2 text-primary"></i>Jadwal Mengajar Hari Ini
            </h5>
            <span class="badge bg-light text-dark px-3 py-2 fs-8 border rounded-3 fw-semibold">
                <i class="bi bi-calendar-event me-1 text-primary"></i>{{ Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
            </span>
        </div>
        <div class="card-body px-4 pb-4">
            @if($todaySchedules->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
                    <h6 class="fw-semibold text-muted mb-1">Tidak Ada Jadwal Mengajar Hari Ini</h6>
                    <p class="text-muted fs-8 mb-0">Selamat beristirahat atau silakan periksa tab jadwal mingguan.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 15%;">Waktu</th>
                                <th class="fs-8 fw-semibold text-muted text-uppercase">Mata Pelajaran</th>
                                <th class="fs-8 fw-semibold text-muted text-uppercase">Kelas</th>
                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 20%;">Status Absensi</th>
                                <th class="fs-8 fw-semibold text-muted text-uppercase text-end" style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todaySchedules as $schedule)
                                @php
                                    $attendance = $todayAttendances->get($schedule->id);
                                @endphp
                                <tr>
                                    <td data-label="Waktu">
                                        <span class="badge bg-dark bg-opacity-10 text-dark px-2 py-1 fw-semibold fs-8">
                                            {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                        </span>
                                    </td>
                                    <td data-label="Mata Pelajaran">
                                        <span class="fw-semibold text-dark">{{ $schedule->subject->name ?? '-' }}</span>
                                    </td>
                                    <td data-label="Kelas">
                                        <span class="fw-semibold text-dark">{{ $schedule->class->name ?? '-' }}</span>
                                    </td>
                                    <td data-label="Status Absensi">
                                        @if($attendance)
                                            @if($attendance->status === 'submitted')
                                                <span class="badge bg-success-subtle text-success-emphasis border border-success border-opacity-25 px-2.5 py-1 fs-9">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Sudah Diisi
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning border-opacity-25 px-2.5 py-1 fs-9">
                                                    <i class="bi bi-pencil-square me-1"></i>Draf (Belum Kirim)
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 fs-9">
                                                <i class="bi bi-exclamation-circle me-1"></i>Belum Diisi
                                            </span>
                                        @endif
                                    </td>
                                    <td data-label="Aksi" class="text-end">
                                        @if($attendance && $attendance->status === 'submitted')
                                            <a href="{{ route('teacher.attendance.input', $schedule->id) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fs-8">
                                                <i class="bi bi-pencil me-1"></i>Ubah Absen
                                            </a>
                                        @else
                                            <a href="{{ route('teacher.attendance.input', $schedule->id) }}" class="btn btn-sm btn-primary rounded-3 px-3 fs-8">
                                                <i class="bi bi-check2-square me-1"></i>Isi Absensi
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
