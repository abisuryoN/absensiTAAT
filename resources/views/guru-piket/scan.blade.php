<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Absensi Piket</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { background: #f4f6fc; }
        .piket-header {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white; padding: 1rem 1.5rem;
        }
        .result-card { display: none; border-radius: 12px; }
        .result-card.success { border-left: 4px solid #28a745; }
        .result-card.error   { border-left: 4px solid #dc3545; }
        .scan-count-badge {
            background: rgba(255,255,255,0.2); border-radius: 20px;
            padding: 2px 12px; font-size: 0.8rem;
        }
        #recent-list { max-height: 280px; overflow-y: auto; }
        .recent-item { padding: 8px 12px; border-bottom: 1px solid #f0f0f0; font-size: 0.85rem; }
        .recent-item:last-child { border-bottom: none; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .dot-hadir     { background: #28a745; }
        .dot-terlambat { background: #ffc107; }

        /* ── Mode Tabs ── */
        .method-tabs {
            display: flex; border-radius: 10px; overflow: hidden;
            border: 1px solid #dee2e6; background: #f8f9fa;
        }
        .method-tab {
            flex: 1; padding: 10px 16px; text-align: center; cursor: pointer;
            font-weight: 600; font-size: 0.9rem; transition: all 0.25s ease;
            border: none; background: transparent; color: #6c757d;
        }
        .method-tab:hover { background: rgba(var(--bs-primary-rgb), 0.05); }
        .method-tab.active {
            background: #fff; color: var(--bs-primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .method-tab.active i { color: var(--bs-primary); }
        .method-tab i { font-size: 1.1rem; display: block; margin-bottom: 2px; }

        /* ── Camera ── */
        .camera-region {
            position: relative; background: #000;
            border-radius: 12px; overflow: hidden; min-height: 260px;
        }
        #camera-container { width: 100%; }
        #camera-container video {
            width: 100% !important; height: auto !important;
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
            display: block;
        }
        #camera-container video[style*="scaleX"] {
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
        }
        #camera-container > div > video { transform: scaleX(1) !important; }
        #camera-container > div { transform: none !important; }
        #camera-container canvas { display: none !important; }
        #camera-container img    { display: none !important; }
        #camera-overlay-loading {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); border-radius: 12px; display: none;
            align-items: center; justify-content: center; flex-direction: column; color: #fff;
        }
        #camera-overlay-loading.show { display: flex; }
        .btn-switch-camera {
            position: absolute; bottom: 12px; right: 12px; z-index: 10;
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
            color: #fff; border-radius: 50%; width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background 0.2s; backdrop-filter: blur(4px);
        }
        .btn-switch-camera:hover { background: rgba(255,255,255,0.3); }
        #camera-selector {
            position: absolute; bottom: 12px; left: 12px; right: 60px; z-index: 10;
            background: rgba(0,0,0,0.55); color: #fff; border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px; padding: 4px 8px; font-size: 0.78rem;
            backdrop-filter: blur(4px); max-width: 220px;
        }
        #camera-selector option { background: #1a1a2e; color: #fff; }

        /* ── USB Scanner ── */
        #usb-scanner-input { font-size: 1.1rem; letter-spacing: 1px; }
        .usb-indicator {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; background: #f0f9ff;
            border: 1px solid #bae6fd; border-radius: 8px; margin-bottom: 12px;
        }
        .usb-indicator .pulse-dot {
            width: 10px; height: 10px; border-radius: 50%; background: #22c55e;
            animation: pulse-green 1.5s infinite;
        }
        @keyframes pulse-green {
            0%,100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.5; transform: scale(0.85); }
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="piket-header d-flex align-items-center justify-content-between">
        <div>
            <a href="{{ route('piket.dashboard') }}" class="text-white text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
            <h5 class="mb-0 fw-bold mt-1">Scan Absensi Gerbang</h5>
        </div>
        <div>
            <span class="piket-badge scan-count-badge">
                Petugas: <strong>{{ $namaLengkap }}</strong>
            </span>
        </div>
    </div>

    <div class="container-fluid p-3">

        {{-- Mode Tabs --}}
        <div class="method-tabs mb-3">
            <button type="button" class="method-tab active" id="tabCamera" onclick="switchTab('camera')">
                <i class="bi bi-camera-video"></i> Kamera
            </button>
            <button type="button" class="method-tab" id="tabUsb" onclick="switchTab('usb')">
                <i class="bi bi-usb-symbol"></i> USB Scanner
            </button>
        </div>

        {{-- Camera Panel --}}
        <div id="panelCamera" class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body p-3">
                <div class="camera-region mb-3" id="camera-wrap">
                    <div id="camera-container"></div>
                    <div id="camera-overlay-loading">
                        <div class="spinner-border text-light mb-2" style="width:2rem;height:2rem;"></div>
                        <small>Memulai kamera...</small>
                    </div>
                    <select id="camera-selector" style="display:none;"></select>
                    <button class="btn-switch-camera" id="btn-switch-cam" style="display:none;" title="Ganti kamera">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary flex-fill" id="btn-start-camera" type="button">
                        <i class="bi bi-play-fill me-1"></i> Mulai Kamera
                    </button>
                    <button class="btn btn-outline-secondary flex-fill" id="btn-stop-camera" type="button" style="display:none;">
                        <i class="bi bi-stop-fill me-1"></i> Stop
                    </button>
                </div>
                <div class="form-text small mt-2 text-center text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Arahkan kamera ke QR code / barcode siswa. Scan otomatis.
                </div>
            </div>
        </div>

        {{-- USB Scanner Panel --}}
        <div id="panelUsb" class="card border-0 shadow-sm rounded-3 mb-3" style="display:none;">
            <div class="card-body p-3">
                <div class="usb-indicator">
                    <span class="pulse-dot"></span>
                    <span class="small text-primary fw-semibold">USB Scanner siap — arahkan ke kartu/barcode siswa</span>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                    <input
                        type="text"
                        id="usb-scanner-input"
                        class="form-control"
                        placeholder="Scan barcode / QR di sini..."
                        autocomplete="off"
                    >
                </div>
                <div class="form-text small mt-1">
                    <i class="bi bi-info-circle me-1"></i>
                    Input otomatis diproses saat scanner mengirim Enter.
                </div>
            </div>
        </div>

        {{-- Result Card --}}
        <div class="card result-card border-0 shadow-sm mb-3" id="result-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <img id="result-photo" src="" alt="Foto" class="rounded-circle"
                         width="56" height="56" style="object-fit:cover; display:none;">
                    <div id="result-avatar"
                         class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                         style="width:56px;height:56px;font-size:1.3rem;flex-shrink:0">?</div>
                    <div class="flex-fill">
                        <div class="fw-bold" id="result-name">-</div>
                        <div class="small text-muted" id="result-detail">-</div>
                        <div class="mt-1" id="result-status-badge"></div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold" id="result-time">-</div>
                        <div class="small text-muted">WIB</div>
                    </div>
                </div>
                <div id="result-message" class="mt-2 small fw-semibold"></div>
            </div>
        </div>

        {{-- Error Card --}}
        <div class="card result-card error border-0 shadow-sm mb-3 bg-danger bg-opacity-10" id="error-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    <span id="error-message" class="text-danger fw-semibold small"></span>
                </div>
            </div>
        </div>

        {{-- Riwayat scan sesi ini --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold small">
                    <i class="bi bi-clock-history me-1 text-primary"></i>
                    Scan Sesi Ini (<span id="scan-count">0</span>)
                </span>
                <a href="{{ route('piket.rekap') }}" class="btn btn-outline-primary btn-sm" style="font-size:0.75rem;">
                    Lihat Semua
                </a>
            </div>
            <div id="recent-list">
                <div class="text-center text-muted py-4 small" id="empty-recent">
                    <i class="bi bi-inbox d-block mb-1"></i>Belum ada scan
                </div>
            </div>
        </div>

    </div><!-- /container -->

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

    // USB debounce
    let usbBuffer = '';
    let usbTimer  = null;

    // DOM refs
    const resultCard  = document.getElementById('result-card');
    const errorCard   = document.getElementById('error-card');
    const recentList  = document.getElementById('recent-list');
    const emptyRecent = document.getElementById('empty-recent');
    const scanCount   = document.getElementById('scan-count');
    const usbInput    = document.getElementById('usb-scanner-input');
    const btnStart    = document.getElementById('btn-start-camera');
    const btnStop     = document.getElementById('btn-stop-camera');
    const btnSwitch   = document.getElementById('btn-switch-cam');
    const camSelector = document.getElementById('camera-selector');
    const overlay     = document.getElementById('camera-overlay-loading');

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
            // Stop camera if running
            if (scanning) stopCamera();
            setTimeout(() => usbInput.focus(), 100);
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
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.data);
                addToRecent(data.data);
            } else {
                showError(data.message);
            }
        })
        .catch(() => showError('Terjadi kesalahan jaringan. Coba lagi.'))
        .finally(() => { processingQr = false; });
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
        cameras.forEach((cam, i) => {
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

        const idx    = camIdx ?? currentCamIdx;
        const camera = cameras[idx];
        camSelector.value = idx;

        try {
            await html5QrCode.start(
                camera.id,
                { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                (decodedText) => {
                    if (processingQr) return;
                    processingQr = true;
                    processScanValue(decodedText);
                },
                () => {} // ignore per-frame errors
            );
            scanning = true;
            overlay.classList.remove('show');
            btnStop.style.display  = '';
            btnStart.style.display = 'none';
        } catch (e) {
            overlay.classList.remove('show');
            btnStart.style.display = '';
            showError('Gagal memulai kamera: ' + (e.message || e));
            scanning = false;
        }
    }

    async function stopCamera() {
        if (html5QrCode && scanning) {
            try { await html5QrCode.stop(); } catch (_) {}
        }
        scanning = false;
        btnStop.style.display  = 'none';
        btnStart.style.display = '';
    }

    async function switchCamera() {
        if (cameras.length <= 1) return;
        currentCamIdx = (currentCamIdx + 1) % cameras.length;
        if (scanning) await stopCamera();
        await startCamera(currentCamIdx);
    }

    btnStart.addEventListener('click', async () => {
        await getCameras();
        await startCamera(currentCamIdx);
    });
    btnStop.addEventListener('click', stopCamera);
    btnSwitch.addEventListener('click', switchCamera);
    camSelector.addEventListener('change', async () => {
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
        // Hardware USB scanner fires keystrokes very fast (~5ms apart)
        usbBuffer += e.key;
        clearTimeout(usbTimer);
        usbTimer = setTimeout(() => {
            if (usbBuffer.trim()) {
                processScanValue(usbBuffer.trim());
            }
            usbBuffer = '';
            usbInput.value = '';
        }, 150);
    });

    usbInput.addEventListener('blur', () => {
        setTimeout(() => {
            if (currentTab === 'usb') usbInput.focus();
        }, 200);
    });

    // ================================================================
    // RESULT DISPLAY
    // ================================================================
    function hideCards() {
        resultCard.style.display = 'none';
        errorCard.style.display  = 'none';
        resultCard.classList.remove('success');
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

        const statusColors = { 'Hadir': 'success', 'Terlambat': 'warning' };
        const color = statusColors[d.status] || 'secondary';
        document.getElementById('result-status-badge').innerHTML =
            `<span class="badge bg-${color}">${d.status}</span>`;
        document.getElementById('result-message').textContent =
            `Absensi berhasil dicatat oleh: ${d.petugas}`;
        document.getElementById('result-message').className = 'mt-2 small fw-semibold text-success';

        resultCard.classList.add('success');
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

        const dotClass = d.status === 'Hadir' ? 'dot-hadir' : 'dot-terlambat';
        const item = document.createElement('div');
        item.className = 'recent-item d-flex justify-content-between align-items-center';
        item.innerHTML = `
            <div>
                <span class="status-dot ${dotClass}"></span>
                <strong>${d.name}</strong>
                <span class="text-muted ms-1">${d.class}</span>
            </div>
            <div class="text-muted">${d.time}&nbsp;
                <span class="badge bg-${d.status==='Hadir'?'success':'warning'} bg-opacity-75">
                    ${d.status}
                </span>
            </div>`;
        recentList.insertBefore(item, recentList.firstChild);
    }
    </script>
</body>
</html>
