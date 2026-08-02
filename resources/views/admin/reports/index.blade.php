<x-app-layout>
    @section('title', 'Laporan Absensi')

    @push('styles')
    <style>
        .btn-export-excel {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4) !important;
            transition: all 0.25s ease !important;
            font-weight: 600;
            padding: 8px 16px !important;
            border-radius: 10px !important;
            display: inline-flex !important;
            align-items: center;
            gap: 6px;
        }
        .btn-export-excel:hover {
            transform: translateY(-2px);
            color: #ffffff !important;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.55) !important;
        }
        .btn-export-excel:active {
            transform: translateY(0);
        }
        .btn-export-pdf {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4) !important;
            transition: all 0.25s ease !important;
            font-weight: 600;
            padding: 8px 16px !important;
            border-radius: 10px !important;
            display: inline-flex !important;
            align-items: center;
            gap: 6px;
        }
        .btn-export-pdf:hover {
            transform: translateY(-2px);
            color: #ffffff !important;
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.55) !important;
        }
        .btn-export-pdf:active {
            transform: translateY(0);
        }

        /* Uniform status badge styling for alignment */
        .status-badge {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 105px;
            padding: 6px 10px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border-radius: 6px !important;
            text-transform: capitalize;
            text-align: center;
        }
    </style>
    @endpush

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
                        <div class="custom-select-wrapper" data-placeholder="Pilih Opsi">
                        <select name="report_type" id="report_type" class="form-select form-select-sm" onchange="toggleSubjectFilter()">
                            <option value="gate" {{ $reportType === 'gate' ? 'selected' : '' }}>Absensi Gerbang (Gate)</option>
                            <option value="subject" {{ $reportType === 'subject' ? 'selected' : '' }}>Absensi Mata Pelajaran</option>
                        </select>
                        </div>
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
                        <div class="custom-select-wrapper" data-placeholder="Semua Kelas">
                        <select name="class_id" class="form-select form-select-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-2" id="subject_filter_wrapper" style="display: {{ $reportType === 'subject' ? 'block' : 'none' }}">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Mata Pelajaran</label>
                        <div class="custom-select-wrapper" data-placeholder="Semua Mapel">
                        <select name="subject_id" class="form-select form-select-sm">
                            <option value="">Semua Mapel</option>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                                    {{ $subj->name }}
                                </option>
                            @endforeach
                        </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Status</label>
                        <div class="custom-select-wrapper" data-placeholder="Semua Status">
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
                            <a href="{{ route('admin.reports.excel', request()->query()) }}" class="btn btn-sm btn-export-excel rounded-3 px-3 fs-8">
                                <i class="bi bi-file-earmark-excel me-1"></i>Ekspor Excel
                            </a>
                            <!-- PDF Download -->
                            <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="btn btn-sm btn-export-pdf rounded-3 px-3 fs-8">
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
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 13%;">Tanggal</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 5%; text-align: center;">Foto</th>
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
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 13%;">Tanggal</th>
                                                <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 5%; text-align: center;">Foto</th>
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
                                                    <td data-label="No" class="px-4 text-muted fs-7">{{ $no++ }}</td>
                                                    <td data-label="Tanggal">
                                                        <span class="fw-semibold text-dark fs-7 d-block">{{ $row->date->format('d M Y') }}</span>
                                                        <span class="text-muted fs-8">{{ $row->date->translatedFormat('l') }}</span>
                                                    </td>
                                                    <td data-label="Foto" style="text-align: center;">
                                                        <img src="{{ $row->student->user?->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($row->student->name ?? 'Siswa').'&background=6366f1&color=fff&size=80&bold=true' }}"
                                                             alt="{{ $row->student->name ?? '-' }}"
                                                             class="rounded-circle object-fit-cover clickable-avatar"
                                                             style="width:36px;height:36px;border:2px solid #e2e8f0;cursor:pointer;"
                                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($row->student->name ?? 'Siswa') }}&background=6366f1&color=fff&size=80&bold=true'"
                                                        >
                                                    </td>
                                                    <td data-label="Nama Siswa">
                                                        <span class="fw-semibold text-dark d-block fs-7">{{ $row->student->name ?? '-' }}</span>
                                                        <span class="text-muted fs-8">NIS: {{ $row->student->nis ?? '-' }}</span>
                                                    </td>
                                                    <td data-label="Kelas">
                                                        <span class="fw-semibold text-dark fs-7">{{ $row->student->class->name ?? '-' }}</span>
                                                    </td>
                                                    <td data-label="Jam Masuk">
                                                        @if($row->time_in && $row->time_in !== '00:00:00')
                                                            <span class="badge bg-dark bg-opacity-10 text-dark px-2 py-1 fs-8 fw-semibold">
                                                                {{ substr($row->time_in, 0, 5) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted fs-8">-</span>
                                                        @endif
                                                    </td>
                                                    <td data-label="Status">
                                                        <span class="badge {{ $cfg['bg'] }} status-badge">
                                                            <i class="bi {{ $cfg['icon'] }}"></i>
                                                            <span>{{ ucfirst(str_replace('_', ' ', $row->status)) }}</span>
                                                        </span>
                                                    </td>
                                                    <td data-label="Metode">
                                                        <span class="fs-8 text-muted">{{ ucfirst($row->method) }}</span>
                                                    </td>
                                                    <td data-label="Catatan">
                                                        <span class="fs-8 text-muted" title="{{ $row->note }}">{{ $row->note ?? '-' }}</span>
                                                    </td>
                                                </tr>
                                            @else
                                                @php
                                                    $attendance = $row->attendanceSubject;
                                                    $schedule = $attendance->schedule ?? null;
                                                @endphp
                                                <tr>
                                                    <td data-label="No" class="px-4 text-muted fs-7">{{ $no++ }}</td>
                                                    <td data-label="Tanggal">
                                                        <span class="fw-semibold text-dark fs-7 d-block">
                                                            {{ $attendance && $attendance->date ? $attendance->date->format('d M Y') : '-' }}
                                                        </span>
                                                        <span class="text-muted fs-8">
                                                            {{ $attendance && $attendance->date ? $attendance->date->translatedFormat('l') : '-' }}
                                                        </span>
                                                    </td>
                                                    <td data-label="Foto" style="text-align: center;">
                                                        <img src="{{ $row->student->user?->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($row->student->name ?? 'Siswa').'&background=6366f1&color=fff&size=80&bold=true' }}"
                                                             alt="{{ $row->student->name ?? '-' }}"
                                                             class="rounded-circle object-fit-cover clickable-avatar"
                                                             style="width:36px;height:36px;border:2px solid #e2e8f0;cursor:pointer;"
                                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($row->student->name ?? 'Siswa') }}&background=6366f1&color=fff&size=80&bold=true'"
                                                        >
                                                    </td>
                                                    <td data-label="Mapel / Jam">
                                                        <span class="fw-semibold text-dark fs-7 d-block">{{ $schedule->subject->name ?? '-' }}</span>
                                                        <span class="text-muted fs-8">
                                                            {{ $schedule ? substr($schedule->start_time, 0, 5) . ' - ' . substr($schedule->end_time, 0, 5) : '-' }}
                                                        </span>
                                                    </td>
                                                    <td data-label="Nama Siswa / Kelas">
                                                        <span class="fw-semibold text-dark fs-7 d-block">{{ $row->student->name ?? '-' }}</span>
                                                        <span class="text-muted fs-8">Kelas: {{ $row->student->class->name ?? '-' }}</span>
                                                    </td>
                                                    <td data-label="Guru Pengajar">
                                                        <span class="fs-7 text-dark">{{ $schedule->teacher->name ?? '-' }}</span>
                                                    </td>
                                                    <td data-label="Status">
                                                        <span class="badge {{ $cfg['bg'] }} status-badge">
                                                            <i class="bi {{ $cfg['icon'] }}"></i>
                                                            <span>{{ ucfirst(str_replace('_', ' ', $row->status)) }}</span>
                                                        </span>
                                                    </td>
                                                    <td data-label="Catatan">
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

        document.addEventListener('DOMContentLoaded', function () {
            // Build lightbox if not present
            let lightbox = document.querySelector('.avatar-lightbox');
            if (!lightbox) {
                lightbox = document.createElement('div');
                lightbox.className = 'avatar-lightbox';
                lightbox.innerHTML = `
                    <div class="lightbox-backdrop"></div>
                    <div class="lightbox-content">
                        <div class="lightbox-header">
                            <h5 class="lightbox-title">Foto Profil</h5>
                            <button class="lightbox-close">&times;</button>
                        </div>
                        <div class="lightbox-body">
                            <div class="lightbox-img-wrapper">
                                <img class="lightbox-img" src="" alt="" draggable="false">
                            </div>
                        </div>
                        <div class="lightbox-controls">
                            <button class="control-btn zoom-out-btn"><i class="bi bi-zoom-out"></i></button>
                            <button class="control-btn reset-btn">1:1</button>
                            <button class="control-btn zoom-in-btn"><i class="bi bi-zoom-in"></i></button>
                        </div>
                    </div>
                `;
                document.body.appendChild(lightbox);

                const style = document.createElement('style');
                style.textContent = `
                    .avatar-lightbox { position:fixed; top:0; left:0; width:100%; height:100%; z-index:10000; display:flex; align-items:center; justify-content:center; opacity:0; visibility:hidden; transition:opacity .25s ease,visibility .25s ease; }
                    .avatar-lightbox.show { opacity:1; visibility:visible; }
                    .lightbox-backdrop { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,.7); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); }
                    .lightbox-content { position:relative; z-index:10001; background:#fff; border-radius:20px; width:90%; max-width:400px; box-shadow:0 20px 25px -5px rgba(0,0,0,.15),0 10px 10px -5px rgba(0,0,0,.04); display:flex; flex-direction:column; overflow:hidden; transform:scale(.9); transition:transform .25s cubic-bezier(.34,1.56,.64,1); }
                    .avatar-lightbox.show .lightbox-content { transform:scale(1); }
                    .lightbox-header { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid #f1f5f9; }
                    .lightbox-title { margin:0; font-weight:700; color:#1e293b; font-size:1.05rem; }
                    .lightbox-close { border:none; background:#f1f5f9; color:#64748b; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.15rem; cursor:pointer; transition:all .2s ease; outline:none!important; box-shadow:none!important; padding:0; }
                    .lightbox-close:hover { background:#e2e8f0; color:#0f172a; }
                    .lightbox-body { padding:24px; display:flex; align-items:center; justify-content:center; background:#f8fafc; }
                    .lightbox-img-wrapper { width:260px; height:260px; border-radius:50%; overflow:hidden; border:4px solid #fff; box-shadow:0 10px 15px -3px rgba(0,0,0,.1); cursor:grab; background:#e2e8f0; }
                    .lightbox-img-wrapper:active { cursor:grabbing; }
                    .lightbox-img { width:100%; height:100%; object-fit:cover; transform-origin:center; user-select:none; -webkit-user-drag:none; }
                    .lightbox-controls { display:flex; justify-content:center; gap:12px; padding:14px; background:#fff; border-top:1px solid #f1f5f9; }
                    .control-btn { border:1px solid #e2e8f0; background:#fff; color:#475569; padding:6px 14px; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; transition:all .2s ease; display:inline-flex; align-items:center; justify-content:center; gap:6px; outline:none!important; box-shadow:none!important; }
                    .control-btn:hover { background:#f8fafc; border-color:#cbd5e1; color:#0f172a; }
                `;
                document.head.appendChild(style);
            }

            const img      = lightbox.querySelector('.lightbox-img');
            const wrapper  = lightbox.querySelector('.lightbox-img-wrapper');
            const titleEl  = lightbox.querySelector('.lightbox-title');
            const closeBtn = lightbox.querySelector('.lightbox-close');
            const backdrop = lightbox.querySelector('.lightbox-backdrop');
            const zoomIn   = lightbox.querySelector('.zoom-in-btn');
            const zoomOut  = lightbox.querySelector('.zoom-out-btn');
            const resetBtn = lightbox.querySelector('.reset-btn');

            let scale = 1, px = 0, py = 0, sx = 0, sy = 0, dragging = false;

            function applyTransform() {
                const max = Math.max(0, (scale - 1) * 130);
                px = Math.max(-max, Math.min(max, px));
                py = Math.max(-max, Math.min(max, py));
                img.style.transform = `translate(${px}px,${py}px) scale(${scale})`;
            }
            function resetZoom() { scale = 1; px = 0; py = 0; applyTransform(); }

            zoomIn.addEventListener('click',  () => { scale = Math.min(4, scale + .35); applyTransform(); });
            zoomOut.addEventListener('click', () => { scale = Math.max(1, scale - .35); applyTransform(); });
            resetBtn.addEventListener('click', resetZoom);

            wrapper.addEventListener('wheel', e => { e.preventDefault(); scale = e.deltaY < 0 ? Math.min(4, scale + .15) : Math.max(1, scale - .15); applyTransform(); });
            wrapper.addEventListener('mousedown', e => { e.preventDefault(); if (scale <= 1) return; dragging = true; sx = e.clientX - px; sy = e.clientY - py; });
            window.addEventListener('mousemove', e => { if (!dragging) return; px = e.clientX - sx; py = e.clientY - sy; applyTransform(); });
            window.addEventListener('mouseup', () => { dragging = false; });
            wrapper.addEventListener('touchstart', e => { if (scale <= 1) return; dragging = true; sx = e.touches[0].clientX - px; sy = e.touches[0].clientY - py; });
            wrapper.addEventListener('touchmove',  e => { if (!dragging) return; px = e.touches[0].clientX - sx; py = e.touches[0].clientY - sy; applyTransform(); });
            wrapper.addEventListener('touchend',   () => { dragging = false; });

            window.openAvatarLightbox = function(src, name) {
                img.removeAttribute('src');
                let hd = src;
                if (hd.includes('ui-avatars.com')) hd = hd.replace(/size=\d+/, 'size=512');
                titleEl.textContent = name;
                img.src = hd;
                resetZoom();
                lightbox.classList.add('show');
                document.body.style.overflow = 'hidden';
            };

            function closeLightbox() { lightbox.classList.remove('show'); document.body.style.overflow = ''; }
            closeBtn.addEventListener('click', closeLightbox);
            backdrop.addEventListener('click', closeLightbox);
            window.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

            // Wire up all avatar clicks
            document.querySelectorAll('.clickable-avatar').forEach(el => {
                el.addEventListener('click', function () {
                    window.openAvatarLightbox(this.getAttribute('src'), this.getAttribute('alt') || 'Foto Profil');
                });
            });
        });
    </script>
    @endpush

    <style>
        .fs-9 { font-size: 0.7rem; }
    </style>
</x-app-layout>
