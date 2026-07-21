<x-app-layout>
    @section('title', 'Dashboard Piket')

    @push('styles')
    <style>
        /* Card Statistik - sama seperti dashboard admin */
        .card-stat {
            position: relative;
            border-radius: 16px !important;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }
        .card-stat::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -30px;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            z-index: 0;
        }
        .card-stat .card-body {
            position: relative;
            z-index: 1;
        }
        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.18) !important;
        }
        .card-stat .stat-value {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            line-height: 1.1;
            color: white !important;
        }
        .card-stat .stat-label {
            font-size: clamp(0.7rem, 2vw, 0.85rem);
            color: rgba(255, 255, 255, 0.75) !important;
        }
        .card-stat .card-body {
            min-height: 110px;
        }

        /* Stats column spacing */
        .stats-col {
            padding-left: 16px !important;
            padding-right: 16px !important;
            margin-bottom: 24px;
        }

        @media (min-width: 768px) {
            .stats-col {
                margin-bottom: 0;
            }
        }

        .status-badge { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
        .status-hadir     { background: #d4edda; color: #155724; }
        .status-terlambat { background: #fff3cd; color: #856404; }
        
        /* Mobile spacing adjustments */
        @media (max-width: 480px) {
            /* Container padding */
            .mobile-main-content {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }
            
            /* Page header spacing */
            .d-flex.align-items-center.justify-content-between.mb-4 {
                margin-bottom: 20px !important;
            }
            
            /* Stats cards - add gap and padding */
            .row.g-3 {
                display: flex !important;
                gap: 8px !important;
                margin-bottom: 20px !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            
            .row.g-3 > [class*="col-"] {
                flex: 1 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                min-width: 0 !important;
            }
            
            .stat-card {
                padding: 16px 12px !important;
            }
            
            .stat-card .h2 {
                font-size: 1.6rem !important;
                margin-bottom: 6px !important;
            }
            
            .stat-card .small {
                font-size: 0.7rem !important;
            }
            
            /* Section "Scan Hari Ini" spacing */
            .card.border-0.shadow-sm.rounded-3.mb-4 {
                margin-bottom: 20px !important;
                padding: 12px;
            }
            
            .card-header {
                padding: 12px !important;
            }
            
            .card-body {
                padding: 12px !important;
            }
            
            /* Action buttons spacing */
            .d-flex.gap-2.justify-content-center {
                margin-top: 20px !important;
                margin-bottom: 90px !important; /* Space for bottom nav */
                gap: 10px !important;
            }
            
            .d-flex.gap-2.justify-content-center .btn {
                padding: 10px 16px !important;
                font-size: 0.9rem !important;
            }
        }
    </style>
    @endpush

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="small text-muted fw-semibold text-uppercase">GURU PIKET</div>
            <h4 class="fw-bold mb-0">{{ $namaLengkap }}</h4>
            <div class="small text-muted">{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</div>
        </div>
        <a href="{{ route('piket.scan') }}" class="btn btn-primary fw-semibold">
            <i class="bi bi-qr-code-scan me-1"></i>Scan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats --}}
    <div class="row g-0 mb-4 stats-row">
        <div class="col-6 col-md-4 stats-col">
            <div class="card stat-card text-white border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                <div class="card-body p-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-qr-code-scan fs-4"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0">{{ $stats['total'] }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Total Scan</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 stats-col">
            <div class="card stat-card bg-success text-white border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0">{{ $stats['hadir'] }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Hadir</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 stats-col">
            <div class="card stat-card bg-warning text-white border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0">{{ $stats['terlambat'] }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Terlambat</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Scan Hari Ini --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-clock-history me-2 text-primary"></i>Scan Hari Ini
            </h6>
            <a href="{{ route('piket.rekap') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-list-ul me-1"></i>Rekap
            </a>
        </div>
        <div class="card-body p-0">
            @if($recentScans->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox d-block mb-1 fs-4"></i>
                    Belum ada scan hari ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Siswa</th>
                                <th>Waktu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentScans as $scan)
                            <tr>
                                <td>
                                    <div class="fw-semibold small">{{ $scan->student->name ?? '-' }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $scan->student->nis ?? '' }}</div>
                                </td>
                                <td class="small text-muted">{{ substr($scan->time_in, 0, 5) }}</td>
                                <td>
                                    <span class="status-badge status-{{ $scan->status }}">
                                        {{ ucfirst($scan->status) }}
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

    {{-- Footer aksi --}}
    <div class="d-flex gap-2 justify-content-center">
        <form method="POST" action="{{ route('piket.end-session') }}" id="end-session-form">
            @csrf
            <button type="button" class="btn btn-danger px-4 py-2 fw-semibold" style="border-radius: 10px;" onclick="handleEndSession(event)">
                <i class="bi bi-stop-circle me-1"></i>Akhiri Sesi
            </button>
        </form>
        <form method="POST" action="{{ route('logout') }}" id="guru-piket-logout-form">
            @csrf
            <button type="button" class="btn btn-danger px-4 py-2 fw-semibold" style="border-radius: 10px; background: linear-gradient(90deg, #dc2626 0%, #ef4444 100%) !important; border: none !important;" onclick="handleGuruPiketLogout(event)">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </button>
        </form>
    </div>

    @push('scripts')
    <script>
        // Handle end session confirmation
        function handleEndSession(event) {
            event.preventDefault();
            if (typeof confirmEndSession === 'function') {
                confirmEndSession(function() {
                    document.getElementById('end-session-form').submit();
                });
            } else {
                // Fallback if confirmEndSession is not loaded yet
                if (confirm('Akhiri sesi piket Anda sekarang?')) {
                    document.getElementById('end-session-form').submit();
                }
            }
        }

        // Handle guru piket logout confirmation
        function handleGuruPiketLogout(event) {
            event.preventDefault();
            if (typeof confirmLogout === 'function') {
                confirmLogout(function() {
                    document.getElementById('guru-piket-logout-form').submit();
                });
            } else {
                // Fallback if confirmLogout is not loaded yet
                if (confirm('Yakin ingin logout?')) {
                    document.getElementById('guru-piket-logout-form').submit();
                }
            }
        }
    </script>
    @endpush

</x-app-layout>