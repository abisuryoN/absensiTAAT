<x-app-layout>
    @section('title', 'Import Data Excel')

    <div class="row mb-4">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Import Data Excel</h3>
            <p class="text-muted mb-0">Impor data Master secara massal menggunakan file spreadsheet (.xlsx, .xls, .csv).</p>
        </div>
    </div>

    <div class="row">
        <!-- Import Form -->
        <div class="col-md-6">
            <div class="card glass-card border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload File
                    </h5>
                    
                    <form method="POST" action="{{ route('admin.imports.preview') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="type" class="form-label fw-semibold">Tipe Data Master <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">Pilih Tipe Data</option>
                                <option value="students">Data Siswa</option>
                                <option value="teachers">Data Guru</option>
                                <option value="classes">Data Kelas</option>
                                <option value="schedules">Data Jadwal Pelajaran</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="file" class="form-label fw-semibold">Pilih File Spreadsheet <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text fs-8 mt-1">Format file harus .xlsx, .xls, atau .csv. Maksimal 5MB.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="bi bi-eye me-1"></i> Preview & Validasi Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Template Downloads -->
        <div class="col-md-6">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-download me-1"></i> Download Template Excel
                    </h5>
                    
                    <p class="text-muted fs-8 mb-3">
                        Download template Excel sesuai dengan jenis data yang akan diimport. Template sudah berisi format kolom yang sesuai.
                    </p>

                    <div class="d-grid gap-2">
                        <!-- Template Siswa -->
                        <a href="{{ route('admin.imports.template', 'students') }}" class="btn btn-outline-primary text-start d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                                <span class="fw-semibold">Template Data Siswa</span>
                            </div>
                            <i class="bi bi-download"></i>
                        </a>

                        <!-- Template Guru -->
                        <a href="{{ route('admin.imports.template', 'teachers') }}" class="btn btn-outline-success text-start d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                                <span class="fw-semibold">Template Data Guru</span>
                            </div>
                            <i class="bi bi-download"></i>
                        </a>

                        <!-- Template Kelas -->
                        <a href="{{ route('admin.imports.template', 'classes') }}" class="btn btn-outline-warning text-start d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                                <span class="fw-semibold">Template Data Kelas</span>
                            </div>
                            <i class="bi bi-download"></i>
                        </a>

                        <!-- Template Jadwal -->
                        <a href="{{ route('admin.imports.template', 'schedules') }}" class="btn btn-outline-info text-start d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                                <span class="fw-semibold">Template Jadwal Pelajaran</span>
                            </div>
                            <i class="bi bi-download"></i>
                        </a>
                    </div>

                    <div class="alert alert-info mt-3 mb-0 fs-8">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Petunjuk:</strong> Download template, isi data sesuai format, lalu upload file untuk preview sebelum menyimpan ke database.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
