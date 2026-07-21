<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Absensi — {{ $student->name }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body {
            background: #eef4ff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .qr-header {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white;
            padding: 1rem 1.5rem;
        }
        .qr-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(30,60,120,0.10);
            padding: 2rem 1.5rem;
            max-width: 380px;
            width: 100%;
            margin: 0 auto;
        }
        .qr-image-wrap {
            background: #f8faff;
            border-radius: 16px;
            border: 2px solid #eef4ff;
            padding: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 260px;
            position: relative;
        }
        .qr-image-wrap img,
        .qr-image-wrap svg {
            max-width: 220px;
            max-height: 220px;
            width: 100%;
        }
        #qr-loading {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.85);
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #6c757d;
        }
        #qr-loading.d-none { display: none !important; }
        .timer-bar-wrap {
            background: #e9ecef;
            border-radius: 999px;
            height: 8px;
            overflow: hidden;
            margin-top: 0.75rem;
        }
        #timer-bar {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #1a73e8, #6eadf7);
            transition: width 1s linear, background 0.5s;
            width: 100%;
        }
        #timer-bar.warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        #timer-bar.danger  { background: linear-gradient(90deg, #ef4444, #f87171); }
        .timer-text {
            font-size: 0.78rem;
            color: #6c757d;
            text-align: center;
            margin-top: 4px;
        }
        .student-info {
            background: #f0f6ff;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-top: 1.25rem;
        }
        .badge-status {
            font-size: 0.7rem;
            padding: 3px 10px;
            border-radius: 999px;
        }
        .qr-expired-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.92);
            border-radius: 14px;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #dc3545;
            font-weight: 600;
        }
        .qr-expired-overlay.show { display: flex; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="qr-header d-flex align-items-center justify-content-between">
        <div>
            <a href="{{ route('student.dashboard') }}" class="text-white text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
            <h5 class="mb-0 fw-bold mt-1">QR Absensi Gerbang</h5>
        </div>
        <div>
            <span class="badge bg-white text-primary fw-semibold" style="font-size:0.75rem;">
                <i class="bi bi-person-fill me-1"></i>{{ $student->class->name ?? '-' }}
            </span>
        </div>
    </div>

    <div class="container py-4 flex-fill d-flex align-items-start justify-content-center">
        <div class="qr-card">

            {{-- Student Info --}}
            <div class="student-info d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                     style="width:46px;height:46px;font-size:1.2rem;">
                    {{ strtoupper(substr($student->name, 0, 1)) }}
                </div>
                <div class="flex-fill overflow-hidden">
                    <div class="fw-bold text-truncate">{{ $student->name }}</div>
                    <div class="small text-muted">NIS: {{ $student->nis }}</div>
                </div>
                <span class="badge bg-success badge-status">Aktif</span>
            </div>

            {{-- QR Display --}}
            <div class="qr-image-wrap" id="qr-wrap">
                {{-- Loading overlay --}}
                <div id="qr-loading">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <span>Memuat QR...</span>
                </div>

                {{-- Expired overlay --}}
                <div class="qr-expired-overlay" id="qr-expired">
                    <i class="bi bi-arrow-clockwise" style="font-size:2rem;"></i>
                    <span>QR Kedaluwarsa</span>
                    <button class="btn btn-sm btn-primary px-3" onclick="generateQr()">Perbarui</button>
                </div>

                {{-- QR Image --}}
                <div id="qr-image-container"></div>
            </div>

            {{-- Timer Bar --}}
            <div class="timer-bar-wrap mt-3">
                <div id="timer-bar"></div>
            </div>
            <div class="timer-text" id="timer-text">Memuat...</div>

            {{-- Refresh Button --}}
            <button class="btn btn-primary w-100 mt-3 fw-semibold" id="btn-refresh" onclick="generateQr()" type="button">
                <i class="bi bi-arrow-clockwise me-2"></i>Perbarui QR
            </button>

            <p class="text-center text-muted small mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Tunjukkan QR ini ke petugas piket untuk absensi gerbang.<br>
                QR diperbarui otomatis setiap <strong>{{ $qrTtl }} detik</strong>.
            </p>
        </div>
    </div>

    <script>
    const GENERATE_URL = '{{ route("student.qrcode.generate") }}';
    const CSRF_TOKEN   = '{{ csrf_token() }}';
    const TTL          = {{ $qrTtl }};

    let timer        = null;
    let secondsLeft  = 0;
    let autoInterval = null;

    const qrLoading   = document.getElementById('qr-loading');
    const qrExpired   = document.getElementById('qr-expired');
    const qrContainer = document.getElementById('qr-image-container');
    const timerBar    = document.getElementById('timer-bar');
    const timerText   = document.getElementById('timer-text');
    const btnRefresh  = document.getElementById('btn-refresh');

    function generateQr() {
        // Clear existing timer
        if (autoInterval) clearInterval(autoInterval);
        if (timer)        clearTimeout(timer);

        // Show loading
        qrLoading.classList.remove('d-none');
        qrExpired.classList.remove('show');
        qrContainer.innerHTML = '';
        btnRefresh.disabled   = true;

        fetch(GENERATE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(r => r.json())
        .then(response => {
            qrLoading.classList.add('d-none');
            btnRefresh.disabled = false;

            if (!response.success || !response.data) {
                showExpired();
                return;
            }

            // Render QR SVG
            const img = document.createElement('img');
            img.src   = 'data:image/svg+xml;base64,' + response.data.qr_svg;
            img.alt   = 'QR Absensi';
            img.style.maxWidth  = '220px';
            img.style.maxHeight = '220px';
            qrContainer.appendChild(img);

            // Start countdown
            startCountdown(response.data.ttl_seconds ?? TTL);
        })
        .catch(() => {
            qrLoading.classList.add('d-none');
            btnRefresh.disabled = false;
            showExpired();
        });
    }

    function startCountdown(seconds) {
        secondsLeft = seconds;
        updateTimerUI(secondsLeft, seconds);

        autoInterval = setInterval(() => {
            secondsLeft--;
            updateTimerUI(secondsLeft, seconds);

            if (secondsLeft <= 0) {
                clearInterval(autoInterval);
                // Auto-generate new QR when expired
                generateQr();
            }
        }, 1000);
    }

    function updateTimerUI(left, total) {
        const pct = Math.max(0, (left / total) * 100);
        timerBar.style.width = pct + '%';

        // Color feedback
        timerBar.classList.remove('warning', 'danger');
        if (pct <= 25)      timerBar.classList.add('danger');
        else if (pct <= 50) timerBar.classList.add('warning');

        timerText.textContent = left > 0
            ? `QR berlaku ${left} detik lagi`
            : 'Memperbarui QR...';
    }

    function showExpired() {
        qrExpired.classList.add('show');
        timerBar.style.width = '0%';
        timerText.textContent = 'QR kedaluwarsa — tap Perbarui';
    }

    // Auto-generate on page load
    document.addEventListener('DOMContentLoaded', generateQr);
    </script>
</body>
</html>
