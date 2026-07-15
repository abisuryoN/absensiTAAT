<x-app-layout>
    @section('title', 'Jadwal Mengajar')

    <div class="row g-4">
        <!-- Header -->
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-calendar3 me-2 text-primary"></i>Jadwal Mengajar Anda
                    </h4>
                    <p class="text-muted mb-0 fs-7">
                        Tahun Ajaran & Semester Aktif
                    </p>
                </div>
                <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Weekly Schedule Lists -->
        @foreach($days as $day)
            <div class="col-12">
                <div class="card glass-card border-0 shadow-sm {{ strtolower($today) === $day ? 'border-start border-4 border-primary' : '' }}">
                    <div class="card-header bg-transparent border-0 pb-0 pt-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold mb-0 {{ strtolower($today) === $day ? 'text-primary' : '' }}">
                                {{ ucfirst($day) }}
                            </h5>
                            @if(strtolower($today) === $day)
                                <span class="badge bg-primary bg-opacity-10 text-primary fs-9">
                                    <i class="bi bi-geo-fill me-1"></i>Hari Ini
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body px-4 pb-3">
                        @if(isset($schedules[$day]) && $schedules[$day]->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 20%;">Waktu</th>
                                            <th class="fs-8 fw-semibold text-muted text-uppercase">Mata Pelajaran</th>
                                            <th class="fs-8 fw-semibold text-muted text-uppercase">Kelas</th>
                                            <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 15%;">Ruangan</th>
                                            <th class="fs-8 fw-semibold text-muted text-uppercase text-end" style="width: 15%;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($schedules[$day] as $schedule)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-dark bg-opacity-10 text-dark fw-semibold px-2 py-1 fs-8">
                                                        {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark">{{ $schedule->subject->name ?? '-' }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark">{{ $schedule->class->name ?? '-' }}</span>
                                                </td>
                                                <td>
                                                    @if($schedule->room)
                                                        <span class="fs-7 text-muted">
                                                            <i class="bi bi-geo-alt-fill me-1"></i>{{ $schedule->room }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted fs-8">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('teacher.attendance.input', $schedule->id) }}" class="btn btn-sm btn-light border rounded-3 px-2.5 py-1 fs-8">
                                                        <i class="bi bi-check2-square"></i> Absen
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <span class="text-muted fs-7">
                                    <i class="bi bi-dash-circle me-1"></i>Tidak ada jadwal mengajar
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .fs-9 { font-size: 0.7rem; }
    </style>
</x-app-layout>
