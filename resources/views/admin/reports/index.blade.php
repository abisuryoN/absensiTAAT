<x-app-layout>
    @section('title', 'Laporan Absensi')

    <div class="row g-4">
        <!-- Header -->
        <div class="col-12">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Laporan & Reporting Absensi
                </h4>
                <p class="text-muted mb-0 fs-7">
                    Buat, filter, dan unduh laporan absensi gerbang serta absensi mata pelajaran dalam format Excel atau PDF.
                </p>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="col-12">
            <div class="card glass-card border-0 shadow-sm p-4">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3">
                    <input type="hidden" name="filter" value="1">

                    <div class="col-12 col-md-3">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Jenis Laporan</label>
                        <select name="report_type" id="report_type" class="form-select form-select-sm" onchange="toggleSubjectFilter()">
                            <option value="gate" {{ $reportType === 'gate' ? 'selected' : '' }}>Absensi Gerbang (Gate)</option>
                            <option value="subject" {{ $reportType === 'subject' ? 'selected' : '' }}>Absensi Mata Pelajaran</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2.5 col-lg-2">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                    </div>

                    <div class="col-12 col-md-2.5 col-lg-2">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Kelas</label>
                        <select name="class_id" class="form-select form-select-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2" id="subject_filter_wrapper" style="display: {{ $reportType === 'subject' ? 'block' : 'none' }};">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Mata Pelajaran</label>
                        <select name="subject_id" class="form-select form-select-sm">
                            <option value="">Semua Mapel</option>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                                    {{ $subj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            @if($reportType === 'gate')
                                <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="izin" {{ request('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ request('status') === 'alpha' ? 'selected' : '' }}>Alpha</option>
                            @else
                                <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="izin" {{ request('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ request('status') === 'alpha' ? 'selected' : '' }}>Alpha</option>
                                <option value="dispensasi" {{ request('status') === 'dispensasi' ? 'selected' : '' }}>Dispensasi</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Card -->
        @if(request()->has('filter'))
            <div class="col-12">
                <div class="card glass-card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-eye me-2 text-primary"></i>Preview Data Laporan
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Excel Download -->
                            <a href="{{ route('admin.reports.excel', request()->query()) }}" class="btn btn-sm btn-success rounded-3 px-3 fs-8">
                                <i class="bi bi-file-earmark-excel me-1"></i>Ekspor Excel
                            </a>
                            <!-- PDF Download -->
                            <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="btn btn-sm btn-danger rounded-3 px-3 fs-8">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Ekspor PDF
                            </a>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4">
                        @if($previewData->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">Tidak ada data absensi ditemukan untuk filter yang dipilih.</p>
                            </div>
                        @else
                            <div class="table-responsive mt-3">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        @if($reportType === 'gate')
                                            <tr>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase px-4" style="width: 5%;">No</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 15%;">Tanggal</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase">Nama Siswa</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase">Kelas</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 12%;">Jam Masuk</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 12%;">Status</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 12%;">Metode</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 15%;">Catatan</th>
                                            </tr>
                                        @else
                                            <tr>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase px-4" style="width: 5%;">No</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 15%;">Tanggal</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase">Mata Pelajaran / Jam</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase">Nama Siswa / Kelas</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase">Guru Pengajar</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 12%;">Status</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 15%;">Catatan</th>
                                            </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @php
                                            $no = ($previewData->currentPage() - 1) * $previewData->perPage() + 1;
                                            $statusConfig = [
                                                'hadir' => ['bg' => 'bg-success', 'icon' => 'bi-check-circle-fill'],
                                                'terlambat' => ['bg' => 'bg-warning text-dark', 'icon' => 'bi-clock-fill'],
                                                'izin' => ['bg' => 'bg-info', 'icon' => 'bi-envelope-fill'],
                                                'sakit' => ['bg' => 'bg-primary', 'icon' => 'bi-heart-pulse-fill'],
                                                'alpha' => ['bg' => 'bg-danger', 'icon' => 'bi-x-circle-fill'],
                                                'dispensasi' => ['bg' => 'bg-warning text-dark', 'icon' => 'bi-award-fill'],
                                            ];
                                        @endphp
                                        @foreach($previewData as $row)
                                            @php
                                                $cfg = $statusConfig[$row->status] ?? ['bg' => 'bg-secondary', 'icon' => 'bi-question-circle'];
                                            @endphp
                                            @if($reportType === 'gate')
                                                <tr>
                                                    <td class="px-4 text-muted fs-7">{{ $no++ }}</td>
                                                    <td>
                                                        <span class="fw-semibold text-dark fs-7 d-block">{{ $row->date->format('d M Y') }}</span>
                                                        <span class="text-muted fs-8">{{ $row->date->translatedFormat('l') }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold text-dark d-block fs-7">{{ $row->student->name ?? '-' }}</span>
                                                        <span class="text-muted fs-8">NIS: {{ $row->student->nis ?? '-' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold text-dark fs-7">{{ $row->student->class->name ?? '-' }}</span>
                                                    </td>
                                                    <td>
                                                        @if($row->time_in && $row->time_in !== '00:00:00')
                                                            <span class="badge bg-dark bg-opacity-10 text-dark px-2 py-1 fs-8 fw-semibold">
                                                                {{ substr($row->time_in, 0, 5) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted fs-8">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $cfg['bg'] }} px-2 py-1 fs-9">
                                                            <i class="bi {{ $cfg['icon'] }} me-1"></i>{{ ucfirst($row->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="fs-8 text-muted">{{ ucfirst($row->method) }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="fs-8 text-muted" title="{{ $row->note }}">{{ $row->note ?? '-' }}</span>
                                                    </td>
                                                </tr>
                                            @else
                                                @php
                                                    $attendance = $row->attendanceSubject;
                                                    $schedule = $attendance->schedule ?? null;
                                                @endphp
                                                <tr>
                                                    <td class="px-4 text-muted fs-7">{{ $no++ }}</td>
                                                    <td>
                                                        <span class="fw-semibold text-dark fs-7 d-block">
                                                            {{ $attendance && $attendance->date ? $attendance->date->format('d M Y') : '-' }}
                                                        </span>
                                                        <span class="text-muted fs-8">
                                                            {{ $attendance && $attendance->date ? $attendance->date->translatedFormat('l') : '-' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold text-dark fs-7 d-block">{{ $schedule->subject->name ?? '-' }}</span>
                                                        <span class="text-muted fs-8">
                                                            {{ $schedule ? substr($schedule->start_time, 0, 5) . ' - ' . substr($schedule->end_time, 0, 5) : '-' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold text-dark fs-7 d-block">{{ $row->student->name ?? '-' }}</span>
                                                        <span class="text-muted fs-8">Kelas: {{ $row->student->class->name ?? '-' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="fs-7 text-dark">{{ $schedule->teacher->name ?? '-' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $cfg['bg'] }} px-2 py-1 fs-9">
                                                            <i class="bi {{ $cfg['icon'] }} me-1"></i>{{ ucfirst($row->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="fs-8 text-muted" title="{{ $row->note }}">{{ $row->note ?? '-' }}</span>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="px-4 py-3 border-top mt-3">
                                {{ $previewData->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function toggleSubjectFilter() {
            const reportType = document.getElementById('report_type').value;
            const subjectFilter = document.getElementById('subject_filter_wrapper');

            if (reportType === 'subject') {
                subjectFilter.style.display = 'block';
            } else {
                subjectFilter.style.display = 'none';
            }
        }
    </script>
    @endpush

    <style>
        .fs-9 { font-size: 0.7rem; }
    </style>
</x-app-layout>
