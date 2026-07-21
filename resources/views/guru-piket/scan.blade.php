<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Absensi Piket</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        :root {
            --piket-primary: #1a73e8;
            --piket-dark: #0d47a1;
        }
        * { box-sizing: border-box; }
        body { background: #eef2ff; min-height: 100vh; font-family: inherit; }

        /* Header */
        .piket-header {
            background: linear-gradient(135deg, var(--piket-primary) 0%, var(--piket-dark) 100%);
            color: white;
            padding: 1rem 1.25rem 3.5rem;
            position: relative;
            overflow: hidden;
        }
        .piket-header::before {
            content: ''; position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }
        .piket-header::after {
            content: ''; position: absolute;
            bottom: -60px; left: -30px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .piket-header-inner { position: relative; z-index: 1; }
        .piket-petugas-badge {
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.8rem;
            backdrop-filter: blur(4px);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .live-clock { font-size: 0.72rem; color: rgba(255,255,255,0.75); letter-spacing: 0.5px; }

        /* Main Content */
        .main-content {
            margin-top: -2.2rem;
            padding: 0 1rem 2rem;
            position: relative;
            z-index: 2;
        }

        /* Camera Section Wrapper - consistent width for tabs, video, buttons */
        .camera-section-wrapper {
            max-width: 640px;
            margin: 0 auto 1rem;
        }

        /* Mode Tabs */
        .method-tabs {
            display: flex;
            background: #fff;
            border-radius: 14px;
            padding: 5px;
            box-shadow: 0 4px 20px rgba(26,115,232,0.12);
        }
        .method-tab {
            flex: 1;
            padding: 14px 16px;
            min-height: 52px;
            text-align: center; cursor: pointer;
            font-weight: 600; font-size: 0.85rem;
            border: none; background: transparent;
            color: #94a3b8; border-radius: 10px;
            transition: all 0.25s ease;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            line-height: 1;
        }
        .method-tab i { font-size: 1.1rem; }
        .method-tab.active {
            background: linear-gradient(135deg, var(--piket-primary), var(--piket-dark));
            color: #fff;
            box-shadow: 0 4px 12px rgba(26,115,232,0.35);
        }

        /* Card Base */
        .scan-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .scan-card-header {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
        }
        .scan-card-body { padding: 14px; }

        /* Camera Region */
        .camera-region {
            position: relative;
            background: #0f172a;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 4 / 3;
            width: 100%;
        }
        #camera-container { 
            width: 100%; 
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #camera-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
            display: block;
        }
        #camera-container > div { 
            transform: none !important;
            width: 100% !important;
            height: 100% !important;
        }
        #camera-container canvas { display: none !important; }
        #camera-container img    { display: none !important; }

        /* Scan Frame Overlay */
        .scan-frame-overlay {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            pointer-events: none;
        }
        .scan-frame { width: 200px; height: 200px; position: relative; }
        .scan-frame::before, .scan-frame::after,
        .scan-frame .corner-br, .scan-frame .corner-bl {
            content: ''; position: absolute;
            width: 28px; height: 28px;
            border-color: rgba(255,255,255,0.9);
            border-style: solid;
        }
        .scan-frame::before  { top: 0; left: 0;   border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
        .scan-frame::after   { top: 0; right: 0;  border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
        .scan-frame .corner-br { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }
        .scan-frame .corner-bl { bottom: 0; left: 0;  border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }
        .scan-line {
            position: absolute;
            left: 4px; right: 4px; height: 2px;
            background: linear-gradient(90deg, transparent, #38bdf8, transparent);
            top: 10%;
            animation: scan-sweep 2s ease-in-out infinite;
        }
        @keyframes scan-sweep {
            0%   { top: 10%; opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { top: 88%; opacity: 0; }
        }
        .scan-hint {
            position: absolute; bottom: 12px; left: 0; right: 0;
            text-align: center; color: rgba(255,255,255,0.75); font-size: 0.72rem;
        }

        #camera-overlay-loading {
            position: absolute; inset: 0;
            background: rgba(15,23,42,0.8);
            border-radius: 12px;
            display: none;
            align-items: center; justify-content: center;
            flex-direction: column; color: #fff; gap: 8px;
        }
        #camera-overlay-loading.show { display: flex; }

        .btn-switch-camera {
            position: absolute; bottom: 12px; right: 12px; z-index: 10;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff; border-radius: 50%;
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background 0.2s;
            backdrop-filter: blur(4px);
        }
        .btn-switch-camera:hover { background: rgba(255,255,255,0.3); }

        #camera-selector {
            position: absolute; bottom: 12px; left: 12px; z-index: 10;
            background: rgba(15,23,42,0.6); color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px; padding: 4px 8px; font-size: 0.75rem;
            backdrop-filter: blur(4px); max-width: 200px;
        }
        #camera-selector option { background: #1e293b; color: #fff; }

        .btn-camera-action {
            flex: 1;
            padding: 14px 20px;
            min-height: 50px;
            border-radius: 10px;
            font-weight: 600; font-size: 0.9rem; border: none;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            cursor: pointer; transition: all 0.2s;
            line-height: 1;
        }
        .btn-camera-start {
            background: linear-gradient(135deg, var(--piket-primary), var(--piket-dark));
            color: #fff; box-shadow: 0 4px 12px rgba(26,115,232,0.3);
        }
        .btn-camera-start:hover { box-shadow: 0 6px 16px rgba(26,115,232,0.45); transform: translateY(-1px); }
        .btn-camera-stop { background: #f1f5f9; color: #475569; }
        .btn-camera-stop:hover { background: #e2e8f0; }

        /* USB */
        .usb-indicator {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 14px;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1px solid #bae6fd;
            border-radius: 10px; margin-bottom: 12px;
        }
        .pulse-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: #22c55e; flex-shrink: 0;
            animation: pulse-green 1.5s infinite;
        }
        @keyframes pulse-green {
            0%,100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.5; transform: scale(0.85); }
        }
        #usb-scanner-input { font-size: 1rem; letter-spacing: 1px; }

        /* Result Cards */
        .result-card { display: none; border-radius: 16px; overflow: hidden; margin-bottom: 1rem; }
        .result-success-card {
            background: #fff;
            border: 1.5px solid #d1fae5;
            box-shadow: 0 4px 20px rgba(13,179,123,0.12);
        }
        .result-success-card .result-header-bar {
            background: linear-gradient(90deg, #0db37b, #059669);
            height: 4px;
        }
        .result-error-card {
            background: #fff;
            border: 1.5px solid #fecaca;
            box-shadow: 0 4px 20px rgba(239,68,68,0.1);
        }
        .result-error-card .result-header-bar {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            height: 4px;
        }
        .result-avatar {
            width: 56px; height: 56px; border-radius: 50%;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: #fff; font-weight: 700; font-size: 1.4rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .result-photo { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
        .badge-hadir     { background: #dcfce7; color: #166534; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .badge-terlambat { background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }

        /* Recent List */
        #recent-list { max-height: 300px; overflow-y: auto; }
        .recent-item {
            padding: 10px 14px;
            border-bottom: 1px solid #f8fafc;
            display: flex; justify-content: space-between; align-items: center;
            transition: background 0.15s;
        }
        .recent-item:hover { background: #f8fafc; }
        .recent-item:last-child { border-bottom: none; }
        .recent-avatar-sm {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: #fff; font-weight: 700; font-size: 0.8rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .dot-hadir     { background: #22c55e; }
        .dot-terlambat { background: #f59e0b; }

        .counter-pill {
            background: linear-gradient(135deg, var(--piket-primary), var(--piket-dark));
            color: #fff; font-size: 0.7rem; font-weight: 700;
            padding: 2px 10px; border-radius: 20px;
            min-width: 26px; text-align: center;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="piket-header">
        <div class="piket-header-inner d-flex align-items-start justify-content-between">
            <div>
                <a href="{{ route('piket.dashboard') }}" class="text-white text-decoration-none small opacity-75 d-inline-flex align-items-center gap-1 mb-2">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <h5 class="mb-0 fw-bold">Scan Absensi Gerbang</h5>
                <div class="live-clock mt-1" id="live-clock">--:--:--</div>
            </div>
            <div class="text-end">
                <div class="piket-petugas-badge">
                    <i class="bi bi-person-badge"></i>
                    <strong>{{ $namaLengkap }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">

        {{-- Camera Section - wrapped for consistent width --}}
        <div class="camera-section-wrapper">
            {{-- Mode Tabs --}}
            <div class="method-tabs">
                <button type="button" class="method-tab active" id="tabCamera" onclick="switchTab('camera')">
                    <i class="bi bi-camera-video-fill"></i> Kamera
                </button>
                <button type="button" class="method-tab" id="tabUsb" onclick="switchTab('usb')">
                    <i class="bi bi-usb-symbol"></i> USB Scanner
                </button>
            </div>

            {{-- Result: Success --}}
            <div class="result-card result-success-card" id="result-card">
                <div class="result-header-bar"></div>
                <div class="p-3">
                    <div class="d-flex align-items-center gap-3">
                        <img id="result-photo" src="" alt="Foto" class="result-photo" style="display:none;">
                        <div id="result-avatar" class="result-avatar">?</div>
                        <div class="flex-fill">
                            <div class="fw-bold" id="result-name">-</div>
                            <div class="small text-muted" id="result-detail">-</div>
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <div id="result-status-badge"></div>
                                <span class="text-muted small">·</span>
                                <span class="fw-bold text-dark" id="result-time">-</span>
                                <span class="small text-muted">WIB</span>
                            </div>
                        </div>
                        <i class="bi bi-check-circle-fill text-success fs-4 flex-shrink-0"></i>
                    </div>
                    <div id="result-message" class="mt-2 small text-success fw-semibold d-flex align-items-center gap-1">
                        <i class="bi bi-shield-check"></i>
                        <span id="result-message-text"></span>
                    </div>
                </div>
            </div>

            {{-- Result: Error --}}
            <div class="result-card result-error-card" id="error-card">
                <div class="result-header-bar"></div>
                <div class="p-3 d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-semibold text-danger small" id="error-message"></div>
                    </div>
                </div>
            </div>

            {{-- Camera Panel --}}
            <div id="panelCamera" class="scan-card">
                <div class="scan-card-header">
                    <span class="fw-semibold small d-flex align-items-center gap-2">
                        <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;" id="cam-status-dot"></span>
                        <span id="cam-status-text">Kamera Tidak Aktif</span>
                    </span>
                    <span class="small text-muted">QR / Barcode</span>
                </div>
                <div class="scan-card-body">
                    <div class="camera-region" id="camera-wrap">
                        <div id="camera-container"></div>
                        <div class="scan-frame-overlay" id="scan-frame-overlay" style="display:none;">
                            <div class="scan-frame">
                                <div class="corner-br"></div>
                                <div class="corner-bl"></div>
                                <div class="scan-line"></div>
                            </div>
                        </div>
                        <div class="scan-hint" id="scan-hint" style="display:none;">
                            Arahkan QR code / barcode ke dalam bingkai
                        </div>
                        <div id="camera-overlay-loading">
                            <div class="spinner-border text-light" style="width:2rem;height:2rem;"></div>
                            <small>Memulai kamera...</small>
                        </div>
                        <select id="camera-selector" style="display:none;"></select>
                        <button class="btn-switch-camera" id="btn-switch-cam" style="display:none;" title="Ganti kamera">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn-camera-action btn-camera-start" id="btn-start-camera" type="button">
                            <i class="bi bi-play-fill"></i> Mulai Kamera
                        </button>
                        <button class="btn-camera-action btn-camera-stop" id="btn-stop-camera" type="button" style="display:none;">
                            <i class="bi bi-stop-fill"></i> Stop
                        </button>
                    </div>
                </div>
            </div>
        </div>{{-- /camera-section-wrapper --}}

        {{-- USB Scanner Panel --}}
        <div id="panelUsb" class="scan-card" style="display:none;">
            <div class="scan-card-header">
                <span class="fw-semibold small d-flex align-items-center gap-2">
                    <i class="bi bi-usb-symbol text-primary"></i> USB Barcode Scanner
                </span>
                <span class="pulse-dot"></span>
            </div>
            <div class="scan-card-body">
                <div class="usb-indicator">
                    <i class="bi bi-broadcast text-primary fs-5"></i>
                    <span class="small text-primary fw-semibold">Scanner siap — scan kartu siswa</span>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-upc-scan text-primary"></i></span>
                    <input
                        type="text"
                        id="usb-scanner-input"
                        class="form-control"
                        placeholder="Scan barcode / QR di sini..."
                        autocomplete="off"
                    >
                </div>
                <div class="form-text small mt-2 text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Diproses otomatis saat scanner mengirim Enter.
                </div>
            </div>
        </div>

        {{-- Riwayat scan sesi ini --}}
        <div class="scan-card">
            <div class="scan-card-header">
                <span class="fw-semibold small d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    Scan Sesi Ini
                    <span class="counter-pill" id="scan-count">0</span>
                </span>
                <a href="{{ route('piket.rekap') }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;border-radius:8px;">
                    Lihat Rekap
                </a>
            </div>
            <div id="recent-list">
                <div class="text-center text-muted py-4 small" id="empty-recent">
                    <i class="bi bi-qr-code d-block mb-2 fs-4 opacity-25"></i>
                    Belum ada scan di sesi ini
                </div>
            </div>
        </div>

    </div>

    <script>
    // ================================================================
    // STATE
    // ================================================================
    const SCAN_URL   = '{{ route("piket.scan.post") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    let currentTab    = 'camera';
    let html5QrCode   = null;
    let cameras       = [];
    let currentCamIdx = 0;
    let scanning      = false;
    let processingQr  = false;
    let count         = 0;

    let usbBuffer = '';
    let usbTimer  = null;

    const resultCard    = document.getElementById('result-card');
    const errorCard     = document.getElementById('error-card');
    const recentList    = document.getElementById('recent-list');
    const emptyRecent   = document.getElementById('empty-recent');
    const scanCount     = document.getElementById('scan-count');
    const usbInput      = document.getElementById('usb-scanner-input');
    const btnStart      = document.getElementById('btn-start-camera');
    const btnStop       = document.getElementById('btn-stop-camera');
    const btnSwitch     = document.getElementById('btn-switch-cam');
    const camSelector   = document.getElementById('camera-selector');
    const overlay       = document.getElementById('camera-overlay-loading');
    const scanOverlay   = document.getElementById('scan-frame-overlay');
    const scanHint      = document.getElementById('scan-hint');
    const camStatusDot  = document.getElementById('cam-status-dot');
    const camStatusText = document.getElementById('cam-status-text');

    // ================================================================
    // LIVE CLOCK
    // ================================================================
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('live-clock').textContent = h + ':' + m + ':' + s + ' WIB';
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ================================================================
    // CAMERA STATUS
    // ================================================================
    function setCamStatus(active) {
        if (active) {
            camStatusDot.style.background = '#22c55e';
            camStatusText.textContent = 'Kamera Aktif';
            scanOverlay.style.display = '';
            scanHint.style.display = '';
        } else {
            camStatusDot.style.background = '#ef4444';
            camStatusText.textContent = 'Kamera Tidak Aktif';
            scanOverlay.style.display = 'none';
            scanHint.style.display = 'none';
        }
    }

    // ================================================================
    // TAB SWITCHING
    // ================================================================
    function switchTab(tab) {
        currentTab = tab;
        document.getElementById('tabCamera').classList.toggle('active', tab === 'camera');
        document.getElementById('tabUsb').classList.toggle('active', tab === 'usb');
        document.getElementById('panelCamera').style.display = tab === 'camera' ? '' : 'none';
        document.getElementById('panelUsb').style.display    = tab === 'usb'    ? '' : 'none';
        if (tab === 'usb') {
            if (scanning) stopCamera();
            setTimeout(function() { usbInput.focus(); }, 100);
        }
    }

    // ================================================================
    // CORE: send scan value to backend
    // ================================================================
    function processScanValue(val) {
        if (!val) return;
        hideCards();
        fetch(SCAN_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ scan_value: val })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showSuccess(data.data);
                addToRecent(data.data);
            } else {
                showError(data.message);
            }
        })
        .catch(function() { showError('Terjadi kesalahan jaringan. Coba lagi.'); })
        .finally(function() { processingQr = false; });
    }

    // ================================================================
    // CAMERA
    // ================================================================
    async function getCameras() {
        try {
            const devices = await Html5Qrcode.getCameras();
            cameras = devices || [];
            updateCameraSelector();
            return cameras;
        } catch (e) {
            console.warn('getCameras error:', e);
            return [];
        }
    }

    function updateCameraSelector() {
        camSelector.innerHTML = '';
        cameras.forEach(function(cam, i) {
            const opt = document.createElement('option');
            opt.value = i;
            opt.textContent = cam.label || ('Kamera ' + (i + 1));
            camSelector.appendChild(opt);
        });
        if (cameras.length > 1) {
            camSelector.style.display = '';
            btnSwitch.style.display   = '';
        } else {
            camSelector.style.display = 'none';
            btnSwitch.style.display   = 'none';
        }
    }

    async function startCamera(camIdx) {
        overlay.classList.add('show');
        btnStart.style.display = 'none';

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode('camera-container');
        }

        if (cameras.length === 0) await getCameras();
        if (cameras.length === 0) {
            overlay.classList.remove('show');
            btnStart.style.display = '';
            showError('Tidak ada kamera yang ditemukan.');
            return;
        }

        const idx    = (camIdx !== undefined && camIdx !== null) ? camIdx : currentCamIdx;
        const camera = cameras[idx];
        camSelector.value = idx;

        try {
            await html5QrCode.start(
                camera.id,
                { 
                    fps: 10, 
                    qrbox: { width: 220, height: 220 },
                    aspectRatio: 1.333,
                    videoConstraints: {
                        width: { ideal: 1280 },
                        height: { ideal: 960 },
                        aspectRatio: { ideal: 1.333 }
                    }
                },
                function(decodedText) {
                    if (processingQr) return;
                    processingQr = true;
                    processScanValue(decodedText);
                },
                function() {}
            );
            scanning = true;
            overlay.classList.remove('show');
            btnStop.style.display  = '';
            btnStart.style.display = 'none';
            setCamStatus(true);
        } catch (e) {
            overlay.classList.remove('show');
            btnStart.style.display = '';
            showError('Gagal memulai kamera: ' + (e.message || e));
            scanning = false;
            setCamStatus(false);
        }
    }

    async function stopCamera() {
        if (html5QrCode && scanning) {
            try { await html5QrCode.stop(); } catch (_e) {}
        }
        scanning = false;
        btnStop.style.display  = 'none';
        btnStart.style.display = '';
        setCamStatus(false);
    }

    async function switchCamera() {
        if (cameras.length <= 1) return;
        currentCamIdx = (currentCamIdx + 1) % cameras.length;
        if (scanning) await stopCamera();
        await startCamera(currentCamIdx);
    }

    btnStart.addEventListener('click', async function() {
        await getCameras();
        await startCamera(currentCamIdx);
    });
    btnStop.addEventListener('click', stopCamera);
    btnSwitch.addEventListener('click', switchCamera);
    camSelector.addEventListener('change', async function() {
        currentCamIdx = parseInt(camSelector.value);
        if (scanning) await stopCamera();
        await startCamera(currentCamIdx);
    });

    // ================================================================
    // USB SCANNER
    // ================================================================
    usbInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = usbInput.value.trim();
            if (val) processScanValue(val);
            usbInput.value = '';
            return;
        }
        usbBuffer += e.key;
        clearTimeout(usbTimer);
        usbTimer = setTimeout(function() {
            if (usbBuffer.trim()) processScanValue(usbBuffer.trim());
            usbBuffer = '';
            usbInput.value = '';
        }, 150);
    });

    usbInput.addEventListener('blur', function() {
        setTimeout(function() {
            if (currentTab === 'usb') usbInput.focus();
        }, 200);
    });

    // ================================================================
    // RESULT DISPLAY
    // ================================================================
    function hideCards() {
        resultCard.style.display = 'none';
        errorCard.style.display  = 'none';
    }

    function showSuccess(d) {
        const photo  = document.getElementById('result-photo');
        const avatar = document.getElementById('result-avatar');
        if (d.photo) {
            photo.src = d.photo;
            photo.style.display  = 'block';
            avatar.style.display = 'none';
        } else {
            photo.style.display  = 'none';
            avatar.style.display = 'flex';
            avatar.textContent   = d.name.charAt(0).toUpperCase();
        }
        document.getElementById('result-name').textContent   = d.name;
        document.getElementById('result-detail').textContent = d.nis + ' · ' + d.class;
        document.getElementById('result-time').textContent   = d.time;

        var color = d.status === 'Hadir' ? 'hadir' : 'terlambat';
        document.getElementById('result-status-badge').innerHTML =
            '<span class="badge-' + color + '">' + d.status + '</span>';
        document.getElementById('result-message-text').textContent =
            'Absensi dicatat oleh: ' + d.petugas;

        resultCard.style.display = 'block';
    }

    function showError(msg) {
        document.getElementById('error-message').textContent = msg;
        errorCard.style.display = 'block';
    }

    function addToRecent(d) {
        count++;
        scanCount.textContent = count;
        emptyRecent.style.display = 'none';

        var dotClass = d.status === 'Hadir' ? 'dot-hadir' : 'dot-terlambat';
        var badgeClass = d.status === 'Hadir' ? 'badge-hadir' : 'badge-terlambat';
        var initial = d.name.charAt(0).toUpperCase();

        var item = document.createElement('div');
        item.className = 'recent-item';
        item.innerHTML =
            '<div class="d-flex align-items-center gap-2">' +
                '<div class="recent-avatar-sm">' + initial + '</div>' +
                '<div>' +
                    '<div class="fw-semibold small">' + d.name + '</div>' +
                    '<div class="text-muted" style="font-size:0.72rem;">' + d.class + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="text-end">' +
                '<span class="' + badgeClass + '">' + d.status + '</span>' +
                '<div class="text-muted mt-1" style="font-size:0.72rem;">' + d.time + '</div>' +
            '</div>';
        recentList.insertBefore(item, recentList.firstChild);
    }
    </script>
</body>
</html>
