<x-app-layout>
    @section('title', 'Rekap Mengajar')

    <div class="row g-4">
        <!-- Header & Filters -->
        <div class="col-12">
            <div class="card glass-card border-0 shadow-sm p-4">
                <div class="row g-3 align-items-center justify-content-between">
                    <div class="col-12 col-md-6">
                        <h4 class="fw-bold mb-1">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>Rekap Mengajar Anda
                        </h4>
                        <p class="text-muted mb-0 fs-7">
                            Pantau riwayat absensi mata pelajaran yang telah diisi.
                        </p>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <form method="GET" action="{{ route('teacher.recap') }}">
                            <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Bulan Rekap</label>
                            <div class="input-group">
                                <input type="month"
                                       name="month"
                                       value="{{ $selectedMonth }}"
                                       class="form-control form-control-sm border-end-0"
                                       onchange="this.form.submit()">
                                <span class="input-group-text bg-white border-start-0 text-muted">
                                    <i class="bi bi-funnel fs-8"></i>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="col-12">
            <div class="row g-3">
                <div class="col-6 col-lg-3">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-2 fw-bold text-dark d-block">{{ $stats['total'] }}</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Total Tatap Muka</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-2 fw-bold text-success d-block">{{ $stats['submitted'] }}</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Kirim Absensi</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-2 fw-bold text-warning d-block">{{ $stats['draft'] }}</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Draf Absensi</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center">
                        <span class="fs-2 fw-bold text-primary d-block">{{ $stats['presence_rate'] }}%</span>
                        <span class="fs-8 text-muted text-uppercase fw-semibold">Rata-rata Kehadiran Siswa</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teaching History Table -->
        <div class="col-12">
            <div class="card glass-card border-0 shadow-sm">
                <div class="card-body p-0">
                    @if($attendances->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <h6 class="fw-semibold text-muted mb-1">Belum Ada Data Mengajar Bulan Ini</h6>
                            <p class="text-muted fs-8 mb-0">Semua riwayat absensi mapel pada bulan ini akan ditampilkan di sini.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase px-4" style="width: 15%;">Tanggal</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 20%;">Mata Pelajaran</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 12%;">Kelas</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase text-center" style="width: 15%;">Kehadiran Siswa</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 13%;">Status</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase px-4" style="width: 25%;">Jurnal/Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances as $att)
                                        @php
                                            $total = $att->details->count();
                                            $present = $att->details->whereIn('status', ['hadir', 'dispensasi'])->count();
                                            $percent = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td class="px-4">
                                                <span class="fw-semibold text-dark d-block fs-7">
                                                    {{ $att->date->format('d M Y') }}
                                                </span>
                                                <span class="text-muted fs-8">{{ $att->date->translatedFormat('l') }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-dark d-block fs-7">
                                                    {{ $att->schedule->subject->name ?? '-' }}
                                                </span>
                                                <span class="text-muted fs-8">
                                                    {{ substr($att->schedule->start_time ?? '', 0, 5) }} - {{ substr($att->schedule->end_time ?? '', 0, 5) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-dark fs-7">{{ $att->schedule->class->name ?? '-' }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($att->status === 'submitted')
                                                    <span class="fw-bold {{ $percent >= 90 ? 'text-success' : ($percent >= 75 ? 'text-warning' : 'text-danger') }} fs-7">
                                                        {{ $present }} / {{ $total }}
                                                    </span>
                                                    <span class="text-muted fs-8 d-block">({{ $percent }}%)</span>
                                                @else
                                                    <span class="text-muted fs-8">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($att->status === 'submitted')
                                                    <span class="badge bg-success-subtle text-success-emphasis border border-success border-opacity-25 px-2 py-0.5 fs-9">
                                                        Sudah Dikirim
                                                    </span>
                                                    <a href="{{ route('teacher.attendance.input', $att->schedule_id) }}?date={{ $att->date->format('Y-m-d') }}" class="fs-9 d-block text-decoration-none mt-1 text-primary">
                                                        <i class="bi bi-pencil-square"></i> Ubah Data
                                                    </a>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning border-opacity-25 px-2 py-0.5 fs-9">
                                                        Draf
                                                    </span>
                                                    <a href="{{ route('teacher.attendance.input', $att->schedule_id) }}?date={{ $att->date->format('Y-m-d') }}" class="fs-9 d-block text-decoration-none mt-1 text-primary fw-bold">
                                                        <i class="bi bi-check2-square"></i> Lanjutkan
                                                    </a>
                                                @endif
                                            </td>
                                            <td class="px-4">
                                                <span class="fs-8 text-muted d-block text-truncate" style="max-width: 250px;" title="{{ $att->note ?? '-' }}">
                                                    {{ $att->note ?? '-' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .fs-9 { font-size: 0.7rem; }
    </style>
</x-app-layout>
