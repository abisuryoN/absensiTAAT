<x-app-layout>
    @section('title', 'Jadwal Pelajaran')

    <div class="row g-4">
        <!-- Header -->
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-calendar3 me-2 text-primary"></i>Jadwal Pelajaran
                    </h4>
                    <p class="text-muted mb-0 fs-7">
                        Kelas {{ $student->class->name ?? '-' }} &bull; Semester Aktif
                    </p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Schedule Cards per Day -->
        @foreach($days as $day)
            <div class="col-12">
                <div class="card glass-card border-0 shadow-sm {{ strtolower($today) === $day ? 'border-start border-4 border-primary' : '' }}">
                    <div class="card-header bg-transparent border-0 pb-0 pt-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold mb-0 {{ strtolower($today) === $day ? 'text-primary' : '' }}">
                                {{ ucfirst($day) }}
                            </h5>
                            @if(strtolower($today) === $day)
                                <span class="badge bg-primary fs-9">
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
                                            <th class="fs-8 fw-semibold text-muted text-uppercase">Guru</th>
                                            <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 15%;">Ruangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($schedules[$day] as $schedule)
                                            <tr>
                                                <td data-label="Waktu">
                                                    <span class="badge bg-dark bg-opacity-10 text-dark fw-semibold px-2 py-1">
                                                        {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                                    </span>
                                                </td>
                                                <td data-label="Mata Pelajaran">
                                                    <span class="fw-semibold">{{ $schedule->subject->name ?? '-' }}</span>
                                                </td>
                                                <td data-label="Guru">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.7rem;">
                                                            {{ substr($schedule->teacher->name ?? '?', 0, 2) }}
                                                        </div>
                                                        <span class="fs-7">{{ $schedule->teacher->name ?? '-' }}</span>
                                                    </div>
                                                </td>
                                                <td data-label="Ruangan">
                                                    @if($schedule->room)
                                                        <span class="fs-7 text-muted">
                                                            <i class="bi bi-geo-alt-fill me-1"></i>{{ $schedule->room }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted fs-8">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <span class="text-muted fs-7">
                                    <i class="bi bi-dash-circle me-1"></i>Tidak ada jadwal
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
