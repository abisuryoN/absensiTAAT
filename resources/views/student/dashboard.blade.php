<x-app-layout>
    @section('title', 'Portal Siswa')

    <div class="row g-4">
        <!-- Main Card: Student Info & Dynamic QR -->
        <div class="col-12 col-lg-8">
            <div class="card glass-card border-0 shadow-sm p-4 h-100">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-md-4 text-center">
                        <!-- Dynamic QR Placeholder -->
                        <div class="bg-light border rounded-4 p-4 d-flex flex-column align-items-center justify-content-center mx-auto" style="max-width: 200px; aspect-ratio: 1/1;">
                            <i class="bi bi-qr-code fs-1 text-muted mb-2"></i>
                            <span class="fs-8 fw-semibold text-muted text-uppercase">QR Code Absensi</span>
                            <span class="badge bg-indigo-50 text-indigo-700 mt-2 fs-9">One-Time Token (30s)</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold text-uppercase px-3 py-1 mb-2 fs-8">Siswa Aktif</span>
                        <h3 class="fw-bold mb-1">{{ auth()->user()->name }}</h3>
                        <p class="text-muted mb-3 fs-7">NIS: 120938491 &bull; Kelas: XI RPL 2</p>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="fs-8 text-muted d-block text-uppercase fw-semibold">Kehadiran Bulanan</span>
                                    <span class="fs-4 fw-bold text-success mt-1 d-block">98.4%</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="fs-8 text-muted d-block text-uppercase fw-semibold">Total Terlambat</span>
                                    <span class="fs-4 fw-bold text-warning mt-1 d-block">2x</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule list on right -->
        <div class="col-12 col-lg-4">
            <div class="card glass-card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">Jadwal Kelas Hari Ini</h5>
                <div class="timeline">
                    <div class="p-3 bg-light rounded-3 mb-2 border-start border-4 border-primary">
                        <span class="fs-8 text-muted fw-semibold">07:00 - 08:30</span>
                        <h6 class="fw-bold mb-0 mt-1">Pemrograman Web & Perangkat Bergerak</h6>
                        <span class="fs-8 text-muted">Guru: Pak Adi</span>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-2 border-start border-4 border-secondary">
                        <span class="fs-8 text-muted fw-semibold">08:45 - 10:15</span>
                        <h6 class="fw-bold mb-0 mt-1">Basis Data</h6>
                        <span class="fs-8 text-muted">Guru: Bu Rina</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
