<x-app-layout>
    @section('title', 'Hasil Validasi Import Data')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.imports.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Import
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-exclamation-octagon-fill text-danger me-2"></i>Hasil Validasi Import
            </h3>
            <p class="text-muted mb-0">Terdapat data tidak valid. Silakan unduh laporan error, perbaiki spreadsheet Anda, lalu upload kembali.</p>
        </div>
    </div>

    @php
        $total = $stats['total_rows'] ?? count($errors);
        $valid = $stats['valid_rows_count'] ?? 0;
        $invalid = $stats['invalid_rows_count'] ?? count($errors);
    @endphp

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center text-md-start" style="border-radius:12px;background:#f8fafc;">
                <span class="fs-8 fw-bold text-uppercase text-secondary">Total Data Baris</span>
                <h3 class="fw-bold mb-0 mt-2 text-dark">{{ $total }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center text-md-start" style="border-radius:12px;background:#eff6ff;">
                <span class="fs-8 fw-bold text-uppercase text-primary">Baris Valid</span>
                <h3 class="fw-bold mb-0 mt-2 text-primary">{{ $valid }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center text-md-start" style="border-radius:12px;background:#fff5f5;">
                <span class="fs-8 fw-bold text-uppercase text-danger">Baris dengan Error</span>
                <h3 class="fw-bold mb-0 mt-2 text-danger">{{ $invalid }}</h3>
            </div>
        </div>
    </div>

    <!-- Alert and Actions -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;background:#fff;">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-danger-subtle text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="bi bi-x-circle-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-1">Import Dibatalkan secara Otomatis (Atomic)</h5>
                    <p class="text-secondary mb-0" style="font-size:0.875rem;">
                        Demi keamanan integritas data, tidak ada data yang masuk ke database karena terdapat baris yang tidak valid.
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                <a href="{{ route('admin.imports.error-report') }}" class="btn btn-danger fw-semibold px-4 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-excel-fill"></i> Download Laporan Error
                </a>
                <a href="{{ route('admin.imports.index') }}" class="btn btn-light border fw-semibold px-4">
                    Tutup
                </a>
            </div>
        </div>
    </div>

    <!-- Error Detail Table -->
    <div class="card glass-card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-header border-bottom py-3 px-4" style="background:#fff;">
            <h6 class="fw-bold mb-0 text-dark">Detail Kesalahan Data</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light text-uppercase text-secondary" style="font-size:0.75rem;">
                        <tr>
                            <th class="py-3 ps-4 text-center" style="width:100px;">Baris Excel</th>
                            <th class="py-3" style="width:180px;">Nama Kolom</th>
                            <th class="py-3" style="width:220px;">Nilai yang Diisi</th>
                            <th class="py-3 pe-4 text-danger">Pesan Kesalahan (Error)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($errors as $error)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="py-3 ps-4 text-center fw-bold text-dark">{{ $error['row'] }}</td>
                                <td class="py-3 fw-semibold text-secondary">{{ $error['column'] }}</td>
                                <td class="py-3">
                                    <code class="px-2 py-1 bg-light rounded text-dark font-monospace" style="font-size:0.8rem;">
                                        {{ $error['value'] === '' || $error['value'] === null ? '[KOSONG]' : $error['value'] }}
                                    </code>
                                </td>
                                <td class="py-3 pe-4 text-danger fw-medium">
                                    <i class="bi bi-x-circle me-1"></i>{{ $error['message'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
