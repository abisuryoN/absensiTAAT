<x-app-layout>
    @section('title', 'Absensi Gerbang')

    <style>
        .scan-container {
            min-height: calc(100vh - 120px);
        }
        .scanner-card {
            border: 2px dashed rgba(var(--bs-primary-rgb), 0.3);
            background-color: rgba(var(--bs-primary-rgb), 0.02);
            transition: all 0.3s ease;
        }
        .scanner-card.active {
            border-color: var(--bs-success);
            background-color: rgba(25, 135, 84, 0.05);
        }
        .scanner-card.error {
            border-color: var(--bs-danger);
            background-color: rgba(220, 53, 69, 0.05);
        }
        .scanner-card.loading {
            border-color: var(--bs-warning);
            background-color: rgba(255, 193, 7, 0.05);
            pointer-events: none;
            opacity: 0.7;
        }
        .visual-indicator {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            transition: all 0.3s ease;
        }
        .visual-indicator.idle {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }
        .visual-indicator.success {
            background-color: rgba(25, 135, 84, 0.2);
            color: var(--bs-success);
            transform: scale(1.1);
        }
        .visual-indicator.error {
            background-color: rgba(220, 53, 69, 0.2);
            color: var(--bs-danger);
            transform: scale(1.1);
        }
        .visual-indicator.loading-state {
            background-color: rgba(255, 193, 7, 0.2);
            color: var(--bs-warning);
        }
        /* Mode Tabs */
        .method-tabs {
            display: flex;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }
        .method-tab {
            flex: 1;
            padding: 10px 16px;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.25s ease;
            border: none;
            background: transparent;
            color: #6c757d;
        }
        .method-tab:hover {
            background: rgba(var(--bs-primary-rgb), 0.05);
        }
        .method-tab.active {
            background: #fff;
            color: var(--bs-primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .method-tab.active i {
            color: var(--bs-primary);
        }
        .method-tab i {
            font-size: 1.1rem;
            margin-right: 6px;
        }
        /* Camera region */
        .camera-region {
            position: relative;
        }
        #camera-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            min-height: 240px;
            aspect-ratio: 4 / 3;
        }
        #qr-reader-container {
            position: absolute !important;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100% !important;
            border: none !important;
            overflow: hidden;
        }
        #qr-reader-container video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
            -moz-transform: scaleX(1) !important;
            -ms-transform: scaleX(1) !important;
            -o-transform: scaleX(1) !important;
            display: block;
        }
        /* Force remove any mirror/scaleX that html5-qrcode may apply inline */
        #qr-reader-container video[style*="scaleX"] {
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
        }
        /* Target possible wrapper div created by html5-qrcode */
        #qr-reader-container > div > video {
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
        }
        #qr-reader-container > div {
            transform: none !important;
            -webkit-transform: none !important;
        }
        #qr-reader-container canvas {
            display: none !important;
        }
        #qr-reader-container img {
            display: none !important;
        }
        #camera-overlay-loading {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            border-radius: 12px;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        #camera-overlay-loading.show {
            display: flex;
        }
        #camera-overlay-loading .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        /* Camera switch button (mobile floating) */
        .btn-switch-camera {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 5;
            border-radius: 50%;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.5);
            color: #fff;
            border: none;
            font-size: 1.2rem;
            backdrop-filter: blur(4px);
            transition: all 0.2s;
        }
        .btn-switch-camera:hover {
            background: rgba(0,0,0,0.7);
            color: #fff;
        }
        /* USB Scanner hidden input */
        #usb-scanner-input {
            position: fixed;
            left: -9999px;
            top: 0;
            width: 1px;
            height: 1px;
            opacity: 0;
            z-index: -1;
        }
        /* Inline USB indicator */
        .usb-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px;
            background: #f0f9ff;
            border: 1px dashed #0dcaf0;
            border-radius: 12px;
            color: #0aa2c0;
        }
        .usb-indicator .pulse-dot {
            width: 10px;
            height: 10px;
            background: #0dcaf0;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }
        /* Camera dropdown */
        #camera-selector {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 5;
            background: rgba(0,0,0,0.6);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 5px 10px;
            font-size: 0.8rem;
            max-width: 180px;
            backdrop-filter: blur(4px);
        }
        #camera-selector option {
            background: #333;
            color: #fff;
        }
        /* Recent scans */
        .recent-scans-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .recent-scans-list .list-group-item {
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .recent-scans-list .list-group-item:hover {
            background: rgba(var(--bs-primary-rgb), 0.03);
        }
        .recent-scans-list .list-group-item.active-scan {
            border-left-color: var(--bs-success);
            background: rgba(25, 135, 84, 0.05);
        }
        .recent-scans-list .list-group-item.rejected-scan {
            border-left-color: var(--bs-danger);
            background: rgba(220, 53, 69, 0.05);
        }
        /* Student profile card */
        .profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            color: #fff;
            padding: 24px;
            margin-bottom: 20px;
        }
        .profile-card .profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
        }
        .profile-card .profile-detail-item {
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 8px 12px;
        }
        .profile-card .profile-detail-item .label {
            font-size: 0.75rem;
            opacity: 0.7;
        }
        .profile-card .profile-detail-item .value {
            font-weight: 600;
        }
        /* Stok minimal list */
        .minimal-list {
            font-size: 0.9rem;
        }
        .minimal-list .list-group-item {
            padding: 8px 12px;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }
        .minimal-list .list-group-item:last-child {
            border-bottom: none;
        }
        /* Audio scan animation */
        .scan-pulse {
            animation: scannerPulse 1.5s ease-in-out infinite;
        }
        @keyframes scannerPulse {
            0% { box-shadow: 0 0 0 0 rgba(25,135,84,0.4); }
            70% { box-shadow: 0 0 0 15px rgba(25,135,84,0); }
            100% { box-shadow: 0 0 0 0 rgba(25,135,84,0); }
        }
        /* Sound toggle */
        .sound-toggle {
            position: absolute;
            bottom: 10px;
            right: 10px;
            z-index: 5;
            background: rgba(0,0,0,0.5);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
            transition: all 0.2s;
        }
        .sound-toggle:hover {
            background: rgba(0,0,0,0.7);
        }
        /* Profile card backdrop for mobile */
        .profile-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
        }
        .profile-backdrop.show {
            display: block;
        }
        /* Responsive */
        @media (max-width: 576px) {
            #camera-container {
                max-width: 100%;
                border-radius: 8px;
                aspect-ratio: 4 / 3;
            }
            .scanner-card {
                padding: 16px !important;
            }
            .method-tab {
                font-size: 0.8rem;
                padding: 8px 10px;
            }
            .method-tab i {
                font-size: 0.9rem;
                margin-right: 4px;
            }
            .visual-indicator {
                width: 80px;
                height: 80px;
                font-size: 0.9rem;
            }
            .visual-indicator i {
                font-size: 1.5rem !important;
            }
            #camera-selector {
                font-size: 0.7rem;
                max-width: 140px;
                padding: 3px 8px;
            }
            .btn-switch-camera {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }
        }
    </style>

    <div class="container-fluid px-2 px-sm-3 scan-container py-3">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-qr-code-scan me-2"></i>Absensi Gerbang</h5>
            <div class="d-flex align-items-center gap-2">
                <div class="text-end small text-muted d-none d-sm-block" id="liveClock">
                    <div id="liveDate" class="fw-semibold"></div>
                    <div id="liveTime" class="fw-bold" style="font-size: 1.2rem;"></div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- LEFT: Scanner -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 p-sm-4">
                        <!-- M To -->
                        <div class="method-tabs mb-3" role="group">
                            <button type="button" class="method-tab active" id="tabCamera">
                                <i class="bi bi-camera-video"></i> Kamera
                            </button>
                            <button type="button" class="method-tab" id="tabUsb">
                                <i class="bi bi-usb-symbol"></i> USB Scanner
                            </button>
                        </div>

                        <!-- Camera Mode -->
                        <div id="cameraMode">
                            <div class="camera-region">
                                <div id="camera-container">
                                    <div id="qr-reader-container">
                                        <!-- html5-qrcode injects <video> here -->
                                    </div>
                                    <div id="camera-overlay-loading">
                                        <div class="spinner-border text-light" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <select id="camera-selector" class="d-none"></select>
                                    <button type="button" id="btnSwitchCamera" class="btn-switch-camera" title="Ganti Kamera">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    <button type="button" id="btnToggleSound" class="sound-toggle" title="Suara">
                                        <i class="bi bi-volume-up"></i>
                                    </button>
                                </div>
                                <div id="cameraPlaceholder" class="text-center py-5" style="background:#f8f9fa; border-radius: 12px;">
                                    <i class="bi bi-camera-video-off display-4 text-muted mb-3 d-block"></i>
                                    <h6 class="text-muted">Kamera belum aktif</h6>
                                    <p class="text-muted small">Tekan tombol Start untuk mengaktifkan kamera</p>
                                </div>
                                <div class="d-flex gap-2 mt-3 justify-content-center">
                                    <button id="btnStartCamera" class="btn btn-primary btn-sm px-4">
                                        <i class="bi bi-play-fill"></i> Start
                                    </button>
                                    <button id="btnStopCamera" class="btn btn-outline-danger btn-sm px-4 d-none">
                                        <i class="bi bi-stop-fill"></i> Stop
                                    </button>
                                </div>
                            </div>

                            <!-- Camera Status & controls -->
                            <div id="cameraActiveControls" class="d-none mt-3">
                                <!-- Scanner Status Card -->
                                <div class="scanner-card rounded-4 p-4 text-center" id="scannerBox">
                                    <div class="visual-indicator idle" id="visualIndicator">
                                        <i class="bi bi-qr-code-scan fs-1" id="indicatorIcon"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1" id="statusTitle">Siap Memindai</h6>
                                    <p class="text-muted small mb-0" id="statusText">Arahkan QR Code ke kamera untuk scan otomatis.</p>
                                </div>
                            </div>
                        </div>

                        <!-- USB Mode -->
                        <div id="usbMode" style="display:none;">
                            <div class="usb-indicator">
                                <div class="pulse-dot"></div>
                                <div>
                                    <strong>USB Scanner Aktif</strong><br>
                                    <small class="text-muted">Scan QR Code menggunakan USB Scanner...</small>
                                </div>
                            </div>
                            <input type="text" id="usb-scanner-input" autocomplete="off" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Recent Scans + Stats -->
            <div class="col-lg-4">
                <!-- Mini Stats -->
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="card border-0 shadow-sm text-center py-2 bg-success-subtle">
                            <div class="fw-bold text-success" id="mobile-stat-hadir">0</div>
                            <small class="text-muted">Hadir</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-0 shadow-sm text-center py-2 bg-warning-subtle">
                            <div class="fw-bold text-warning" id="mobile-stat-terlambat">0</div>
                            <small class="text-muted">Terlambat</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-0 shadow-sm text-center py-2 bg-danger-subtle">
                            <div class="fw-bold text-danger" id="mobile-stat-tidak-hadir">0</div>
                            <small class="text-muted">Tidak Hadir</small>
                        </div>
                    </div>
                </div>

                <!-- Student Profile Card -->
                <div id="studentProfileContainer" style="display:none;">
                    <div class="profile-card" id="studentProfileCard">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="profile-avatar" id="profileAvatar">A</div>
                            <div>
                                <h6 class="mb-0 fw-bold" id="profileName">-</h6>
                                <small id="profileNis" class="opacity-75">-</small>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="profile-detail-item">
                                    <div class="label">Kelas</div>
                                    <div class="value" id="profileKelas">-</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="profile-detail-item">
                                    <div class="label">Jurusan</div>
                                    <div class="value" id="profileJurusan">-</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="profile-detail-item">
                                    <div class="label">Status</div>
                                    <div class="value" id="profileStatus">-</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="profile-detail-item">
                                    <div class="label">Waktu</div>
                                    <div class="value" id="profileWaktu">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Scans -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-1"></i>Scan Terbaru</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="recent-scans-list list-group list-group-flush minimal-list" id="recentScansList">
                            <div class="list-group-item text-center text-muted py-4" id="noScansYet">
                                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                <small>Belum ada scan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Backdrop for mobile -->
    <div class="profile-backdrop" id="profileBackdrop" onclick="hideStudentProfile()"></div>

    <!-- No audio files needed - beep sounds are generated via Web Audio API -->

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        // ============================================================
        // STATE
        // ============================================================
        let html5QrCode = null;
        let isCameraActive = false;
        let isProcessing = false;
        let selectedCameraId = null;
        let currentMethod = 'camera';
        let availableCameras = [];
        let scannerTimeout = null;
        let soundEnabled = true;
        let recentScanCount = 0;
        let audioCtx = null;

        // ============================================================
        // DOM HELPERS
        // ============================================================
        // The app layout renders the slot content in both a desktop container
        // (d-none d-lg-flex) and a mobile container (d-lg-none).
        // Bootstrap lg breakpoint = 992px.
        // Using offsetParent is unreliable for initially-hidden elements (d-none),
        // so we use window.innerWidth to pick the correct container instead.
        function getEl(id) {
            var isMobile = window.innerWidth < 992;
            var mobileWrap = document.querySelector('.mobile-layout');
            var desktopWrap = document.querySelector('.d-lg-flex.container-fluid');
            var container = (isMobile && mobileWrap) ? mobileWrap
                          : (desktopWrap ? desktopWrap : document.body);
            var el = container.querySelector('[id="' + id + '"]');
            if (el) return el;
            return document.getElementById(id); // fallback
        }

        // ============================================================
        // DOM REFS  (resolved at script run-time → correct for current viewport)
        // ============================================================
        const cameraContainer        = getEl('camera-container');
        const qrReaderContainer      = getEl('qr-reader-container');
        const cameraPlaceholder      = getEl('cameraPlaceholder');
        const cameraActiveControls   = getEl('cameraActiveControls');
        const cameraOverlayLoading   = getEl('camera-overlay-loading');
        const btnStartCamera         = getEl('btnStartCamera');
        const btnStopCamera          = getEl('btnStopCamera');
        const btnSwitchCamera        = getEl('btnSwitchCamera');
        const cameraSelector         = getEl('camera-selector');
        const btnToggleSound         = getEl('btnToggleSound');
        const scannerBox             = getEl('scannerBox');
        const visualIndicator        = getEl('visualIndicator');
        const indicatorIcon          = getEl('indicatorIcon');
        const statusTitle            = getEl('statusTitle');
        const statusText             = getEl('statusText');
        const tabCamera              = getEl('tabCamera');
        const tabUsb                 = getEl('tabUsb');
        const cameraMode             = getEl('cameraMode');
        const usbMode                = getEl('usbMode');
        const usbScannerInput        = getEl('usb-scanner-input');
        const recentScansList        = getEl('recentScansList');
        const profileBackdrop        = getEl('profileBackdrop');
        const studentProfileContainer= getEl('studentProfileContainer');
        // Profile fields
        const profileAvatar   = getEl('profileAvatar');
        const profileName     = getEl('profileName');
        const profileNis      = getEl('profileNis');
        const profileKelas    = getEl('profileKelas');
        const profileJurusan  = getEl('profileJurusan');
        const profileStatus   = getEl('profileStatus');
        const profileWaktu    = getEl('profileWaktu');

        // Live clock
        function updateClock() {
            var now = new Date();
            var dateOpts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            var liveDateEl = document.getElementById('liveDate');
            var liveTimeEl = document.getElementById('liveTime');
            if (liveDateEl) liveDateEl.innerText = now.toLocaleDateString('id-ID', dateOpts);
            if (liveTimeEl) liveTimeEl.innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        updateClock();
        setInterval(updateClock, 1000);

        // ============================================================
        // SOUND - generated via Web Audio API, no files needed
        // ============================================================
        function getAudioContext() {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            // iOS/Android may suspend context until a user gesture resumes it
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            return audioCtx;
        }

        function playBeep(success) {
            if (!soundEnabled) return;
            try {
                var ctx = getAudioContext();
                var oscillator = ctx.createOscillator();
                var gainNode = ctx.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(ctx.destination);
                if (success) {
                    // Two short rising tones for success
                    oscillator.frequency.setValueAtTime(880, ctx.currentTime);
                    oscillator.frequency.setValueAtTime(1320, ctx.currentTime + 0.1);
                    oscillator.type = 'sine';
                    gainNode.gain.setValueAtTime(0.4, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                    oscillator.start(ctx.currentTime);
                    oscillator.stop(ctx.currentTime + 0.35);
                } else {
                    // Low descending tone for error
                    oscillator.frequency.setValueAtTime(440, ctx.currentTime);
                    oscillator.frequency.setValueAtTime(220, ctx.currentTime + 0.15);
                    oscillator.type = 'sawtooth';
                    gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                    oscillator.start(ctx.currentTime);
                    oscillator.stop(ctx.currentTime + 0.4);
                }
            } catch(e) {}
        }

        btnToggleSound.addEventListener('click', function() {
            soundEnabled = !soundEnabled;
            btnToggleSound.innerHTML = soundEnabled
                ? '<i class="bi bi-volume-up"></i>'
                : '<i class="bi bi-volume-mute"></i>';
        });

        // ============================================================
        // SCAN PROCESSING
        // ============================================================
        function processScanValue(value) {
            if (isProcessing) return;
            isProcessing = true;

            scannerBox.className = 'scanner-card rounded-4 p-4 text-center loading';
            visualIndicator.className = 'visual-indicator loading-state';
            indicatorIcon.className = 'bi bi-arrow-repeat fs-1 spinner-inline';
            statusTitle.innerText = 'Memproses...';
            statusText.innerText = 'Sedang mencocokkan data absensi siswa...';

            if (isCameraActive) {
                cameraOverlayLoading.classList.add('show');
            }

            fetch("{{ route('admin.attendance.scan.post') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ scan_value: value })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message) });
                }
                return response.json();
            })
            .then(data => {
                playBeep(true);
                updateScannerUI(true, 'Absensi Berhasil', data.message || 'Scan berhasil');
                showStudentProfile(data.data);
                addToRecentFeed(data.data);
            })
            .catch(err => {
                playBeep(false);
                updateScannerUI(false, 'Absensi Gagal', err.message || 'Terjadi kesalahan');
            })
            .finally(() => {
                isProcessing = false;
                cameraOverlayLoading.classList.remove('show');
                if (scannerTimeout) clearTimeout(scannerTimeout);
                scannerTimeout = setTimeout(() => {
                    resetScannerUI();
                }, 3500);
            });
        }

        function updateScannerUI(success, title, message) {
            scannerBox.className = 'scanner-card rounded-4 p-4 text-center ' + (success ? 'active' : 'error');
            visualIndicator.className = 'visual-indicator ' + (success ? 'success' : 'error');
            indicatorIcon.className = success ? 'bi bi-check-circle fs-1' : 'bi bi-x-circle fs-1';
            statusTitle.innerText = title;
            statusText.innerText = message;
        }

        function resetScannerUI() {
            scannerBox.className = 'scanner-card rounded-4 p-4 text-center';
            visualIndicator.className = 'visual-indicator idle';
            indicatorIcon.className = 'bi bi-qr-code-scan fs-1';
            if (currentMethod === 'camera') {
                statusTitle.innerText = 'Siap Memindai';
                statusText.innerText = 'Arahkan QR Code ke kamera untuk scan otomatis.';
            } else {
                statusTitle.innerText = 'Siap Memindai';
                statusText.innerText = 'Scan QR Code menggunakan USB Scanner.';
            }
        }

        function showStudentProfile(data) {
            if (!data) return;
            studentProfileContainer.style.display = 'block';
            profileAvatar.innerText = (data.nama || '?').charAt(0).toUpperCase();
            profileName.innerText = data.nama || '-';
            profileNis.innerText = data.nis || '-';
            profileKelas.innerText = data.kelas || '-';
            profileJurusan.innerText = data.jurusan || '-';
            profileStatus.innerText = data.status || '-';
            var now = new Date();
            profileWaktu.innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            profileBackdrop.classList.add('show');
        }

        function hideStudentProfile() {
            profileBackdrop.classList.remove('show');
            studentProfileContainer.style.display = 'none';
        }

        function addToRecentFeed(data) {
            if (!data) return;
            var noMsg = getEl('noScansYet');
            if (noMsg) noMsg.remove();

            var statusIcon = (data.status && data.status.toLowerCase() === 'terlambat')
                ? '<i class="bi bi-exclamation-triangle text-warning"></i>'
                : '<i class="bi bi-check-circle text-success"></i>';

            var item = document.createElement('div');
            item.className = 'list-group-item d-flex align-items-center gap-2';
            item.innerHTML = '<div class="flex-shrink-0">' + statusIcon + '</div>' +
                '<div class="flex-grow-1"><strong>' + (data.nama || 'Unknown') + '</strong><br><small class="text-muted">' +
                (data.nis || '-') + ' &middot; ' + (data.kelas || '-') + '</small></div>' +
                '<small class="text-nowrap text-muted">' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + '</small>';

            recentScansList.insertBefore(item, recentScansList.firstChild);

            if (data.status && data.status.toLowerCase() === 'terlambat') {
                const telatEl = getEl('mobile-stat-terlambat');
                if (telatEl) telatEl.innerText = parseInt(telatEl.innerText || 0) + 1;
            } else {
                const hadirEl = getEl('mobile-stat-hadir');
                if (hadirEl) hadirEl.innerText = parseInt(hadirEl.innerText || 0) + 1;
            }
        }

        // ============================================================
        // TAB SWITCHING
        // ============================================================
        function switchMethod(method) {
            if (method === currentMethod) return;
            currentMethod = method;

            // Only toggle tabs inside the same visible container
            var visibleTab = getEl('tabCamera');
            if (visibleTab) {
                visibleTab.closest('.method-tabs').querySelectorAll('.method-tab').forEach(function(t) {
                    t.classList.remove('active');
                });
            }
            if (method === 'camera') {
                if (tabCamera) tabCamera.classList.add('active');
            } else {
                if (tabUsb) tabUsb.classList.add('active');
            }

            if (method === 'camera') {
                usbMode.style.display = 'none';
                cameraMode.style.display = 'block';
                usbScannerInput.blur();
                usbScannerInput.value = '';
                resetScannerUI();
            } else {
                cameraMode.style.display = 'none';
                usbMode.style.display = 'block';
                if (html5QrCode) {
                    stopCameraScanner(false);
                }
                setTimeout(function() {
                    usbScannerInput.focus();
                }, 100);
                resetScannerUI();
            }
        }

        tabCamera.addEventListener('click', function() {
            switchMethod('camera');
        });
        tabUsb.addEventListener('click', function() {
            switchMethod('usb');
        });

        // ============================================================
        // CAMERA ENGINE
        // ============================================================
        function enumerateCameras() {
            // Use Html5Qrcode.getCameras() which works better across mobile browsers
            return Html5Qrcode.getCameras().then(function(cameras) {
                availableCameras = cameras || [];
                return availableCameras;
            }).catch(function() {
                availableCameras = [];
                return availableCameras;
            });
        }

        function populateCameraSelector() {
            cameraSelector.innerHTML = '';
            if (availableCameras.length <= 1) {
                cameraSelector.classList.add('d-none');
                return;
            }
            cameraSelector.classList.remove('d-none');

            availableCameras.forEach(function(cam, idx) {
                var opt = document.createElement('option');
                // Html5Qrcode.getCameras() returns {id, label}, not {deviceId, label}
                opt.value = cam.id;
                opt.text = cam.label || ('Kamera ' + (idx + 1));
                if (cam.id === selectedCameraId) {
                    opt.selected = true;
                }
                cameraSelector.appendChild(opt);
            });
        }

        function applyMirrorFix() {
            var videoEl = qrReaderContainer.querySelector('video');
            if (!videoEl) return;

            function forceNonMirror() {
                var el = qrReaderContainer.querySelector('video');
                if (!el) return;
                var t = el.style.transform || '';
                var wt = el.style.webkitTransform || '';
                if (t.indexOf('scaleX(-') !== -1 || t.indexOf('scale(-1') !== -1 ||
                    wt.indexOf('scaleX(-') !== -1 || wt.indexOf('scale(-1') !== -1) {
                    el.style.setProperty('transform', 'scaleX(1)', 'important');
                    el.style.setProperty('-webkit-transform', 'scaleX(1)', 'important');
                    el.style.setProperty('-moz-transform', 'scaleX(1)', 'important');
                }
            }

            forceNonMirror();

            var mirrorObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        forceNonMirror();
                    }
                });
            });
            mirrorObserver.observe(videoEl, { attributes: true, attributeFilter: ['style'] });

            if (window._nonmirrorInterval) clearInterval(window._nonmirrorInterval);
            window._nonmirrorInterval = setInterval(function() {
                if (!isCameraActive) {
                    clearInterval(window._nonmirrorInterval);
                    return;
                }
                forceNonMirror();
            }, 1000);
        }

        function startCameraScanner() {
            if (isCameraActive) return;

            btnStartCamera.classList.add('d-none');
            btnStopCamera.classList.remove('d-none');
            cameraActiveControls.classList.remove('d-none');
            cameraOverlayLoading.classList.add('show');
            cameraPlaceholder.style.display = 'none';

            qrReaderContainer.innerHTML = '';
            // Html5Qrcode uses getElementById internally, which always finds the
            // first element in the DOM (the hidden desktop copy).
            // Give the VISIBLE container a temporary unique ID so the library
            // injects the video stream into the correct (visible) element.
            var qrScanId = 'qr-scan-active';
            qrReaderContainer.id = qrScanId;
            html5QrCode = new Html5Qrcode(qrScanId);

            var containerW = cameraContainer.offsetWidth;
            if (containerW < 200) containerW = 280;
            var qrSize = Math.round(containerW * 0.65);

            // getCameras() triggers the permission prompt on mobile and returns real device IDs.
            // Starting with a device ID string is more reliable than facingMode constraints
            // across Android and iOS browsers.
            Html5Qrcode.getCameras().then(function(cameras) {
                if (!cameras || cameras.length === 0) {
                    throw new Error('Tidak ada kamera yang ditemukan di perangkat ini');
                }

                availableCameras = cameras;
                populateCameraSelector();

                // Pick camera: use selectedCameraId if set, otherwise prefer rear camera
                var cameraId = selectedCameraId;
                if (!cameraId) {
                    var rearCam = cameras.find(function(c) {
                        var label = (c.label || '').toLowerCase();
                        return label.includes('back') || label.includes('rear') ||
                               label.includes('environment') || label.includes('belakang');
                    });
                    // On mobile, the last camera in the list is typically rear
                    cameraId = rearCam ? rearCam.id : cameras[cameras.length - 1].id;
                    selectedCameraId = cameraId;
                    cameraSelector.value = cameraId;
                }

                return html5QrCode.start(
                    cameraId,
                    { fps: 10, qrbox: { width: qrSize, height: qrSize } },
                    function(decodedText) {
                        if (!isProcessing) {
                            processScanValue(decodedText);
                        }
                    },
                    function(err) { /* ignore scan errors */ }
                );
            }).then(function() {
                isCameraActive = true;
                cameraOverlayLoading.classList.remove('show');
                applyMirrorFix();
            }).catch(function(err) {
                console.error('Camera start error:', err);
                stopCameraScanner(true);
                var msg = (err && err.message) ? err.message
                        : (typeof err === 'string' ? err : 'Periksa izin kamera di browser Anda');
                cameraActiveControls.classList.remove('d-none');
                updateScannerUI(false, 'Gagal Membuka Kamera', msg);
            });
        }

        function stopCameraScanner(resetUI) {
            // Restore the original ID so getEl() / CSS work correctly after stop
            if (qrReaderContainer) qrReaderContainer.id = 'qr-reader-container';
            if (html5QrCode) {
                try {
                    html5QrCode.stop().then(function() {
                        html5QrCode.clear();
                        html5QrCode = null;
                    }).catch(function(e) {});
                } catch(e) {}
            }
            isCameraActive = false;
            cameraOverlayLoading.classList.remove('show');

            if (resetUI !== false) {
                btnStartCamera.classList.remove('d-none');
                btnStopCamera.classList.add('d-none');
                cameraActiveControls.classList.add('d-none');
                cameraPlaceholder.style.display = 'block';
            }
        }

        btnStartCamera.addEventListener('click', function() {
            startCameraScanner();
        });

        btnStopCamera.addEventListener('click', function() {
            stopCameraScanner(true);
        });

        btnSwitchCamera.addEventListener('click', function() {
            if (availableCameras.length <= 1) return;
            var currentIdx = availableCameras.findIndex(function(cam) {
                return cam.id === selectedCameraId;
            });
            var nextIdx = (currentIdx + 1) % availableCameras.length;
            selectedCameraId = availableCameras[nextIdx].id;
            cameraSelector.value = selectedCameraId;

            if (isCameraActive) {
                stopCameraScanner(false);
                setTimeout(function() {
                    startCameraScanner();
                }, 300);
            }
        });

        cameraSelector.addEventListener('change', function() {
            selectedCameraId = this.value;
            if (isCameraActive) {
                stopCameraScanner(false);
                setTimeout(function() {
                    startCameraScanner();
                }, 300);
            }
        });

        // ============================================================
        // USB SCANNER INPUT
        // ============================================================
        var usbBuffer = '';
        var usbTimer = null;

        usbScannerInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                var value = usbBuffer.trim();
                if (value) {
                    processScanValue(value);
                }
                usbBuffer = '';
                usbScannerInput.value = '';
                e.preventDefault();
                return;
            }

            if (e.key.length === 1) {
                usbBuffer += e.key;
                clearTimeout(usbTimer);
                usbTimer = setTimeout(function() {
                    if (usbBuffer.trim()) {
                        processScanValue(usbBuffer.trim());
                    }
                    usbBuffer = '';
                    usbScannerInput.value = '';
                }, 150);
            }
        });

        usbScannerInput.addEventListener('blur', function() {
            setTimeout(function() {
                if (currentMethod === 'usb') {
                    usbScannerInput.focus();
                }
            }, 200);
        });

        // ============================================================
        // INIT
        // ============================================================
        // Camera permission is only requested when the user explicitly clicks Start.
        // Do NOT call getCameras() here — it triggers a permission prompt on mobile
        // before the user has indicated they want to use the camera.
    </script>
    @endpush
</x-app-layout>