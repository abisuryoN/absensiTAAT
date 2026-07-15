<x-app-layout>
    @section('title', 'Dashboard Super Admin')

    <div class="row g-4 mb-4">
        <!-- Stat Card 1 -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card glass-card text-white bg-primary h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-7 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Total Siswa</span>
                        <h2 class="display-6 fw-bold mb-0 mt-1">1,245</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-people-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card glass-card text-white bg-success h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-7 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Hadir Hari Ini</span>
                        <h2 class="display-6 fw-bold mb-0 mt-1">1,182</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-check-circle-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card glass-card text-white bg-warning h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-7 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Terlambat</span>
                        <h2 class="display-6 fw-bold mb-0 mt-1">42</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-clock-history fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card glass-card text-white bg-danger h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-7 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Tanpa Keterangan</span>
                        <h2 class="display-6 fw-bold mb-0 mt-1">21</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card glass-card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Monitoring Absensi Realtime</h5>
                <p class="text-muted">Fase 1 Foundation setup berhasil diselesaikan. Modul master data, pencatatan absensi gerbang (USB/kamera), notifikasi WhatsApp queue, rekap absensi pelajaran guru, dan reporting akan diimplementasikan secara bertahap pada fase berikutnya.</p>
                <div class="alert alert-info d-flex align-items-center gap-2 mb-0" role="alert">
                    <i class="bi bi-info-circle-fill"></i>
                    <div>Default Akun Super Admin: <strong>admin@sman1tajurhalang.sch.id</strong> / password: <strong>password</strong></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card glass-card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">Aktivitas Sistem</h5>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex gap-3 mb-3">
                        <span class="badge bg-success p-2 rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;"><i class="bi bi-shield-check"></i></span>
                        <div>
                            <p class="mb-0 fw-semibold text-dark fs-7">Sistem Diinisialisasi</p>
                            <span class="text-muted fs-8">Fase 1: Foundation & Setup Sukses</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
