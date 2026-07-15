<x-app-layout>
    @section('title', 'Portal Siswa')

    <!-- Welcome Header (Photo 4 style) -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="bg-dark bg-opacity-10 text-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background-color: #334155 !important;">
            <i class="bi bi-eye-slash text-white fs-5"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-1 text-dark">Halo, {{ strtoupper($student->name) }}!</h4>
            <p class="text-muted mb-0 fs-7">
                Mode Privasi Aktif: Data sensitif disembunyikan.
            </p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Card: Student Info & Dynamic QR -->
        <div class="col-12 col-lg-8">
            <div class="card glass-card border-0 shadow-sm p-4 h-100">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-md-5 text-center">
                        <!-- Dynamic QR Code Container -->
                        <div id="qr-container" class="position-relative mx-auto" style="max-width: 220px;">
                            <div id="qr-code-display" class="bg-white border rounded-4 p-3 d-flex flex-column align-items-center justify-content-center" style="aspect-ratio: 1/1;">
                                <div id="qr-loading" class="text-center">
                                    <div class="spinner-border text-primary mb-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="fs-8 text-muted mb-0">Memuat QR Code...</p>
                                </div>
                                <div id="qr-image" class="d-none text-center w-100">
                                    <!-- QR SVG will be injected here -->
                                </div>
                                <div id="qr-error" class="d-none text-center">
                                    <i class="bi bi-exclamation-triangle fs-1 text-danger mb-2"></i>
                                    <p class="fs-8 text-danger mb-0">Gagal memuat QR Code</p>
                                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="generateQr()">
                                        <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                                    </button>
                                </div>
                            </div>

                            <!-- Countdown Progress Ring -->
                            <div id="qr-countdown" class="d-none mt-2 text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 4px; border-radius: 4px;">
                                        <div id="qr-progress-bar" class="progress-bar bg-primary" role="progressbar" style="width: 100%; transition: width 1s linear;"></div>
                                    </div>
                                    <span id="qr-timer-text" class="fs-8 fw-bold text-primary" style="min-width: 28px;">30s</span>
                                </div>
                                <span class="badge bg-indigo-50 text-indigo-700 mt-1 fs-9">
                                    <i class="bi bi-shield-lock-fill me-1"></i>One-Time Token
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-7">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold text-uppercase px-3 py-1 mb-2 fs-8">
                            @if($student->is_active)
                                <i class="bi bi-check-circle-fill me-1"></i>Siswa Aktif
                            @else
                                <i class="bi bi-x-circle-fill me-1"></i>Siswa Tidak Aktif
                            @endif
                        </span>
                        <h3 class="fw-bold mb-1">{{ $student->name }}</h3>
                        <p class="text-muted mb-3 fs-7">
                            NIS: {{ $student->nis ?? '-' }}
                            @if($student->class)
                                &bull; Kelas: {{ $student->class->name }}
                            @endif
                        </p>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="fs-8 text-muted d-block text-uppercase fw-semibold">Kehadiran Bulan Ini</span>
                                    <span class="fs-4 fw-bold {{ $attendancePercent >= 90 ? 'text-success' : ($attendancePercent >= 75 ? 'text-warning' : 'text-danger') }} mt-1 d-block">
                                        {{ $attendancePercent }}%
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="fs-8 text-muted d-block text-uppercase fw-semibold">Total Terlambat</span>
                                    <span class="fs-4 fw-bold text-warning mt-1 d-block">{{ $terlambatCount }}x</span>
                                </div>
                            </div>
                        </div>

                        <!-- Mini stats row -->
                        <div class="row g-2 mt-2">
                            <div class="col-4">
                                <div class="text-center p-2 bg-success bg-opacity-10 rounded-3">
                                    <span class="d-block fw-bold text-success">{{ $hadirCount }}</span>
                                    <span class="fs-9 text-muted">Hadir</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 bg-info bg-opacity-10 rounded-3">
                                    <span class="d-block fw-bold text-info">{{ $izinCount + $sakitCount }}</span>
                                    <span class="fs-9 text-muted">Izin/Sakit</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 bg-danger bg-opacity-10 rounded-3">
                                    <span class="d-block fw-bold text-danger">{{ $alphaCount }}</span>
                                    <span class="fs-9 text-muted">Alpha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule list on right -->
        <div class="col-12 col-lg-4">
            <div class="card glass-card border-0 shadow-sm p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-calendar3 me-2 text-primary"></i>Jadwal Hari Ini
                    </h5>
                    <a href="{{ route('student.schedule') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                @if($todaySchedules->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-emoji-smile fs-1 text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">Tidak ada jadwal hari ini</p>
                    </div>
                @else
                    <div class="timeline">
                        @foreach($todaySchedules as $schedule)
                            <div class="p-3 bg-light rounded-3 mb-2 border-start border-4 {{ $loop->first ? 'border-primary' : 'border-secondary' }}">
                                <span class="fs-8 text-muted fw-semibold">
                                    {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                </span>
                                <h6 class="fw-bold mb-0 mt-1">{{ $schedule->subject->name ?? '-' }}</h6>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="fs-8 text-muted">
                                        <i class="bi bi-person-fill me-1"></i>{{ $schedule->teacher->name ?? '-' }}
                                    </span>
                                    @if($schedule->room)
                                        <span class="fs-8 text-muted">
                                            <i class="bi bi-geo-alt-fill me-1"></i>{{ $schedule->room }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Links -->
        <div class="col-12">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <a href="{{ route('student.schedule') }}" class="card glass-card border-0 shadow-sm p-3 text-decoration-none text-center h-100 hover-lift">
                        <i class="bi bi-calendar3 fs-2 text-primary mb-2"></i>
                        <span class="fw-semibold text-dark fs-7">Jadwal Mingguan</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('student.history') }}" class="card glass-card border-0 shadow-sm p-3 text-decoration-none text-center h-100 hover-lift">
                        <i class="bi bi-clock-history fs-2 text-success mb-2"></i>
                        <span class="fw-semibold text-dark fs-7">Riwayat Hadir</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('profile.edit') }}" class="card glass-card border-0 shadow-sm p-3 text-decoration-none text-center h-100 hover-lift">
                        <i class="bi bi-person-gear fs-2 text-info mb-2"></i>
                        <span class="fw-semibold text-dark fs-7">Edit Profil</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card glass-card border-0 shadow-sm p-3 text-center h-100">
                        <i class="bi bi-bell fs-2 text-warning mb-2"></i>
                        <span class="fw-semibold text-dark fs-7">Notifikasi</span>
                        <span class="badge bg-secondary mt-1 fs-9">Segera Hadir</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const QR_GENERATE_URL = "{{ route('student.qrcode.generate') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const QR_TTL = {{ $qrTtl }};

        let countdownInterval = null;
        let refreshTimeout = null;
        let remainingSeconds = QR_TTL;

        function generateQr() {
            // Show loading
            document.getElementById('qr-loading').classList.remove('d-none');
            document.getElementById('qr-image').classList.add('d-none');
            document.getElementById('qr-error').classList.add('d-none');
            document.getElementById('qr-countdown').classList.add('d-none');

            clearInterval(countdownInterval);
            clearTimeout(refreshTimeout);

            fetch(QR_GENERATE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Decode and display QR SVG
                    const svgData = atob(data.data.qr_svg);
                    document.getElementById('qr-image').innerHTML = svgData;

                    // Show QR, hide loading
                    document.getElementById('qr-loading').classList.add('d-none');
                    document.getElementById('qr-image').classList.remove('d-none');
                    document.getElementById('qr-countdown').classList.remove('d-none');

                    // Start countdown
                    startCountdown(data.data.ttl_seconds);

                    // Auto-refresh 5 seconds before expiry
                    const refreshDelay = (data.data.ttl_seconds - 5) * 1000;
                    refreshTimeout = setTimeout(() => {
                        generateQr();
                    }, Math.max(refreshDelay, 5000));
                } else {
                    showQrError();
                }
            })
            .catch(() => {
                showQrError();
            });
        }

        function showQrError() {
            document.getElementById('qr-loading').classList.add('d-none');
            document.getElementById('qr-image').classList.add('d-none');
            document.getElementById('qr-error').classList.remove('d-none');
            document.getElementById('qr-countdown').classList.add('d-none');
        }

        function startCountdown(seconds) {
            remainingSeconds = seconds;
            const progressBar = document.getElementById('qr-progress-bar');
            const timerText = document.getElementById('qr-timer-text');

            updateCountdownUI(progressBar, timerText, seconds);

            countdownInterval = setInterval(() => {
                remainingSeconds--;
                if (remainingSeconds <= 0) {
                    clearInterval(countdownInterval);
                    return;
                }
                updateCountdownUI(progressBar, timerText, seconds);
            }, 1000);
        }

        function updateCountdownUI(progressBar, timerText, totalSeconds) {
            const pct = (remainingSeconds / totalSeconds) * 100;
            progressBar.style.width = pct + '%';
            timerText.textContent = remainingSeconds + 's';

            // Color change based on remaining time
            if (remainingSeconds <= 5) {
                progressBar.className = 'progress-bar bg-danger';
                timerText.className = 'fs-8 fw-bold text-danger';
            } else if (remainingSeconds <= 10) {
                progressBar.className = 'progress-bar bg-warning';
                timerText.className = 'fs-8 fw-bold text-warning';
            } else {
                progressBar.className = 'progress-bar bg-primary';
                timerText.className = 'fs-8 fw-bold text-primary';
            }
        }

        // Initial load
        document.addEventListener('DOMContentLoaded', () => {
            generateQr();
        });

        // Pause/Resume on tab visibility
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(countdownInterval);
                clearTimeout(refreshTimeout);
            } else {
                generateQr(); // Refresh immediately when tab becomes visible
            }
        });
    </script>
    @endpush

    <style>
        .fs-9 { font-size: 0.7rem; }
        .bg-indigo-50 { background-color: rgba(99, 102, 241, 0.1); }
        .text-indigo-700 { color: #4338ca; }
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
        }
        #qr-image svg {
            width: 100%;
            height: auto;
        }
    </style>
</x-app-layout>
