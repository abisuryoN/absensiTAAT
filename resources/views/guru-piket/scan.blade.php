<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Absensi Piket</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body { background: #f4f6fc; }
        .piket-header {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white; padding: 1rem 1.5rem;
        }
        #scan-input { font-size: 1.1rem; letter-spacing: 1px; }
        .result-card { display: none; border-radius: 12px; }
        .result-card.success { border-left: 4px solid #28a745; }
        .result-card.error { border-left: 4px solid #dc3545; }
        .scan-count-badge {
            background: rgba(255,255,255,0.2); border-radius: 20px;
            padding: 2px 12px; font-size: 0.8rem;
        }
        #recent-list { max-height: 280px; overflow-y: auto; }
        .recent-item { padding: 8px 12px; border-bottom: 1px solid #f0f0f0; font-size: 0.85rem; }
        .recent-item:last-child { border-bottom: none; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .dot-hadir { background: #28a745; }
        .dot-terlambat { background: #ffc107; }
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
        {{-- Scan Input --}}
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body p-3">
                <label class="form-label fw-semibold small text-muted">
                    <i class="bi bi-qr-code-scan me-1"></i>Arahkan scanner ke barcode / QR siswa
                </label>
                <div class="input-group">
                    <input
                        type="text"
                        id="scan-input"
                        class="form-control"
                        placeholder="Scan atau ketik kode siswa..."
                        autocomplete="off"
                        autofocus
                    >
                    <button class="btn btn-primary" id="btn-scan" type="button">
                        <i class="bi bi-arrow-right-circle"></i>
                    </button>
                </div>
                <div class="form-text small mt-1">
                    <i class="bi bi-info-circle me-1"></i>
                    Input otomatis diproses saat scanner mengirim Enter.
                </div>
            </div>
        </div>

        {{-- Result Display --}}
        <div class="card result-card border-0 shadow-sm mb-3" id="result-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <img id="result-photo" src="" alt="Foto" class="rounded-circle"
                         width="56" height="56" style="object-fit:cover; display:none;">
                    <div id="result-avatar" class="rounded-circle bg-primary text-white d-flex align-items-center
                         justify-content-center fw-bold" style="width:56px;height:56px;font-size:1.3rem;flex-shrink:0">
                        ?
                    </div>
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

        {{-- Error card --}}
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
                <a href="{{ route('piket.rekap') }}" class="btn btn-outline-primary btn-sm" style="font-size:0.75rem">
                    Lihat Semua
                </a>
            </div>
            <div id="recent-list">
                <div class="text-center text-muted py-4 small" id="empty-recent">
                    <i class="bi bi-inbox d-block mb-1"></i>Belum ada scan
                </div>
            </div>
        </div>
    </div>

    <script>
        const scanInput = document.getElementById('scan-input');
        const btnScan   = document.getElementById('btn-scan');
        const resultCard  = document.getElementById('result-card');
        const errorCard   = document.getElementById('error-card');
        const recentList  = document.getElementById('recent-list');
        const emptyRecent = document.getElementById('empty-recent');
        const scanCount   = document.getElementById('scan-count');
        let count = 0;

        // Auto-focus scan input
        scanInput.focus();
        document.addEventListener('click', () => scanInput.focus());

        // Process on Enter or button click
        scanInput.addEventListener('keydown', e => { if (e.key === 'Enter') processScan(); });
        btnScan.addEventListener('click', processScan);

        function processScan() {
            const val = scanInput.value.trim();
            if (!val) return;

            btnScan.disabled = true;
            scanInput.disabled = true;
            hideCards();

            fetch('{{ route("piket.scan.post") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
            .finally(() => {
                scanInput.value = '';
                btnScan.disabled = false;
                scanInput.disabled = false;
                scanInput.focus();
            });
        }

        function hideCards() {
            resultCard.style.display = 'none';
            errorCard.style.display  = 'none';
            resultCard.classList.remove('success');
        }

        function showSuccess(d) {
            const photo = document.getElementById('result-photo');
            const avatar = document.getElementById('result-avatar');
            if (d.photo) {
                photo.src = d.photo;
                photo.style.display = 'block';
                avatar.style.display = 'none';
            } else {
                photo.style.display = 'none';
                avatar.style.display = 'flex';
                avatar.textContent = d.name.charAt(0).toUpperCase();
            }
            document.getElementById('result-name').textContent  = d.name;
            document.getElementById('result-detail').textContent = d.nis + ' · ' + d.class;
            document.getElementById('result-time').textContent  = d.time;

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
                <div class="text-muted">${d.time} &nbsp;<span class="badge bg-${d.status==='Hadir'?'success':'warning'} bg-opacity-75">${d.status}</span></div>
            `;
            recentList.insertBefore(item, recentList.firstChild);
        }
    </script>
</body>
</html>