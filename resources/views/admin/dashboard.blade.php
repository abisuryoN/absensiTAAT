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

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Stat Card 1: Total Siswa -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card glass-card text-white bg-primary border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-8 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Total Siswa</span>
                        <h2 class="fw-bold mb-0 mt-1">{{ number_format($totalSiswa) }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-people-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat Card 2: Hadir Hari Ini -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card glass-card text-white bg-success border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-8 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Hadir Hari Ini</span>
                        <h2 class="fw-bold mb-0 mt-1">{{ number_format($hadir) }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-check-circle-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat Card 3: Terlambat -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card glass-card text-white bg-warning border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-8 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Terlambat</span>
                        <h2 class="fw-bold mb-0 mt-1">{{ number_format($terlambat) }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-clock-history fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat Card 4: Belum Absen -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card glass-card text-white bg-danger border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-8 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Belum Absen</span>
                        <h2 class="fw-bold mb-0 mt-1">{{ number_format($belumAbsen) }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                    </div>
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
                    <canvas id="weeklyTrendChart"></canvas>
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
            const ctx = document.getElementById('weeklyTrendChart').getContext('2d');
            
            const labels = @json($chartLabels);
            const dataHadir = @json($chartHadir);
            const dataTerlambat = @json($chartTerlambat);
            const dataAlpha = @json($chartAlpha);

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
    </style>
</x-app-layout>
