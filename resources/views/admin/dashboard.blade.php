<x-app-layout>
    @section('title', 'Dashboard Super Admin')

    
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

   
    <div class="row g-0 mb-4 stats-row">
        <!-- Total Siswa -->
        <div class="col-6 col-md-3 stats-col">
            <div class="card card-stat text-white border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
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
        <div class="col-6 col-md-3 stats-col">
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
        <div class="col-6 col-md-3 stats-col">
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

        <!-- Tidak Hadir (clickable) -->
        <div class="col-6 col-md-3 stats-col">
            <a href="{{ route('admin.attendance.today') }}" class="text-decoration-none">
                <div class="card card-stat bg-danger text-white border-0 rounded-4 shadow-sm h-100">
                    <div class="card-body position-relative p-4">
                        <div class="position-absolute top-0 end-0 p-3">
                            <i class="bi bi-exclamation-triangle-fill fs-3 opacity-50"></i>
                        </div>
                        <div class="stat-value display-6 fw-bold mb-0">{{ number_format($tidakHadir) }}</div>
                        <div class="stat-label fs-7 fw-medium text-white-50 mt-1">Tidak Hadir</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Charts & Activity Row -->
    <div class="row g-4">
        <!-- Weekly Trend Chart -->
        <div class="col-12 col-lg-8">
            <div class="card glass-card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-bar-chart-line-fill me-2" style="color: #0d6efd !important;"></i>Tren Kehadiran Mingguan
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
                                        'scan' => ['bg' => 'bg-light-blue', 'icon' => 'bi-qr-code-scan'],
                                        'login' => ['bg' => 'bg-primary', 'icon' => 'bi-box-arrow-in-right'],
                                        'import' => ['bg' => 'bg-warning text-dark', 'icon' => 'bi-file-earmark-arrow-up'],
                                    ];
                                    $cfg = $badgeConfig[$log->action] ?? ['bg' => 'bg-secondary', 'icon' => 'bi-info-circle'];
                                @endphp
                                <div class="position-relative flex-shrink-0">
                                    @if($log->user && $log->user->profile_picture)
                                        <img src="{{ asset('storage/' . $log->user->profile_picture) }}" 
                                             alt="{{ $log->user->name }}" 
                                             class="rounded-circle"
                                             style="width: 30px; height: 30px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-semibold" 
                                             style="width: 30px; height: 30px; font-size: 0.7rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            {{ strtoupper(substr($log->user->name ?? 'S', 0, 2)) }}
                                        </div>
                                    @endif
                                    <span class="badge {{ $cfg['bg'] }} position-absolute bottom-0 end-0 p-1 rounded-circle d-flex align-items-center justify-content-center" 
                                          style="width: 16px; height: 16px; border: 2px solid white;">
                                        <i class="bi {{ $cfg['icon'] }}" style="font-size: 0.5rem;"></i>
                                    </span>
                                </div>
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
                            label: 'Hadir (Tepat + Terlambat)',
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
        .bg-light-blue { 
            background-color: #eef4ff !important; 
            color: #334155 !important; 
        }

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
            color: white !important;
        }
        .card-stat .stat-label {
            font-size: clamp(0.7rem, 2vw, 0.85rem);
            color: rgba(255, 255, 255, 0.75) !important;
        }
        .card-stat .card-body {
            min-height: 110px;
        }

        /* Stats column spacing - manual padding to avoid global SCSS override */
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

        @media (max-width: 767.98px) {
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