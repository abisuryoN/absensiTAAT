<x-app-layout>
    @section('title', 'Absensi Gerbang')

    <!-- Style Override for Scan Page to feel immersive -->
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
        #interactive-camera {
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            overflow: hidden;
            display: none;
            margin: 0 auto;
        }
    </style>

    <div class="row scan-container align-items-stretch g-4">
        <!-- Left Side: Scanner Control & Camera -->
        <div class="col-md-6 d-flex flex-column justify-content-between">
            <div class="card glass-card border-0 h-100 p-4">
                <div>
                    <h3 class="fw-bold tracking-tight text-dark mb-1">Absensi Gerbang Masuk</h3>
                    <p class="text-muted">Gunakan USB Barcode Scanner (auto-focus) atau Kamera untuk memindai QR Code siswa.</p>
                </div>

                <div class="my-4 text-center">
                    <!-- Scanner Box / Visual Feedback -->
                    <div id="scanner-box" class="scanner-card rounded-4 p-4 text-center">
                        <div id="visual-indicator" class="visual-indicator idle">
                            <i id="indicator-icon" class="bi bi-qr-code-scan fs-1"></i>
                        </div>
                        <h4 id="status-title" class="fw-bold text-dark mb-2">Siap Memindai</h4>
                        <p id="status-text" class="text-muted fs-7 mb-0">Dekatkan kartu barcode siswa ke scanner atau posisikan QR code pada kamera.</p>
                    </div>

                    <!-- Camera Scanner Container -->
                    <div class="mt-4">
                        <div id="interactive-camera"></div>
                        <button type="button" id="btn-toggle-camera" class="btn btn-outline-primary fw-semibold btn-sm mt-3">
                            <i class="bi bi-camera me-1"></i> Aktifkan Kamera QR
                        </button>
                    </div>
                </div>

                <!-- Input Field (Hidden or Autofocused for USB Scanner keyboard emulation) -->
                <form id="scan-form" class="mt-auto">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-barcode"></i></span>
                        <input type="text" name="scan_value" id="scan_value" class="form-control border-start-0" placeholder="Input Barcode/QR manual..." autocomplete="off">
                        <button type="submit" class="btn btn-primary fw-semibold">Proses</button>
                    </div>
                    <div class="form-text fs-8 text-center mt-2 text-muted">
                        <i class="bi bi-lightning-fill text-warning me-1"></i> Input otomatis terfokus untuk kelancaran USB Scanner.
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side: Student Profile & History Feed -->
        <div class="col-md-6 d-flex flex-column">
            <!-- Student Detail Display Panel -->
            <div class="card glass-card border-0 mb-4 p-4 flex-grow-1 d-flex flex-column justify-content-center align-items-center text-center">
                <div id="student-profile" style="display: none;">
                    <img id="student-avatar" src="" alt="Foto Siswa" class="rounded-circle object-fit-cover shadow-sm mb-3 border border-3 border-white" style="width: 140px; height: 140px; display: none;">
                    <div id="student-avatar-placeholder" class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm mb-3 border border-3 border-white mx-auto" style="width: 140px; height: 140px; font-size: 2.5rem;">
                        -
                    </div>
                    <h4 id="student-name" class="fw-bold text-dark mb-1">-</h4>
                    <p id="student-class" class="text-primary fw-semibold mb-2">-</p>
                    <span id="student-nis" class="text-muted fs-7 mb-3 d-block">-</span>

                    <div class="d-flex justify-content-center gap-3">
                        <div class="bg-light border rounded px-3 py-2">
                            <span class="fs-8 text-muted d-block text-uppercase">Waktu Scan</span>
                            <strong id="scan-time" class="text-dark fs-5">-</strong>
                        </div>
                        <div class="bg-light border rounded px-3 py-2">
                            <span class="fs-8 text-muted d-block text-uppercase">Status</span>
                            <strong id="scan-status" class="text-dark fs-5">-</strong>
                        </div>
                    </div>
                </div>

                <div id="profile-idle-state" class="text-muted">
                    <i class="bi bi-person-bounding-box text-light-emphasis fs-1 d-block mb-3"></i>
                    <p class="mb-0">Profil siswa yang sukses scan akan ditampilkan di sini.</p>
                </div>
            </div>

            <!-- Recent Scans Mini Feed -->
            <div class="card glass-card border-0 p-4" style="height: 250px;">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-1"></i> Scan Terakhir Hari Ini</h6>
                <div id="recent-scans-list" class="overflow-y-auto flex-grow-1" style="max-height: 160px;">
                    <div class="text-center py-4 text-muted fs-8" id="recent-scans-empty">
                        Belum ada riwayat scan untuk sesi ini.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Gate Grid (only on <768px) --}}
    <div class="d-block d-md-none mobile-page-content" style="padding:0; margin-top:0;">
        <div class="mobile-gate-grid">
            {{-- Scanner --}}
            <div class="gate-card gate-scanner" id="mobile-gate-scanner">
                <div class="gate-card-title">Scanner</div>
                <div class="scanner-placeholder">
                    <div class="scanner-icon"><i class="bi bi-qr-code-scan"></i></div>
                    <div class="scanner-text">Arahkan QR Code ke kamera atau scan barcode</div>
                </div>
            </div>

            {{-- Result --}}
            <div class="gate-card gate-result" id="mobile-gate-result">
                <div class="gate-card-title">Hasil Scan</div>
                <div id="mobile-gate-result-content">
                    <div class="result-profile">
                        <div class="result-avatar"><i class="bi bi-person"></i></div>
                        <div class="result-info">
                            <div class="result-name" id="mobile-result-name">-</div>
                            <div class="result-class" id="mobile-result-class">-</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="gate-card gate-status" id="mobile-gate-status">
                <div class="gate-card-title">Status</div>
                <div class="status-item">
                    <span class="status-label">Status Scan</span>
                    <span class="status-badge badge bg-secondary" id="mobile-status-badge">Menunggu</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Waktu</span>
                    <span class="status-label fw-semibold" id="mobile-scan-time">-</span>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="gate-card gate-stats" id="mobile-gate-stats">
                <div class="gate-card-title">Statistik</div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-num" id="mobile-stat-hadir">0</div>
                        <div class="stat-label">Hadir</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num" id="mobile-stat-terlambat">0</div>
                        <div class="stat-label">Telat</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include html5-qrcode library for camera scanning -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scanInput = document.getElementById('scan_value');
            const scanForm = document.getElementById('scan-form');
            const scannerBox = document.getElementById('scanner-box');
            const visualIndicator = document.getElementById('visual-indicator');
            const indicatorIcon = document.getElementById('indicator-icon');
            const statusTitle = document.getElementById('status-title');
            const statusText = document.getElementById('status-text');

            // Student profile nodes
            const profileIdle = document.getElementById('profile-idle-state');
            const studentProfile = document.getElementById('student-profile');
            const studentAvatar = document.getElementById('student-avatar');
            const studentAvatarPlaceholder = document.getElementById('student-avatar-placeholder');
            const studentName = document.getElementById('student-name');
            const studentClass = document.getElementById('student-class');
            const studentNis = document.getElementById('student-nis');
            const scanTime = document.getElementById('scan-time');
            const scanStatus = document.getElementById('scan-status');

            // Recent scan feed
            const recentScansList = document.getElementById('recent-scans-list');
            const recentScansEmpty = document.getElementById('recent-scans-empty');

            // Web Audio API Synthesizer (Beep maker)
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

            function playBeep(success = true) {
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                if (success) {
                    // Quick high-pitch beep
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
                    gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + 0.15);
                } else {
                    // Sawtooth error buzz
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(120, audioCtx.currentTime); // low pitch
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + 0.35);
                }
            }

            // Always keep input focused for USB barcode scanner keyboard emulation
            function focusInput() {
                scanInput.focus();
            }
            focusInput();
            document.addEventListener('click', focusInput);

            // Audio Activation trigger on first interaction
            document.addEventListener('keydown', function() {
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
            }, { once: true });

            // Handle Scan Submit
            scanForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const val = scanInput.value.trim();
                if (!val) return;

                processScanValue(val);
                scanInput.value = '';
            });

            function processScanValue(value) {
                // Update UI to processing state
                statusTitle.innerText = "Memproses...";
                statusText.innerText = "Sedang mencocokkan data absensi siswa...";

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
                    // Success Feedback
                    playBeep(true);
                    updateScannerUI(true, "Absensi Berhasil", data.message);
                    showStudentProfile(data.data);
                    addToRecentFeed(data.data);
                })
                .catch(err => {
                    // Error Feedback
                    playBeep(false);
                    updateScannerUI(false, "Absensi Gagal", err.message);
                })
                .finally(() => {
                    setTimeout(() => {
                        resetScannerUI();
                    }, 3000);
                });
            }

            function updateScannerUI(success, title, message) {
                scannerBox.className = `scanner-card rounded-4 p-4 text-center ${success ? 'active' : 'error'}`;
                visualIndicator.className = `visual-indicator ${success ? 'success' : 'error'}`;
                indicatorIcon.className = success ? 'bi bi-check-circle fs-1' : 'bi bi-x-circle fs-1';
                statusTitle.innerText = title;
                statusText.innerText = message;
            }

            function resetScannerUI() {
                scannerBox.className = 'scanner-card rounded-4 p-4 text-center';
                visualIndicator.className = 'visual-indicator idle';
                indicatorIcon.className = 'bi bi-qr-code-scan fs-1';
                statusTitle.innerText = "Siap Memindai";
                statusText.innerText = "Dekatkan kartu barcode siswa ke scanner atau posisikan QR code pada kamera.";
            }

            function showStudentProfile(data) {
                profileIdle.style.display = 'none';
                studentProfile.style.display = 'block';

                if (data.photo) {
                    studentAvatar.src = data.photo;
                    studentAvatar.style.display = 'block';
                    studentAvatarPlaceholder.style.display = 'none';
                } else {
                    studentAvatar.style.display = 'none';
                    studentAvatarPlaceholder.innerText = data.name.substring(0, 2).toUpperCase();
                    studentAvatarPlaceholder.style.display = 'flex';
                }

                studentName.innerText = data.name;
                studentClass.innerText = data.class;
                studentNis.innerText = `NIS: ${data.nis}`;
                scanTime.innerText = data.time;
                scanStatus.innerText = data.status;

                // Color based on status
                if (data.status.toLowerCase() === 'terlambat') {
                    scanStatus.className = "text-danger fs-5";
                } else {
                    scanStatus.className = "text-success fs-5";
                }
            }

            function addToRecentFeed(data) {
                recentScansEmpty.style.display = 'none';
                
                const item = document.createElement('div');
                item.className = 'd-flex align-items-center justify-content-between p-2 mb-2 bg-light rounded border border-start-3 ' + 
                    (data.status.toLowerCase() === 'terlambat' ? 'border-start-danger' : 'border-start-success');
                
                item.innerHTML = `
                    <div>
                        <span class="fw-semibold text-dark fs-7 d-block">${data.name}</span>
                        <span class="text-muted fs-8">${data.class} &middot; NIS: ${data.nis}</span>
                    </div>
                    <div class="text-end">
                        <span class="badge ${data.status.toLowerCase() === 'terlambat' ? 'bg-danger' : 'bg-success'} fs-8 d-block mb-1">${data.status}</span>
                        <span class="text-muted fs-8">${data.time} WIB</span>
                    </div>
                `;

                recentScansList.insertBefore(item, recentScansList.firstChild);
            }

            // HTML5 Camera QR Code Scanner setup
            let html5QrcodeScanner = null;
            const btnToggleCamera = document.getElementById('btn-toggle-camera');
            const interactiveCamera = document.getElementById('interactive-camera');

            btnToggleCamera.addEventListener('click', function() {
                if (html5QrcodeScanner) {
                    // Stop camera
                    html5QrcodeScanner.stop().then(() => {
                        html5QrcodeScanner = null;
                        interactiveCamera.style.display = 'none';
                        btnToggleCamera.innerHTML = '<i class="bi bi-camera me-1"></i> Aktifkan Kamera QR';
                    }).catch(err => {
                        console.error("Gagal menghentikan kamera: ", err);
                    });
                } else {
                    // Start camera
                    interactiveCamera.style.display = 'block';
                    btnToggleCamera.innerHTML = '<i class="bi bi-camera-video-off me-1"></i> Matikan Kamera';
                    
                    html5QrcodeScanner = new Html5Qrcode("interactive-camera");
                    html5QrcodeScanner.start(
                        { facingMode: "environment" },
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        (decodedText) => {
                            // On scan success
                            processScanValue(decodedText);
                        },
                        (errorMessage) => {
                            // ignore console spam on idle frames
                        }
                    ).catch(err => {
                        console.error("Gagal mengaktifkan kamera: ", err);
                        alert("Kamera gagal diakses. Pastikan izin kamera telah diberikan.");
                        interactiveCamera.style.display = 'none';
                        btnToggleCamera.innerHTML = '<i class="bi bi-camera me-1"></i> Aktifkan Kamera QR';
                    });
                }
            });
        });
    </script>
</x-app-layout>
