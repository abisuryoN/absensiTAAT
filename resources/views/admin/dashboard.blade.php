<x-app-layout>
    @section('title', 'Dashboard Super Admin')

    <!-- Welcome Header (Photo 4 style) -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="bg-dark bg-opacity-10 text-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background-color: #334155 !important;">
            <i class="bi bi-eye-slash text-white fs-5"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-1 text-dark">Halo, {{ strtoupper(auth()->user()->name) }}!</h4>
            <p class="text-muted mb-0 fs-7">
                Mode Privasi Aktif: Data sensitif disembunyikan.
            </p>
        </div>
    </div>

    <!-- Stats Cards - Unified Grid (2 kolom mobile, 4 kolom desktop) -->
    <div class="row g-5 mb-5 stats-row">
        <!-- Total Siswa -->
        <div class="col-6 col-md-3">
            <div class="card card-stat bg-primary text-white border-0 rounded-4 shadow-sm h-100">
                <div class="card-body position-relative p-4">
                    <div class="position-absolute top-0 end-0 p-3">
                        <i class="bi bi-people-fill fs-3 opacity-50"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0">{{ number_format($totalSiswa) }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Total Siswa</div>
                </div>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="col-6 col-md-3">
            <div class="card card-stat bg-success text-white border-0 rounded-4 shadow-sm h-100">
                <div class="card-body position-relative p-4">
                    <div class="position-absolute top-0 end-0 p-3">
                        <i class="bi bi-check-circle-fill fs-3 opacity-50"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0">{{ number_format($hadir) }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Hadir Hari Ini</div>
                </div>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="col-6 col-md-3">
            <div class="card card-stat bg-warning text-white border-0 rounded-4 shadow-sm h-100">
                <div class="card-body position-relative p-4">
                    <div class="position-absolute top-0 end-0 p-3">
                        <i class="bi bi-clock-history fs-3 opacity-50"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0">{{ number_format($terlambat) }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Terlambat</div>
                </div>
            </div>
        </div>

        <!-- Belum Absen -->
        <div class="col-6 col-md-3">
            <div class="card card-stat bg-danger text-white border-0 rounded-4 shadow-sm h-100">
                <div class="card-body position-relative p-4">
                    <div class="position-absolute top-0 end-0 p-3">
                        <i class="bi bi-exclamation-triangle-fill fs-3 opacity-50"></i>
                    </div>
                    <div class="stat-value display-6 fw-bold mb-0">{{ number_format($belumAbsen) }}</div>
                    <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Belum Absen</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Activity Row -->
    <div class="row g-4">
        <!-- Weekly Trend Chart -->
        <div class="col-12 col-lg-8">
            <div class="card glass-card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Tren Kehadiran Mingguan
                </h5>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="weeklyTrendChart"
                        data-labels='{{ json_encode($chartLabels ?? []) }}'
                        data-hadir='{{ json_encode($chartHadir ?? []) }}'
                        data-terlambat='{{ json_encode($chartTerlambat ?? []) }}'
                        data-alpha='{{ json_encode($chartAlpha ?? []) }}'></canvas>
                </div>
            </div>
        </div>

        <!-- System Activity Timeline -->
        <div class="col-12 col-lg-4">
            <div class="card glass-card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-activity me-2 text-success"></i>Aktivitas Sistem
                </h5>
                <div class="activity-timeline" style="max-height: 320px; overflow-y: auto;">
                    <ul class="list-unstyled mb-0">
                        @forelse($activities as $log)
                            <li class="d-flex gap-3 mb-3 align-items-start">
                                @php
                                    $badgeConfig = [
                                        'create' => ['bg' => 'bg-success', 'icon' => 'bi-plus-circle'],
                                        'update' => ['bg' => 'bg-info', 'icon' => 'bi-pencil'],
                                        'delete' => ['bg' => 'bg-danger', 'icon' => 'bi-trash'],
                                        'scan' => ['bg' => 'bg-indigo', 'icon' => 'bi-qr-code-scan'],
                                        'login' => ['bg' => 'bg-primary', 'icon' => 'bi-box-arrow-in-right'],
                                        'import' => ['bg' => 'bg-warning text-dark', 'icon' => 'bi-file-earmark-arrow-up'],
                                    ];
                                    $cfg = $badgeConfig[$log->action] ?? ['bg' => 'bg-secondary', 'icon' => 'bi-info-circle'];
                                @endphp
                                <span class="badge {{ $cfg['bg'] }} p-2 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:30px; height:30px;">
                                    <i class="bi {{ $cfg['icon'] }}"></i>
                                </span>
                                <div class="overflow-hidden">
                                    <p class="mb-0 fw-semibold text-dark fs-7 text-truncate" title="{{ $log->description }}">{{ $log->description }}</p>
                                    <span class="text-muted fs-8 d-block">{{ $log->user->name ?? 'System' }} &bull; {{ $log->created_at->diffForHumans() }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="text-center py-5">
                                <i class="bi bi-journal-x fs-1 text-muted d-block mb-2"></i>
                                <span class="text-muted fs-7">Belum ada aktivitas tercatat</span>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('weeklyTrendChart');
            const ctx = canvas.getContext('2d');
            
            const labels = JSON.parse(canvas.dataset.labels);
            const dataHadir = JSON.parse(canvas.dataset.hadir);
            const dataTerlambat = JSON.parse(canvas.dataset.terlambat);
            const dataAlpha = JSON.parse(canvas.dataset.alpha);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Hadir Tepat Waktu',
                            data: dataHadir,
                            backgroundColor: 'rgba(25, 135, 84, 0.85)', // bg-success
                            borderColor: 'rgb(25, 135, 84)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Terlambat',
                            data: dataTerlambat,
                            backgroundColor: 'rgba(255, 193, 7, 0.85)', // bg-warning
                            borderColor: 'rgb(255, 193, 7)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Alpha (Tanpa Keterangan)',
                            data: dataAlpha,
                            backgroundColor: 'rgba(220, 53, 69, 0.85)', // bg-danger
                            borderColor: 'rgb(220, 53, 69)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush

    <style>
        .bg-indigo { background-color: #6366f1; color: white; }

        /* Card Statistik - Grid rapi dengan rounded corner & floating effect */
        .card-stat {
            border-radius: 16px !important;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }
        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.18) !important;
        }
        .card-stat .stat-value {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            line-height: 1.1;
        }
        .card-stat .stat-label {
            font-size: clamp(0.7rem, 2vw, 0.85rem);
        }
        .card-stat .card-body {
            min-height: 110px;
        }
        @media (max-width: 767.98px) {
            /* Protect stats row from global mobile override that kills Bootstrap gutters */
            .stats-row {
                margin-left: -12px !important;
                margin-right: -12px !important;
            }
            .row.stats-row [class*="col-"] {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .card-stat .card-body {
                padding: 1rem !important;
                min-height: 100px;
            }
            .card-stat .stat-value {
                font-size: 1.6rem;
            }
            .card-stat .stat-label {
                font-size: 0.7rem;
            }
            .card-stat .position-absolute.p-3 {
                padding: 0.65rem !important;
            }
            .card-stat .position-absolute i {
                font-size: 1.4rem !important;
            }
        }
    </style>
</x-app-layout>
