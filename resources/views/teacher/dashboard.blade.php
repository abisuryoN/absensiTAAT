<x-app-layout>
    @section('title', 'Dashboard Guru')

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card stat-card glass-card text-white bg-primary h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-7 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Jadwal Mengajar Hari Ini</span>
                        <h2 class="display-6 fw-bold mb-0 mt-1">3 Kelas</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-calendar-check-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card stat-card glass-card text-white bg-success h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="fs-7 text-white text-opacity-75 text-uppercase fw-semibold tracking-wider d-block">Absensi Selesai Diisi</span>
                        <h2 class="display-6 fw-bold mb-0 mt-1">2 Kelas</h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-3 p-3">
                        <i class="bi bi-check2-all fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card glass-card border-0 shadow-sm p-4">
        <h5 class="fw-bold mb-3">Jadwal Pelajaran Hari Ini</h5>
        <div class="table-responsive">
            <table class="table table-premium align-middle mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Status Absensi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>07:30 - 09:00</td>
                        <td>Matematika Peminatan</td>
                        <td>XI IPA 1</td>
                        <td><span class="badge bg-success">Sudah Diisi</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary rounded-3 px-3 disabled">Lihat Rekap</button>
                        </td>
                    </tr>
                    <tr>
                        <td>09:15 - 10:45</td>
                        <td>Matematika Peminatan</td>
                        <td>XI IPA 2</td>
                        <td><span class="badge bg-success">Sudah Diisi</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary rounded-3 px-3 disabled">Lihat Rekap</button>
                        </td>
                    </tr>
                    <tr>
                        <td>11:00 - 12:30</td>
                        <td>Matematika Wajib</td>
                        <td>XI IPS 3</td>
                        <td><span class="badge bg-warning text-dark">Belum Diisi</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary rounded-3 px-3 disabled">Isi Absensi</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
